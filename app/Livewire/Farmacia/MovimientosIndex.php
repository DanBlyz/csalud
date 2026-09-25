<?php

namespace App\Livewire\Farmacia;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MovimientosIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursal = null;

    public ?int $filtroProducto = null;

    public string $filtroTipo = ''; // 'Entrada Compra', 'Salida Receta', 'Consumo Extra', 'Merma por Vencimiento', 'Ajuste de Inventario'

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    public function mount(): void
    {
        $this->filtroSucursal = Auth::user()->sucursal_id ?? null;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroProducto(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    public function updatingFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFechaHasta(): void
    {
        $this->resetPage();
    }

    public function limpiarFiltros(): void
    {
        $this->search = '';
        $this->filtroProducto = null;
        $this->filtroTipo = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->resetPage();
    }

    public function render(): View
    {
        $query = MovimientoInventario::query()
            ->with([
                'producto.marca',
                'lote',
                'sucursal',
                'usuario',
                'proforma.paciente',
            ]);

        if (! empty($this->search)) {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('producto', function (Builder $p) use ($search) {
                    $p->where('nombre', 'like', $search);
                })
                    ->orWhereHas('lote', function (Builder $l) use ($search) {
                        $l->where('codigo_lote', 'like', $search);
                    })
                    ->orWhereHas('usuario', function (Builder $u) use ($search) {
                        $u->where('name', 'like', $search);
                    })
                    ->orWhereHas('proforma.paciente', function (Builder $pac) use ($search) {
                        $pac->where('nombres', 'like', $search)
                            ->orWhere('apellido_paterno', 'like', $search);
                    })
                    ->orWhere('tipo_movimiento', 'like', $search);
            });
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroProducto) {
            $query->where('producto_id', $this->filtroProducto);
        }

        if (! empty($this->filtroTipo)) {
            $query->where('tipo_movimiento', $this->filtroTipo);
        }

        if (! empty($this->fechaDesde)) {
            $query->whereDate('created_at', '>=', $this->fechaDesde);
        }

        if (! empty($this->fechaHasta)) {
            $query->whereDate('created_at', '<=', $this->fechaHasta);
        }

        $movimientos = $query->latest('id')->paginate($this->perPage);

        // Métricas
        $totalMovimientos = MovimientoInventario::count();
        $totalEntradas = MovimientoInventario::where('tipo_movimiento', 'like', 'Entrada%')->count();
        $totalSalidas = MovimientoInventario::whereIn('tipo_movimiento', ['Salida Receta', 'Consumo Extra'])->count();
        $totalMermas = MovimientoInventario::whereIn('tipo_movimiento', ['Merma por Vencimiento', 'Ajuste de Inventario', 'Baja por Deterioro'])->count();

        // Colecciones para filtros
        $sucursales = Sucursal::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get(['id', 'nombre']);

        return view('livewire.farmacia.movimientos-index', [
            'movimientos' => $movimientos,
            'totalMovimientos' => $totalMovimientos,
            'totalEntradas' => $totalEntradas,
            'totalSalidas' => $totalSalidas,
            'totalMermas' => $totalMermas,
            'sucursales' => $sucursales,
            'productos' => $productos,
        ]);
    }
}
