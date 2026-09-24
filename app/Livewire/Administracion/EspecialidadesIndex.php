<?php

namespace App\Livewire\Administracion;

use App\Models\Especialidad;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class EspecialidadesIndex extends Component
{
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';

    public bool $modalOpen = false;

    public ?int $especialidadId = null;

    public string $nombre = '';

    public string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:3|max:100|unique:especialidades,nombre,'.$this->especialidadId,
            'descripcion' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la especialidad es obligatorio.',
        'nombre.unique' => 'Esta especialidad médica ya está registrada.',
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
        $this->reset(['especialidadId', 'nombre', 'descripcion']);
        $this->modalOpen = true;
    }

    public function abrirModalEditar(int $id): void
    {
        $this->resetValidation();
        $esp = Especialidad::findOrFail($id);

        $this->especialidadId = $esp->id;
        $this->nombre = $esp->nombre;
        $this->descripcion = $esp->descripcion ?? '';
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

        if ($this->especialidadId) {
            $esp = Especialidad::findOrFail($this->especialidadId);
            $esp->update($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Especialidad Actualizada',
                'text' => "La especialidad '{$esp->nombre}' fue actualizada.",
            ]);
        } else {
            $esp = Especialidad::create($validated);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Especialidad Registrada',
                'text' => "La especialidad '{$esp->nombre}' fue registrada exitosamente.",
            ]);
        }

        $this->cerrarModal();
    }

    #[On('eliminarEspecialidad')]
    public function eliminar(int $id): void
    {
        $esp = Especialidad::withCount('users')->findOrFail($id);

        if ($esp->users_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "Existen {$esp->users_count} médico(s) asignados a esta especialidad. Reasígnelos antes de eliminarla.",
                'toast' => false,
            ]);

            return;
        }

        $esp->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Especialidad Eliminada',
            'text' => "La especialidad '{$esp->nombre}' fue eliminada del sistema.",
        ]);
    }

    public function render(): View
    {
        $query = Especialidad::query()
            ->withCount('users')
            ->when($this->search !== '', function ($q): void {
                $q->where(function ($sub): void {
                    $sub->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->latest();

        return view('livewire.administracion.especialidades-index', [
            'especialidades' => $query->paginate($this->perPage),
        ]);
    }
}
