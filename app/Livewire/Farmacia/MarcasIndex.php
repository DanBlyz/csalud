<?php

namespace App\Livewire\Farmacia;

use App\Models\Marca;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MarcasIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public int $perPage = 10;

    // Control de modal de creación / edición
    public bool $modalOpen = false;

    public ?int $marcaId = null;

    public string $nombre = '';

    public string $descripcion = '';

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:150|unique:marcas,nombre,'.$this->marcaId.',id,deleted_at,NULL',
            'descripcion' => 'nullable|string|max:500',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre de la marca o laboratorio es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'nombre.max' => 'El nombre no debe exceder 150 caracteres.',
        'nombre.unique' => 'Ya existe una marca o laboratorio registrado con este nombre.',
        'descripcion.max' => 'La descripción no debe exceder 500 caracteres.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function abrirModal(?int $id = null): void
    {
        $this->resetValidation();
        $this->marcaId = $id;

        if ($id) {
            $marca = Marca::findOrFail($id);
            $this->nombre = $marca->nombre;
            $this->descripcion = $marca->descripcion ?? '';
        } else {
            $this->nombre = '';
            $this->descripcion = '';
        }

        $this->modalOpen = true;
    }

    public function cerrarModal(): void
    {
        $this->modalOpen = false;
        $this->marcaId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->resetValidation();
    }

    public function guardar(): void
    {
        $this->validate();

        if ($this->marcaId) {
            $marca = Marca::findOrFail($this->marcaId);
            $marca->update([
                'nombre' => trim($this->nombre),
                'descripcion' => ! empty($this->descripcion) ? trim($this->descripcion) : null,
            ]);

            $mensaje = 'Marca / Laboratorio actualizado correctamente.';
        } else {
            Marca::create([
                'nombre' => trim($this->nombre),
                'descripcion' => ! empty($this->descripcion) ? trim($this->descripcion) : null,
            ]);

            $mensaje = 'Nueva marca / laboratorio registrado con éxito.';
        }

        $this->cerrarModal();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Operación Exitosa!',
            'text' => $mensaje,
            'toast' => true,
        ]);
    }

    public function eliminar(int $id): void
    {
        $marca = Marca::withCount('productos')->findOrFail($id);

        if ($marca->productos_count > 0) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "La marca tiene {$marca->productos_count} producto(s) o medicamento(s) vinculado(s). Debe reasignar o desvincularlos antes de eliminarla.",
                'toast' => false,
            ]);

            return;
        }

        $marca->delete();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => 'Marca Eliminada',
            'text' => 'La marca o laboratorio ha sido eliminada del catálogo.',
            'toast' => true,
        ]);
    }

    public function render(): View
    {
        $marcas = Marca::withCount('productos')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('nombre', 'like', "%{$this->search}%")
                        ->orWhere('descripcion', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('nombre', 'asc')
            ->paginate($this->perPage);

        $totalMarcas = Marca::count();
        $totalProductosConMarca = Marca::withCount('productos')->get()->sum('productos_count');

        return view('livewire.farmacia.marcas-index', [
            'marcas' => $marcas,
            'totalMarcas' => $totalMarcas,
            'totalProductosConMarca' => $totalProductosConMarca,
        ]);
    }
}
