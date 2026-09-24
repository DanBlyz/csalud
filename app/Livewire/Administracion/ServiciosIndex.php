<?php

namespace App\Livewire\Administracion;

use App\Models\Categoria;
use App\Models\Servicio;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ServiciosIndex extends Component
{
    use WithPagination;

    public string $tab = 'servicios'; // 'servicios' o 'categorias'

    // Controles estándar de tabla
    public int $perPage = 10;

    public string $search = '';

    public string $filtroCategoria = '';

    public string $filtroEstado = '';

    // Modal Servicio
    public bool $modalServicioOpen = false;

    public ?int $servicioId = null;

    public ?int $categoria_id = null;

    public string $nombreServicio = '';

    public string $descripcionServicio = '';

    public string $precio_tentativo = '0.00';

    public bool $estadoServicio = true;

    // Modal Categoría
    public bool $modalCategoriaOpen = false;

    public ?int $categoriaId = null;

    public string $nombreCategoria = '';

    public string $descripcionCategoria = '';

    public bool $estadoCategoria = true;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoria(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function cambiarTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
        $this->search = '';
        $this->filtroCategoria = '';
        $this->filtroEstado = '';
    }

    /* -------------------------------------------------------------
     * GESTIÓN DE SERVICIOS
     * ------------------------------------------------------------- */
    public function abrirModalServicioCrear(): void
    {
        $this->resetValidation();
        $this->reset(['servicioId', 'nombreServicio', 'descripcionServicio']);
        $this->precio_tentativo = '0.00';
        $this->estadoServicio = true;
        $this->categoria_id = Categoria::where('estado', true)->first()?->id;

        $this->modalServicioOpen = true;
    }

    public function abrirModalServicioEditar(int $id): void
    {
        $this->resetValidation();
        $servicio = Servicio::findOrFail($id);

        $this->servicioId = $servicio->id;
        $this->categoria_id = $servicio->categoria_id;
        $this->nombreServicio = $servicio->nombre;
        $this->descripcionServicio = $servicio->descripcion ?? '';
        $this->precio_tentativo = (string) $servicio->precio_tentativo;
        $this->estadoServicio = (bool) $servicio->estado;

        $this->modalServicioOpen = true;
    }

    public function cerrarModalServicio(): void
    {
        $this->modalServicioOpen = false;
        $this->resetValidation();
    }

    public function guardarServicio(): void
    {
        $this->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombreServicio' => 'required|string|min:3|max:150',
            'descripcionServicio' => 'nullable|string|max:500',
            'precio_tentativo' => 'required|numeric|min:0',
            'estadoServicio' => 'boolean',
        ], [
            'categoria_id.required' => 'Debe asociar el servicio a una categoría.',
            'nombreServicio.required' => 'El nombre del servicio es obligatorio.',
            'precio_tentativo.required' => 'El precio sugerido es obligatorio.',
        ]);

        $data = [
            'categoria_id' => $this->categoria_id,
            'nombre' => $this->nombreServicio,
            'descripcion' => $this->descripcionServicio ?: null,
            'precio_tentativo' => $this->precio_tentativo,
            'estado' => $this->estadoServicio,
        ];

        if ($this->servicioId) {
            $servicio = Servicio::findOrFail($this->servicioId);
            $servicio->update($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Servicio Actualizado',
                'text' => "El servicio '{$servicio->nombre}' fue modificado correctamente.",
            ]);
        } else {
            $servicio = Servicio::create($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Servicio Creado',
                'text' => "El servicio '{$servicio->nombre}' fue registrado con éxito.",
            ]);
        }

        $this->cerrarModalServicio();
    }

    public function toggleEstadoServicio(int $id): void
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->estado = ! $servicio->estado;
        $servicio->save();

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado Modificado',
            'text' => "El servicio '{$servicio->nombre}' ahora está ".($servicio->estado ? 'activo' : 'inactivo').'.',
        ]);
    }

    #[On('eliminarServicio')]
    public function eliminarServicio(int $id): void
    {
        $servicio = Servicio::withCount('proformaServicios')->findOrFail($id);

        if ($servicio->proforma_servicios_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "Este servicio está asociado a {$servicio->proforma_servicios_count} proforma(s). Desactívelo para no romper el historial.",
                'toast' => false,
            ]);

            return;
        }

        $servicio->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Servicio Eliminado',
            'text' => "El servicio '{$servicio->nombre}' ha sido eliminado.",
        ]);
    }

    /* -------------------------------------------------------------
     * GESTIÓN DE CATEGORÍAS
     * ------------------------------------------------------------- */
    public function abrirModalCategoriaCrear(): void
    {
        $this->resetValidation();
        $this->reset(['categoriaId', 'nombreCategoria', 'descripcionCategoria']);
        $this->estadoCategoria = true;
        $this->modalCategoriaOpen = true;
    }

    public function abrirModalCategoriaEditar(int $id): void
    {
        $this->resetValidation();
        $cat = Categoria::findOrFail($id);

        $this->categoriaId = $cat->id;
        $this->nombreCategoria = $cat->nombre;
        $this->descripcionCategoria = $cat->descripcion ?? '';
        $this->estadoCategoria = (bool) $cat->estado;

        $this->modalCategoriaOpen = true;
    }

    public function cerrarModalCategoria(): void
    {
        $this->modalCategoriaOpen = false;
        $this->resetValidation();
    }

    public function guardarCategoria(): void
    {
        $this->validate([
            'nombreCategoria' => 'required|string|min:3|max:100',
            'descripcionCategoria' => 'nullable|string|max:255',
            'estadoCategoria' => 'boolean',
        ], [
            'nombreCategoria.required' => 'El nombre de la categoría es obligatorio.',
        ]);

        $data = [
            'nombre' => $this->nombreCategoria,
            'descripcion' => $this->descripcionCategoria ?: null,
            'estado' => $this->estadoCategoria,
        ];

        if ($this->categoriaId) {
            $cat = Categoria::findOrFail($this->categoriaId);
            $cat->update($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Categoría Actualizada',
                'text' => "La categoría '{$cat->nombre}' fue modificada.",
            ]);
        } else {
            $cat = Categoria::create($data);

            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Categoría Creada',
                'text' => "La categoría '{$cat->nombre}' fue registrada.",
            ]);
        }

        $this->cerrarModalCategoria();
    }

    public function toggleEstadoCategoria(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        $cat->estado = ! $cat->estado;
        $cat->save();

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado Modificado',
            'text' => "La categoría '{$cat->nombre}' ahora está ".($cat->estado ? 'activa' : 'inactiva').'.',
        ]);
    }

    #[On('eliminarCategoria')]
    public function eliminarCategoria(int $id): void
    {
        $cat = Categoria::withCount('servicios')->findOrFail($id);

        if ($cat->servicios_count > 0) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'No se puede eliminar',
                'text' => "Esta categoría contiene {$cat->servicios_count} servicio(s) registrados. Reasígnelos o elimínelos antes.",
                'toast' => false,
            ]);

            return;
        }

        $cat->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Categoría Eliminada',
            'text' => "La categoría '{$cat->nombre}' ha sido eliminada.",
        ]);
    }

    public function render(): View
    {
        if ($this->tab === 'categorias') {
            $items = Categoria::query()
                ->withCount('servicios')
                ->when($this->search !== '', function ($q): void {
                    $q->where(function ($sub): void {
                        $sub->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('descripcion', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->filtroEstado !== '', function ($q): void {
                    $q->where('estado', (bool) $this->filtroEstado);
                })
                ->latest()
                ->paginate($this->perPage);
        } else {
            $items = Servicio::query()
                ->with('categoria')
                ->withCount('proformaServicios')
                ->when($this->search !== '', function ($q): void {
                    $q->where(function ($sub): void {
                        $sub->where('nombre', 'like', "%{$this->search}%")
                            ->orWhere('descripcion', 'like', "%{$this->search}%");
                    });
                })
                ->when($this->filtroCategoria !== '', function ($q): void {
                    $q->where('categoria_id', $this->filtroCategoria);
                })
                ->when($this->filtroEstado !== '', function ($q): void {
                    $q->where('estado', (bool) $this->filtroEstado);
                })
                ->latest()
                ->paginate($this->perPage);
        }

        return view('livewire.administracion.servicios-index', [
            'items' => $items,
            'categorias' => Categoria::where('estado', true)->get(),
            'todasCategorias' => Categoria::all(),
        ]);
    }
}
