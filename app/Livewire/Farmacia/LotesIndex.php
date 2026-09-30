<?php

namespace App\Livewire\Farmacia;

use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class LotesIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursal = null;

    public ?int $filtroProveedor = null;

    public string $filtroVencimiento = ''; // '' = Todos, 'vigentes', 'proximos', 'vencidos', 'agotados'

    // Modal Registro de Lote
    public bool $modalLoteOpen = false;

    public ?int $sucursal_id = null;

    public ?int $producto_id = null;

    public ?int $proveedor_id = null;

    public string $codigo_lote = '';

    public ?int $cantidad_ingresada = null;

    public ?string $fecha_vencimiento = null;

    public string $precio_compra = '';

    public string $precio_venta = '';

    // Buscador interactivo de producto en el modal
    public string $buscarProducto = '';

    // Modal Ajuste / Merma de Lote
    public bool $modalAjusteOpen = false;

    public ?int $loteAjusteId = null;

    public string $tipoAjuste = 'Merma por Vencimiento';

    public int $cantidadAjuste = 1;

    public string $motivoAjuste = '';

    protected function rules(): array
    {
        return [
            'sucursal_id' => 'required|exists:sucursales,id',
            'producto_id' => 'required|exists:productos,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'codigo_lote' => 'required|string|max:100',
            'cantidad_ingresada' => 'required|integer|min:1',
            'fecha_vencimiento' => 'nullable|date',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'sucursal_id.required' => 'Debe seleccionar la sucursal de destino del lote.',
        'producto_id.required' => 'Debe seleccionar el medicamento o insumo médico.',
        'codigo_lote.required' => 'El código o número de lote es obligatorio.',
        'cantidad_ingresada.required' => 'La cantidad ingresada debe ser mayor a 0.',
        'precio_compra.required' => 'El precio de compra unitario es obligatorio.',
        'precio_venta.required' => 'El precio de venta unitario es obligatorio.',
    ];

    public function mount(): void
    {
        // Asignar sucursal por defecto del usuario
        $this->sucursal_id = Auth::user()->sucursal_id ?? Sucursal::first()?->id;
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

    public function updatingFiltroProveedor(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroVencimiento(): void
    {
        $this->resetPage();
    }

    public function limpiarFormularioLote(): void
    {
        $this->sucursal_id = Auth::user()->sucursal_id ?? Sucursal::first()?->id;
        $this->producto_id = null;
        $this->proveedor_id = null;
        $this->codigo_lote = 'LOT-'.strtoupper(substr(uniqid(), -6));
        $this->cantidad_ingresada = null;
        $this->fecha_vencimiento = null;
        $this->precio_compra = '';
        $this->precio_venta = '';
        $this->buscarProducto = '';
        $this->resetValidation();
    }

    public function abrirModalLote(): void
    {
        $this->limpiarFormularioLote();
        $this->modalLoteOpen = true;
    }

    public function cerrarModalLote(): void
    {
        $this->modalLoteOpen = false;
        $this->limpiarFormularioLote();
    }

    public function seleccionarProducto(int $id): void
    {
        $this->producto_id = $id;
        $prod = Producto::find($id);
        if ($prod) {
            $this->precio_venta = (string) $prod->ultimo_precio_venta;
            $ultimoLote = Lote::where('producto_id', $id)->latest('id')->first();
            if ($ultimoLote && empty($this->precio_compra)) {
                $this->precio_compra = (string) $ultimoLote->precio_compra;
            }
        }
    }

    public function guardarLote(): void
    {
        $this->validate();

        DB::transaction(function () {
            // 1. Crear el Lote
            $lote = Lote::create([
                'sucursal_id' => $this->sucursal_id,
                'producto_id' => $this->producto_id,
                'proveedor_id' => $this->proveedor_id,
                'codigo_lote' => trim($this->codigo_lote),
                'cantidad_ingresada' => $this->cantidad_ingresada,
                'cantidad_actual' => $this->cantidad_ingresada,
                'fecha_vencimiento' => $this->fecha_vencimiento,
                'precio_compra' => (float) $this->precio_compra,
                'precio_venta' => (float) $this->precio_venta,
            ]);

            // 2. Generar Movimiento Automático de Entrada en Kardex
            MovimientoInventario::create([
                'sucursal_id' => $lote->sucursal_id,
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'cantidad' => $lote->cantidad_ingresada,
                'tipo_movimiento' => 'Entrada Compra',
                'user_id' => Auth::id(),
            ]);

            // 3. Observer: Actualizar último precio de venta en el producto padre
            $producto = Producto::find($this->producto_id);
            if ($producto && (float) $this->precio_venta > 0) {
                $producto->update([
                    'ultimo_precio_venta' => (float) $this->precio_venta,
                ]);
            }
        });

        $this->cerrarModalLote();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Lote Registrado!',
            'message' => 'Lote ingresado y stock incorporado al inventario con su asiento en Kardex.',
        ]);
    }

    public function abrirModalAjuste(int $loteId): void
    {
        $lote = Lote::findOrFail($loteId);
        $this->loteAjusteId = $loteId;
        $this->tipoAjuste = ($lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast())
            ? 'Merma por Vencimiento'
            : 'Ajuste de Inventario';
        $this->cantidadAjuste = min(1, $lote->cantidad_actual);
        $this->motivoAjuste = '';
        $this->modalAjusteOpen = true;
    }

    public function cerrarModalAjuste(): void
    {
        $this->modalAjusteOpen = false;
        $this->loteAjusteId = null;
    }

    public function procesarAjuste(): void
    {
        $this->validate([
            'loteAjusteId' => 'required|exists:lotes,id',
            'tipoAjuste' => 'required|string',
            'cantidadAjuste' => 'required|integer|min:1',
            'motivoAjuste' => 'required|string|min:3|max:255',
        ], [
            'motivoAjuste.required' => 'Debe ingresar la justificación clínica o motivo de la baja/ajuste.',
            'cantidadAjuste.min' => 'La cantidad a dar de baja debe ser de al menos 1 unidad.',
        ]);

        $lote = Lote::findOrFail($this->loteAjusteId);

        if ($this->cantidadAjuste > $lote->cantidad_actual) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Cantidad no disponible',
                'message' => "El lote solo dispone de {$lote->cantidad_actual} unidades.",
            ]);

            return;
        }

        DB::transaction(function () use ($lote) {
            // Descontar del lote
            $lote->decrement('cantidad_actual', $this->cantidadAjuste);

            // Asentar salida en Kardex
            MovimientoInventario::create([
                'sucursal_id' => $lote->sucursal_id,
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'cantidad' => $this->cantidadAjuste,
                'tipo_movimiento' => $this->tipoAjuste,
                'user_id' => Auth::id(),
            ]);
        });

        $this->cerrarModalAjuste();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => 'Ajuste Realizado',
            'message' => "Se registraron {$this->cantidadAjuste} unidades de baja bajo el concepto '{$this->tipoAjuste}'.",
        ]);
    }

    public function render(): View
    {
        $query = Lote::query()
            ->with(['producto.marca', 'sucursal', 'proveedor']);

        if (! empty($this->search)) {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($search) {
                $q->where('codigo_lote', 'like', $search)
                    ->orWhereHas('producto', function (Builder $p) use ($search) {
                        $p->where('nombre', 'like', $search);
                    })
                    ->orWhereHas('proveedor', function (Builder $pr) use ($search) {
                        $pr->where('razon_social', 'like', $search);
                    });
            });
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroProveedor) {
            $query->where('proveedor_id', $this->filtroProveedor);
        }

        $hoy = now()->toDateString();
        $proximoLimite = now()->addDays(60)->toDateString();

        if ($this->filtroVencimiento === 'vencidos') {
            $query->where('fecha_vencimiento', '<', $hoy)->where('cantidad_actual', '>', 0);
        } elseif ($this->filtroVencimiento === 'proximos') {
            $query->whereBetween('fecha_vencimiento', [$hoy, $proximoLimite])->where('cantidad_actual', '>', 0);
        } elseif ($this->filtroVencimiento === 'vigentes') {
            $query->where('fecha_vencimiento', '>', $proximoLimite)->where('cantidad_actual', '>', 0);
        } elseif ($this->filtroVencimiento === 'agotados') {
            $query->where('cantidad_actual', '<=', 0);
        }

        $lotes = $query->orderBy('fecha_vencimiento', 'asc')->paginate($this->perPage);

        // Métricas
        $totalLotesActivos = Lote::where('cantidad_actual', '>', 0)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();
        $lotesPorVencer = Lote::whereBetween('fecha_vencimiento', [$hoy, $proximoLimite])
            ->where('cantidad_actual', '>', 0)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();
        $lotesVencidos = Lote::where('fecha_vencimiento', '<', $hoy)
            ->where('cantidad_actual', '>', 0)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->count();

        // Valorización de Stock:
        // Se determina el último lote registrado para cada producto (último precio de compra y venta)
        // y se multiplica por el stock actual de dicho producto.
        $ultimosLotes = Lote::query()
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('lotes')
                    ->whereNull('deleted_at')
                    ->groupBy('producto_id');
            })
            ->get()
            ->keyBy('producto_id');

        $productosConStock = Producto::query()
            ->whereHas('lotes', function ($q) {
                $q->where('cantidad_actual', '>', 0)
                    ->when($this->filtroSucursal, fn ($sq) => $sq->where('sucursal_id', $this->filtroSucursal));
            })
            ->with(['lotes' => function ($q) {
                $q->where('cantidad_actual', '>', 0)
                    ->when($this->filtroSucursal, fn ($sq) => $sq->where('sucursal_id', $this->filtroSucursal));
            }])
            ->get();

        $valorStockCompra = 0.0;
        $valorStockVenta = 0.0;

        foreach ($productosConStock as $prod) {
            $stockProd = (int) $prod->lotes->sum('cantidad_actual');
            if ($stockProd <= 0) {
                continue;
            }

            $loteReciente = $ultimosLotes->get($prod->id);
            $ultimoPrecioCompra = $loteReciente ? (float) $loteReciente->precio_compra : 0.0;
            $ultimoPrecioVenta = (float) ($prod->ultimo_precio_venta ?? 0);
            if ($ultimoPrecioVenta <= 0 && $loteReciente) {
                $ultimoPrecioVenta = (float) $loteReciente->precio_venta;
            }

            $valorStockCompra += ($stockProd * $ultimoPrecioCompra);
            $valorStockVenta += ($stockProd * $ultimoPrecioVenta);
        }

        // Colecciones auxiliares
        $sucursales = Sucursal::orderBy('nombre')->get();
        $proveedores = Proveedor::orderBy('razon_social')->get();

        // Búsqueda de productos en modal de lote
        $productosEncontrados = collect();
        if ($this->modalLoteOpen) {
            $pQuery = Producto::with('marca')->orderBy('nombre');
            if (! empty($this->buscarProducto)) {
                $pSearch = '%'.trim($this->buscarProducto).'%';
                $pQuery->where('nombre', 'like', $pSearch);
            }
            $productosEncontrados = $pQuery->take(10)->get();
        }

        $productoSeleccionado = $this->producto_id ? Producto::with('marca')->find($this->producto_id) : null;

        // Lote en ajuste
        $loteAjuste = $this->loteAjusteId ? Lote::with('producto')->find($this->loteAjusteId) : null;

        return view('livewire.farmacia.lotes-index', [
            'lotes' => $lotes,
            'totalLotesActivos' => $totalLotesActivos,
            'lotesPorVencer' => $lotesPorVencer,
            'lotesVencidos' => $lotesVencidos,
            'valorStockCompra' => $valorStockCompra,
            'valorStockVenta' => $valorStockVenta,
            'valorTotalInventario' => $valorStockVenta,
            'sucursales' => $sucursales,
            'proveedores' => $proveedores,
            'productosEncontrados' => $productosEncontrados,
            'productoSeleccionado' => $productoSeleccionado,
            'loteAjuste' => $loteAjuste,
        ]);
    }
}
