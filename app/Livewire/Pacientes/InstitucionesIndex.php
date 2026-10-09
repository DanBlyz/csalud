<?php

namespace App\Livewire\Pacientes;

use App\Models\Institucion;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class InstitucionesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    public string $filtroEstado = '';

    // Modal Crear / Editar
    public bool $modalOpen = false;

    public ?int $institucionId = null;

    public string $nombre = '';

    public string $descripcion = '';

    public string $estado = 'Activo';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:150|unique:instituciones,nombre,' . $this->institucionId . ',id,deleted_at,NULL',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|string|in:Activo,Inactivo',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre de la institución o seguro es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no debe exceder 150 caracteres.',
        'nombre.unique' => 'Ya existe una institución registrada con este nombre.',
        'descripcion.max' => 'La descripción no debe exceder 500 caracteres.',
        'estado.required' => 'El estado es obligatorio.',
        'estado.in' => 'El estado seleccionado no es válido.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function abrirModal(?int $id = null): void
    {
        if (! Auth::user()?->tienePermiso('instituciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar instituciones y convenios.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->institucionId = $id;

        if ($id) {
            $institucion = Institucion::findOrFail($id);
            $this->nombre = $institucion->nombre;
            $this->descripcion = $institucion->descripcion ?? '';
            $this->estado = $institucion->estado ?? 'Activo';
        } else {
            $this->nombre = '';
            $this->descripcion = '';
            $this->estado = 'Activo';
        }

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->institucionId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->estado = 'Activo';
        $this->resetValidation();
    }

    public function guardar(): void
    {
        if (! Auth::user()?->tienePermiso('instituciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar instituciones y convenios.',
            ]);

            return;
        }

        $this->validate();

        if ($this->institucionId) {
            $institucion = Institucion::findOrFail($this->institucionId);
            $institucion->update([
                'nombre' => trim($this->nombre),
                'descripcion' => ! empty($this->descripcion) ? trim($this->descripcion) : null,
                'estado' => $this->estado,
            ]);

            $mensaje = 'Institución o seguro actualizado correctamente.';
        } else {
            Institucion::create([
                'nombre' => trim($this->nombre),
                'descripcion' => ! empty($this->descripcion) ? trim($this->descripcion) : null,
                'estado' => $this->estado,
            ]);

            $mensaje = 'Nueva institución o seguro registrado con éxito.';
        }

        $this->cerrarModal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Operación Exitosa!',
            'text' => $mensaje,
        ]);
    }

    public function toggleEstado(int $id): void
    {
        if (! Auth::user()?->tienePermiso('instituciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar instituciones y convenios.',
            ]);

            return;
        }

        $institucion = Institucion::findOrFail($id);
        $institucion->estado = ($institucion->estado === 'Activo' || $institucion->estado === null) ? 'Inactivo' : 'Activo';
        $institucion->save();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Estado Actualizado',
            'text' => "La institución '{$institucion->nombre}' ahora está {$institucion->estado}.",
        ]);
    }

    #[On('eliminarInstitucion')]
    public function eliminarInstitucion(int $id): void
    {
        if (! Auth::user()?->tienePermiso('instituciones.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar instituciones y convenios.',
            ]);

            return;
        }

        $institucion = Institucion::withCount('pacientes')->findOrFail($id);

        if ($institucion->pacientes_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "La institución tiene {$institucion->pacientes_count} paciente(s) vinculado(s). Debe reasignar los pacientes antes de poder eliminarla.",
            ]);

            return;
        }

        $nombre = $institucion->nombre;
        $institucion->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Institución Eliminada',
            'text' => "La institución '{$nombre}' ha sido eliminada del sistema.",
        ]);
    }

    public function render(): View
    {
        $instituciones = Institucion::withCount('pacientes')
            ->when($this->search !== '', function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filtroEstado !== '', function ($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        $totalInstituciones = Institucion::count();
        $totalActivas = Institucion::where(function ($q) {
            $q->where('estado', 'Activo')->orWhereNull('estado');
        })->count();
        $totalPacientesVinculados = Institucion::withCount('pacientes')->get()->sum('pacientes_count');

        return view('livewire.pacientes.instituciones-index', [
            'instituciones' => $instituciones,
            'totalInstituciones' => $totalInstituciones,
            'totalActivas' => $totalActivas,
            'totalPacientesVinculados' => $totalPacientesVinculados,
        ]);
    }
}
