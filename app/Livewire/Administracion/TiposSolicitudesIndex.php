<?php

namespace App\Livewire\Administracion;

use App\Models\ProformaSolicitud;
use App\Models\TipoSolicitud;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TiposSolicitudesIndex extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public bool $modalOpen = false;

    public ?int $tipoSolicitudId = null;

    public string $nombre = '';

    public string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:150|unique:tipos_solicitudes,nombre,'.$this->tipoSolicitudId.',id,deleted_at,NULL',
            'descripcion' => 'nullable|string|max:500',
        ];
    }

    protected array $messages = [
        'nombre.required' => 'El nombre del tipo de solicitud o examen es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no debe exceder los 150 caracteres.',
        'nombre.unique' => 'Ya existe un tipo de solicitud o examen registrado con este nombre.',
        'descripcion.max' => 'La descripción no debe exceder los 500 caracteres.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function abrirModalCrear(): void
    {
        if (! Auth::user()?->tienePermiso('tipos-solicitudes.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar tipos de solicitudes y exámenes médicos.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->reset(['tipoSolicitudId', 'nombre', 'descripcion']);
        $this->modalOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        if (! Auth::user()?->tienePermiso('tipos-solicitudes.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar tipos de solicitudes y exámenes médicos.',
            ]);

            return;
        }

        $this->resetValidation();
        $ts = TipoSolicitud::findOrFail($id);

        $this->tipoSolicitudId = $ts->id;
        $this->nombre = $ts->nombre;
        $this->descripcion = $ts->descripcion ?? '';
        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
        $this->reset(['tipoSolicitudId', 'nombre', 'descripcion']);
    }

    public function guardar(): void
    {
        if (! Auth::user()?->tienePermiso('tipos-solicitudes.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar tipos de solicitudes y exámenes médicos.',
            ]);

            return;
        }

        $this->validate();

        if ($this->tipoSolicitudId) {
            $ts = TipoSolicitud::findOrFail($this->tipoSolicitudId);
            $ts->update([
                'nombre' => trim($this->nombre),
                'descripcion' => trim($this->descripcion) ?: null,
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Tipo de Solicitud Actualizado',
                'text' => "El tipo de solicitud '{$ts->nombre}' fue actualizado correctamente.",
            ]);
        } else {
            $ts = TipoSolicitud::create([
                'nombre' => trim($this->nombre),
                'descripcion' => trim($this->descripcion) ?: null,
            ]);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Tipo de Solicitud Registrado',
                'text' => "El tipo de solicitud '{$ts->nombre}' fue registrado exitosamente.",
            ]);
        }

        $this->cerrarModal();
    }

    #[On('eliminarTipoSolicitud')]
    public function eliminar(int $id): void
    {
        if (! Auth::user()?->tienePermiso('tipos-solicitudes.gestionar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para administrar tipos de solicitudes y exámenes médicos.',
            ]);

            return;
        }

        $ts = TipoSolicitud::withCount('proformaSolicitudes')->findOrFail($id);

        if ($ts->proforma_solicitudes_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "Existen {$ts->proforma_solicitudes_count} solicitud(es) médica(s) vinculada(s) a este tipo en proformas clínicas. No puede eliminarse.",
            ]);

            return;
        }

        $nombre = $ts->nombre;
        $ts->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Tipo de Solicitud Eliminado',
            'text' => "El tipo de solicitud '{$nombre}' fue eliminado del sistema.",
        ]);
    }

    public function render(): View
    {
        $tiposSolicitudes = TipoSolicitud::query()
            ->withCount('proformaSolicitudes')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        $totalTipos = TipoSolicitud::count();
        $totalConSolicitudes = TipoSolicitud::has('proformaSolicitudes')->count();
        $totalSolicitudesRealizadas = ProformaSolicitud::count();

        return view('livewire.administracion.tipos-solicitudes-index', [
            'tiposSolicitudes' => $tiposSolicitudes,
            'totalTipos' => $totalTipos,
            'totalConSolicitudes' => $totalConSolicitudes,
            'totalSolicitudesRealizadas' => $totalSolicitudesRealizadas,
        ]);
    }
}
