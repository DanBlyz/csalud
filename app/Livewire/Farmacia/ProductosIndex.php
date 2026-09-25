<?php

namespace App\Livewire\Farmacia;

use App\Models\Marca;
use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProductosIndex extends Component
{
    use WithPagination;

    // Filtros y búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroMarca = null;

    public string $filtroStock = ''; // '' = Todos, 'normal' = Stock suficiente, 'critico' = <= stock_minimo, 'agotado' = 0

    // Modal Crear / Editar Producto
    public bool $modalProductoOpen = false;

    public ?int $productoId = null;

    public string $nombre = '';

    public ?int $marca_id = null;

    public string $unidad_medida = 'Tableta';

    public string $descripcion = '';

    public string $ultimo_precio_venta = '0.00';

    public int $stock_minimo = 5;

    // Modal Lotes del Producto
    public bool $modalLotesOpen = false;

    public ?int $productoDetalleId = null;

    protected function rules(): array
    {
        return [
            'nombre' => 'required|string|min:2|max:191',
            'marca_id' => 'nullable|exists:marcas,id',
            'unidad_medida' => 'required|string|max:50',
            'descripcion' => 'nullable|string|max:500',
            'ultimo_precio_venta' => 'required|numeric|min:0',
            'stock_minimo' => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'nombre.required' => 'El nombre del medicamento o insumo es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'unidad_medida.required' => 'La unidad de medida es requerida (ej. Tableta, Ampolla, Frasco).',
        'ultimo_precio_venta.required' => 'El precio de venta es obligatorio.',
        'ultimo_precio_venta.numeric' => 'El precio debe ser un número válido.',
        'stock_minimo.required' => 'El stock mínimo para alertas es obligatorio.',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroMarca(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroStock(): void
    {
        $this->resetPage();
    }

    public function abrirModalProducto(?int $id = null): void
    {
        $this->resetValidation();
        $this->productoId = $id;

        if ($id) {
            $producto = Producto::findOrFail($id);
            $this->nombre = $producto->nombre;
            $this->marca_id = $producto->marca_id;
            $this->unidad_medida = $producto->unidad_medida ?? 'Tableta';
            $this->descripcion = $producto->descripcion ?? '';
            $this->ultimo_precio_venta = (string) $producto->ultimo_precio_venta;
            $this->stock_minimo = (int) $producto->stock_minimo;
        } else {
            $this->nombre = '';
            $this->marca_id = null;
            $this->unidad_medida = 'Tableta';
            $this->descripcion = '';
            $this->ultimo_precio_venta = '0.00';
            $this->stock_minimo = 5;
        }

        $this->modalProductoOpen = true;
    }

    public function cerrarModalProducto(): void
    {
        $this->modalProductoOpen = false;
        $this->resetValidation();
    }

    public function guardarProducto(): void
    {
        $this->validate();

        if ($this->productoId) {
            $producto = Producto::findOrFail($this->productoId);
            $producto->update([
                'nombre' => trim($this->nombre),
                'marca_id' => $this->marca_id,
                'unidad_medida' => trim($this->unidad_medida),
                'descripcion' => trim($this->descripcion),
                'ultimo_precio_venta' => (float) $this->ultimo_precio_venta,
                'stock_minimo' => (int) $this->stock_minimo,
            ]);

            $mensaje = 'Producto / medicamento actualizado correctamente.';
        } else {
            Producto::create([
                'nombre' => trim($this->nombre),
                'marca_id' => $this->marca_id,
                'unidad_medida' => trim($this->unidad_medida),
                'descripcion' => trim($this->descripcion),
                'ultimo_precio_venta' => (float) $this->ultimo_precio_venta,
                'stock_minimo' => (int) $this->stock_minimo,
            ]);

            $mensaje = 'Nuevo producto / medicamento registrado en el catálogo.';
        }

        $this->cerrarModalProducto();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Operación Exitosa!',
            'message' => $mensaje,
        ]);
    }

    public function verLotes(int $id): void
    {
        $this->productoDetalleId = $id;
        $this->modalLotesOpen = true;
    }

    public function cerrarModalLotes(): void
    {
        $this->modalLotesOpen = false;
        $this->productoDetalleId = null;
    }

    public function eliminarProducto(int $id): void
    {
        $producto = Producto::with('lotes')->findOrFail($id);

        $stockDisponible = (int) $producto->lotes->where('cantidad_actual', '>', 0)->sum('cantidad_actual');
        if ($stockDisponible > 0) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'No se puede eliminar',
                'message' => "El producto tiene {$stockDisponible} unidades disponibles en lotes activos. Debe agotar o dar de baja los lotes antes de eliminarlo.",
            ]);

            return;
        }

        $producto->delete();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => 'Producto Eliminado',
            'message' => 'El producto fue archivado correctamente en el catálogo.',
        ]);
    }

    public function render(): View
    {
        // Query de productos con relación a marca y cálculo de stock total
        $query = Producto::query()
            ->with(['marca'])
            ->withSum('lotes as stock_total', 'cantidad_actual');

        if (! empty($this->search)) {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('nombre', 'like', $search)
                    ->orWhere('descripcion', 'like', $search)
                    ->orWhere('unidad_medida', 'like', $search)
                    ->orWhereHas('marca', function (Builder $m) use ($search) {
                        $m->where('nombre', 'like', $search);
                    });
            });
        }

        if ($this->filtroMarca) {
            $query->where('marca_id', $this->filtroMarca);
        }

        if ($this->filtroStock === 'agotado') {
            $query->having('stock_total', '<=', 0)->orHavingRaw('stock_total IS NULL');
        } elseif ($this->filtroStock === 'critico') {
            $query->havingRaw('stock_total > 0 AND stock_total <= stock_minimo');
        } elseif ($this->filtroStock === 'normal') {
            $query->havingRaw('stock_total > stock_minimo');
        }

        $productos = $query->orderBy('nombre', 'asc')->paginate($this->perPage);

        // Métricas rápidas
        $totalProductos = Producto::count();
        $marcas = Marca::orderBy('nombre')->get();

        // Producto seleccionado para ver lotes
        $productoSeleccionado = $this->productoDetalleId
            ? Producto::with(['marca', 'lotes.sucursal', 'lotes.proveedor'])->find($this->productoDetalleId)
            : null;

        return view('livewire.farmacia.productos-index', [
            'productos' => $productos,
            'totalProductos' => $totalProductos,
            'marcas' => $marcas,
            'productoSeleccionado' => $productoSeleccionado,
        ]);
    }
}
