<?php

namespace App\Livewire\Administracion;

use App\Models\Rol;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class RolesIndex extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public bool $modalOpen = false;

    public ?int $rolId = null;

    public string $nombre = '';

    public string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:3|max:100|unique:roles,nombre,'.$this->rolId,
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre del rol es obligatorio.',
        'nombre.unique' => 'Ya existe un rol con este nombre.',
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
        $this->resetValidation();
        $this->reset(['rolId', 'nombre', 'descripcion']);
        $this->modalOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetValidation();
        $rol = Rol::findOrFail($id);

        $this->rolId = $rol->id;
        $this->nombre = $rol->nombre;
        $this->descripcion = $rol->descripcion ?? '';
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

        if ($this->rolId) {
            $rol = Rol::findOrFail($this->rolId);

            // Protección de rol del sistema
            if ($rol->nombre === 'Admin' && $validated['nombre'] !== 'Admin') {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Acción Bloqueada',
                    'text' => 'El nombre del rol Administrador del sistema no puede ser modificado.',
                    'toast' => false,
                ]);

                return;
            }

            $rol->update($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Rol Actualizado!',
                'text' => "El rol '{$rol->nombre}' ha sido actualizado con éxito.",
            ]);
        } else {
            $rol = Rol::create($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => '¡Rol Creado!',
                'text' => "El rol '{$rol->nombre}' ha sido registrado en el sistema.",
            ]);
        }

        $this->cerrarModal();
    }

    #[On('eliminarRol')]
    public function eliminar(int $id): void
    {
        $rol = Rol::withCount('users')->findOrFail($id);

        if ($rol->nombre === 'Admin') {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acción Prohibida',
                'text' => 'El rol Administrador es esencial para la seguridad del sistema y no puede eliminarse.',
                'toast' => false,
            ]);

            return;
        }

        if ($rol->users_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "Este rol tiene {$rol->users_count} usuario(s) asignados. Reasigne a los usuarios antes de eliminar el rol.",
                'toast' => false,
            ]);

            return;
        }

        $rol->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Rol Eliminado',
            'text' => "El rol '{$rol->nombre}' ha sido eliminado correctamente.",
        ]);
    }

    public function render(): View
    {
        $query = Rol::query()
            ->withCount('users')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->latest();

        return view('livewire.administracion.roles-index', [
            'roles' => $query->paginate($this->perPage),
        ]);
    }
}
