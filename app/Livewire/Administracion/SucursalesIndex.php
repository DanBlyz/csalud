<?php

namespace App\Livewire\Administracion;

use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class SucursalesIndex extends Component
{
    use WithPagination;

    // Control de listado estándar
    public int $perPage = 10;

    public string $search = '';

    public string $filtroEstado = '';

    // Control de modal de formulario
    public bool $modalOpen = false;

    public ?int $sucursalId = null;

    // Campos del formulario
    public string $nombre = '';

    public string $direccion = '';

    public string $telefono = '';

    public string $ciudad = '';

    public bool $estado = true;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:3|max:150',
            'direccion' => 'nullable|string|max:255',
            'telefono' => 'nullable|string|max:30',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'boolean',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la sucursal es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'nombre.max' => 'El nombre no debe superar los 150 caracteres.',
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

    public function abrirModalCrear(): void
    {
        $this->resetValidation();
        $this->reset(['sucursalId', 'nombre', 'direccion', 'telefono', 'ciudad']);
        $this->estado = true;
        $this->modalOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetValidation();
        $sucursal = Sucursal::findOrFail($id);

        $this->sucursalId = $sucursal->id;
        $this->nombre = $sucursal->nombre;
        $this->direccion = $sucursal->direccion ?? '';
        $this->telefono = $sucursal->telefono ?? '';
        $this->ciudad = $sucursal->ciudad ?? '';
        $this->estado = (bool) $sucursal->estado;

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $validated = $this->validate();

        if ($this->sucursalId) {
            $sucursal = Sucursal::findOrFail($this->sucursalId);
            $sucursal->update($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Sucursal Actualizada!',
                'text' => "La sucursal '{$sucursal->nombre}' ha sido modificada correctamente.",
            ]);
        } else {
            $sucursal = Sucursal::create($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Sucursal Creada!',
                'text' => "La sucursal '{$sucursal->nombre}' fue registrada con éxito.",
            ]);
        }

        $this->cerrarModal();
    }

    public function toggleEstado(int $id): void
    {
        $sucursal = Sucursal::findOrFail($id);
        $sucursal->estado = ! $sucursal->estado;
        $sucursal->save();

        $estadoTexto = $sucursal->estado ? 'activada' : 'desactivada';

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado Modificado',
            'text' => "La sucursal '{$sucursal->nombre}' ahora está {$estadoTexto}.",
        ]);
    }

    #[On('eliminarSucursal')]
    public function eliminar(int $id): void
    {
        $sucursal = Sucursal::withCount(['users', 'lotes', 'proformas'])->findOrFail($id);

        // Regla de Negocio documentada en Obsidian: No se puede eliminar si posee usuarios, lotes o proformas
        if ($sucursal->users_count > 0 || $sucursal->lotes_count > 0 || $sucursal->proformas_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "La sucursal '{$sucursal->nombre}' tiene registros vinculados ({$sucursal->users_count} usuarios, {$sucursal->lotes_count} lotes, {$sucursal->proformas_count} proformas). Desactívela en su lugar.",
                'toast' => false,
            ]);

            return;
        }

        $sucursal->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Sucursal Eliminada',
            'text' => "La sucursal '{$sucursal->nombre}' ha sido eliminada del sistema.",
        ]);
    }

    public function render(): View
    {
        $query = Sucursal::query()
            ->withCount(['users', 'lotes', 'proformas'])
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('ciudad', 'like', "%{$this->search}%")
                        ->orWhere('direccion', 'like', "%{$this->search}%")
                        ->orWhere('telefono', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filtroEstado !== '', function ($q): void {
                $q->where('estado', (bool) $this->filtroEstado);
            })
            ->latest();

        return view('livewire.administracion.sucursales-index', [
            'sucursales' => $query->paginate($this->perPage),
        ]);
    }
}
