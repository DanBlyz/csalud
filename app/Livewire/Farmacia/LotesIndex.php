<?php

namespace App\Livewire\Farmacia;

use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Seccion;
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

    public ?int $filtroSeccion = null;

    public ?int $filtroProveedor = null;

    public string $filtroVencimiento = ''; // '' = Todos, 'vigentes', 'proximos', 'vencidos', 'agotados'

    // Modal Registro de Lote
    public bool $modalLoteOpen = false;

    public ?int $sucursal_id = null;

    public ?int $seccion_ingreso_id = null;

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

    public ?int $seccionAjusteId = null;

    public string $tipoAjuste = 'Merma por Vencimiento';

    public int $cantidadAjuste = 1;

    public string $motivoAjuste = '';

    // Modal Transferencia entre Secciones Hospitalarias
    public bool $modalTransferenciaOpen = false;

    public ?int $loteTransferenciaId = null;

    public ?int $seccion_origen_id = null;

    public ?int $seccion_destino_id = null;

    public ?int $cantidad_transferir = 1;

    public string $motivo_transferencia = 'Transferencia interna a área de atención';

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
        $this->filtroSeccion = null;
        $this->resetPage();
    }

    public function updatingFiltroSeccion(): void
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
        $this->seccion_ingreso_id = Seccion::where('sucursal_id', $this->sucursal_id)
            ->where('es_almacen_principal', true)
            ->value('id') ?? Seccion::where('sucursal_id', $this->sucursal_id)->value('id');
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

    public function updatedSucursalId(int $val): void
    {
        $this->seccion_ingreso_id = Seccion::where('sucursal_id', $val)
            ->where('es_almacen_principal', true)
            ->value('id') ?? Seccion::where('sucursal_id', $val)->value('id');
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
        $this->validate([
            'sucursal_id' => 'required|exists:sucursales,id',
            'seccion_ingreso_id' => 'nullable|exists:secciones,id',
            'producto_id' => 'required|exists:productos,id',
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'codigo_lote' => 'required|string|max:100',
            'cantidad_ingresada' => 'required|integer|min:1',
            'fecha_vencimiento' => 'nullable|date',
            'precio_compra' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',
        ]);

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

            // Determinar sección de ingreso (por defecto almacén principal de la sede)
            $seccionDestinoId = $this->seccion_ingreso_id
                ?? Seccion::where('sucursal_id', $lote->sucursal_id)->where('es_almacen_principal', true)->value('id')
                ?? Seccion::where('sucursal_id', $lote->sucursal_id)->value('id');

            if ($seccionDestinoId) {
                // Sincronizar o actualizar el lote_secciones generado
                $ls = LoteSeccion::where('lote_id', $lote->id)->first();
                if ($ls) {
                    $ls->update(['seccion_id' => $seccionDestinoId]);
                } else {
                    LoteSeccion::create([
                        'lote_id' => $lote->id,
                        'seccion_id' => $seccionDestinoId,
                        'cantidad_actual' => $lote->cantidad_actual,
                    ]);
                }
            }

            // 2. Generar Movimiento Automático de Entrada en Kardex con destino de sección
            MovimientoInventario::create([
                'sucursal_id' => $lote->sucursal_id,
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'cantidad' => $lote->cantidad_ingresada,
                'tipo_movimiento' => 'Entrada Compra',
                'seccion_destino_id' => $seccionDestinoId,
                'user_id' => Auth::id(),
            ]);

            // 3. Actualizar último precio de venta en el producto padre
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
        $lote = Lote::with('loteSecciones.seccion')->findOrFail($loteId);
        $this->loteAjusteId = $loteId;
        $this->tipoAjuste = ($lote->fecha_vencimiento && $lote->fecha_vencimiento->isPast())
            ? 'Merma por Vencimiento'
            : 'Ajuste de Inventario';

        // Preseleccionar sección con existencias
        $primeraConStock = $lote->loteSecciones->where('cantidad_actual', '>', 0)->first();
        $this->seccionAjusteId = $primeraConStock?->seccion_id;
        $this->cantidadAjuste = min(1, $lote->cantidad_actual);
        $this->motivoAjuste = '';
        $this->modalAjusteOpen = true;
    }

    public function cerrarModalAjuste(): void
    {
        $this->modalAjusteOpen = false;
        $this->loteAjusteId = null;
        $this->seccionAjusteId = null;
    }

    public function procesarAjuste(): void
    {
        $this->validate([
            'loteAjusteId' => 'required|exists:lotes,id',
            'seccionAjusteId' => 'nullable|exists:secciones,id',
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
                'message' => "El lote solo dispone de {$lote->cantidad_actual} unidades consolidadas.",
            ]);

            return;
        }

        if ($this->seccionAjusteId) {
            $ls = LoteSeccion::where('lote_id', $lote->id)
                ->where('seccion_id', $this->seccionAjusteId)
                ->first();

            if (! $ls || $ls->cantidad_actual < $this->cantidadAjuste) {
                $disp = $ls ? $ls->cantidad_actual : 0;
                $this->dispatch('swal', [
                    'type' => 'error',
                    'title' => 'Stock insuficiente en la sección',
                    'message' => "La sección seleccionada solo dispone de {$disp} unidades para dar de baja.",
                ]);

                return;
            }
        }

        DB::transaction(function () use ($lote) {
            // Descontar del lote consolidado
            $lote->decrement('cantidad_actual', $this->cantidadAjuste);

            // Descontar de la sección específica
            if ($this->seccionAjusteId) {
                LoteSeccion::where('lote_id', $lote->id)
                    ->where('seccion_id', $this->seccionAjusteId)
                    ->decrement('cantidad_actual', $this->cantidadAjuste);
            } else {
                $ls = LoteSeccion::where('lote_id', $lote->id)
                    ->where('cantidad_actual', '>=', $this->cantidadAjuste)
                    ->first();
                if ($ls) {
                    $ls->decrement('cantidad_actual', $this->cantidadAjuste);
                }
            }

            // Asentar salida en Kardex
            MovimientoInventario::create([
                'sucursal_id' => $lote->sucursal_id,
                'producto_id' => $lote->producto_id,
                'lote_id' => $lote->id,
                'cantidad' => $this->cantidadAjuste,
                'tipo_movimiento' => $this->tipoAjuste,
                'seccion_origen_id' => $this->seccionAjusteId,
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

    public function abrirModalTransferencia(int $loteId): void
    {
        $lote = Lote::with('loteSecciones.seccion')->findOrFail($loteId);
        $this->loteTransferenciaId = $loteId;

        // Sección origen por defecto (la que tenga mayor stock)
        $origenConStock = $lote->loteSecciones->where('cantidad_actual', '>', 0)->sortByDesc('cantidad_actual')->first();
        $this->seccion_origen_id = $origenConStock?->seccion_id;

        // Sección destino por defecto (otra sección de la misma sucursal)
        $otraSeccion = Seccion::where('sucursal_id', $lote->sucursal_id)
            ->where('activo', true)
            ->where('id', '!=', $this->seccion_origen_id)
            ->first();
        $this->seccion_destino_id = $otraSeccion?->id;

        $this->cantidad_transferir = 1;
        $this->motivo_transferencia = 'Transferencia interna para atención clínica';
        $this->resetValidation();
        $this->modalTransferenciaOpen = true;
    }

    public function cerrarModalTransferencia(): void
    {
        $this->modalTransferenciaOpen = false;
        $this->loteTransferenciaId = null;
        $this->seccion_origen_id = null;
        $this->seccion_destino_id = null;
        $this->cantidad_transferir = 1;
        $this->motivo_transferencia = '';
    }

    public function transferirStock(): void
    {
        $this->validate([
            'loteTransferenciaId' => 'required|exists:lotes,id',
            'seccion_origen_id' => 'required|exists:secciones,id',
            'seccion_destino_id' => 'required|exists:secciones,id|different:seccion_origen_id',
            'cantidad_transferir' => 'required|integer|min:1',
            'motivo_transferencia' => 'nullable|string|max:255',
        ], [
            'seccion_origen_id.required' => 'Debe seleccionar el área u hospital de origen.',
            'seccion_destino_id.required' => 'Debe seleccionar el área u hospital de destino.',
            'seccion_destino_id.different' => 'El área de destino debe ser distinta a la de origen.',
            'cantidad_transferir.required' => 'Debe ingresar la cantidad a transferir.',
            'cantidad_transferir.min' => 'La cantidad a transferir debe ser al menos 1 unidad.',
        ]);

        $lote = Lote::findOrFail($this->loteTransferenciaId);

        try {
            $lote->transferirASeccion(
                seccionOrigenId: $this->seccion_origen_id,
                seccionDestinoId: $this->seccion_destino_id,
                cantidad: $this->cantidad_transferir,
                motivo: $this->motivo_transferencia,
                userId: Auth::id()
            );

            $this->cerrarModalTransferencia();

            $this->dispatch('swal', [
                'type' => 'success',
                'title' => '¡Transferencia Exitosa!',
                'message' => "Se transfirieron {$this->cantidad_transferir} unidades entre áreas con su correspondiente registro en Kardex.",
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Error en Transferencia',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render(): View
    {
        $query = Lote::query()
            ->with(['producto.marca', 'sucursal', 'proveedor', 'loteSecciones.seccion']);

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

        if ($this->filtroSeccion) {
            $query->whereHas('loteSecciones', function ($sq) {
                $sq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0);
            });
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
            ->when($this->filtroSeccion, fn ($q) => $q->whereHas('loteSecciones', fn ($sq) => $sq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0)))
            ->count();
        $lotesPorVencer = Lote::whereBetween('fecha_vencimiento', [$hoy, $proximoLimite])
            ->where('cantidad_actual', '>', 0)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->when($this->filtroSeccion, fn ($q) => $q->whereHas('loteSecciones', fn ($sq) => $sq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0)))
            ->count();
        $lotesVencidos = Lote::where('fecha_vencimiento', '<', $hoy)
            ->where('cantidad_actual', '>', 0)
            ->when($this->filtroSucursal, fn ($q) => $q->where('sucursal_id', $this->filtroSucursal))
            ->when($this->filtroSeccion, fn ($q) => $q->whereHas('loteSecciones', fn ($sq) => $sq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0)))
            ->count();

        // Valorización de Stock:
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
                    ->when($this->filtroSucursal, fn ($sq) => $sq->where('sucursal_id', $this->filtroSucursal))
                    ->when($this->filtroSeccion, fn ($sq) => $sq->whereHas('loteSecciones', fn ($lsq) => $lsq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0)));
            })
            ->with(['lotes' => function ($q) {
                $q->where('cantidad_actual', '>', 0)
                    ->when($this->filtroSucursal, fn ($sq) => $sq->where('sucursal_id', $this->filtroSucursal))
                    ->when($this->filtroSeccion, fn ($sq) => $sq->whereHas('loteSecciones', fn ($lsq) => $lsq->where('seccion_id', $this->filtroSeccion)->where('cantidad_actual', '>', 0)));
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

        // Secciones disponibles para la sucursal activa o seleccionada
        $seccionesSucursalId = $this->filtroSucursal ?? $this->sucursal_id ?? Auth::user()->sucursal_id ?? Sucursal::first()?->id;
        $secciones = Seccion::query()
            ->where('activo', true)
            ->when($seccionesSucursalId, fn ($q) => $q->where('sucursal_id', $seccionesSucursalId))
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre')
            ->get();

        // Secciones para modal de ingreso (basadas en la sucursal elegida en el modal)
        $seccionesModalIngreso = Seccion::query()
            ->where('activo', true)
            ->where('sucursal_id', $this->sucursal_id)
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre')
            ->get();

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
        $loteAjuste = $this->loteAjusteId ? Lote::with(['producto', 'loteSecciones.seccion'])->find($this->loteAjusteId) : null;

        // Lote en transferencia
        $loteTransferencia = $this->loteTransferenciaId ? Lote::with(['producto.marca', 'sucursal', 'loteSecciones.seccion'])->find($this->loteTransferenciaId) : null;

        // Secciones destino posibles para la transferencia
        $seccionesDestino = collect();
        if ($loteTransferencia) {
            $seccionesDestino = Seccion::where('sucursal_id', $loteTransferencia->sucursal_id)
                ->where('activo', true)
                ->where('id', '!=', $this->seccion_origen_id)
                ->orderBy('nombre')
                ->get();
        }

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
            'secciones' => $secciones,
            'seccionesModalIngreso' => $seccionesModalIngreso,
            'seccionesDestino' => $seccionesDestino,
            'productosEncontrados' => $productosEncontrados,
            'productoSeleccionado' => $productoSeleccionado,
            'loteAjuste' => $loteAjuste,
            'loteTransferencia' => $loteTransferencia,
        ]);
    }
}
