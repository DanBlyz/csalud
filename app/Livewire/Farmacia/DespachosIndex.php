<?php

namespace App\Livewire\Farmacia;

use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class DespachosIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursal = null;

    public string $filtroEstadoDespacho = 'pendientes'; // 'pendientes', 'completadas', 'todas'

    // Modal de Despacho de Receta
    public bool $modalDespachoOpen = false;

    public ?int $recetaId = null;

    public array $despachosItems = []; // [detalle_id => ['lote_id' => X, 'cantidad_despachar' => Y]]

    // Insumos y Medicamentos Extras / Adicionales
    public array $extrasItems = [];

    public ?int $extra_producto_id = null;

    public ?int $extra_lote_id = null;

    public int $extra_cantidad = 1;

    public ?string $extra_observaciones = null;

    public string $buscarExtraProducto = '';

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

    public function updatingFiltroEstadoDespacho(): void
    {
        $this->resetPage();
    }

    public function seleccionarProductoExtra(int $productoId): void
    {
        $this->extra_producto_id = $productoId;
        $this->buscarExtraProducto = '';
        $this->updatedExtraProductoId($productoId);
    }

    public function limpiarProductoExtra(): void
    {
        $this->extra_producto_id = null;
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;
        $this->extra_observaciones = null;
        $this->buscarExtraProducto = '';
    }

    public function updatedExtraProductoId($val): void
    {
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;

        if ($val && $this->recetaId) {
            $receta = Receta::with('proforma')->find($this->recetaId);
            $sucursalId = $receta?->proforma?->sucursal_id ?? Auth::user()->sucursal_id;

            $primerLote = Lote::where('producto_id', $val)
                ->where('sucursal_id', $sucursalId)
                ->where('cantidad_actual', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            $this->extra_lote_id = $primerLote?->id ?? null;
        }
    }

    public function agregarItemExtra(): void
    {
        $this->validate([
            'extra_producto_id' => ['required', 'exists:productos,id'],
            'extra_lote_id' => ['required', 'exists:lotes,id'],
            'extra_cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'extra_producto_id.required' => 'Seleccione un insumo o medicamento.',
            'extra_lote_id.required' => 'Seleccione un lote con existencias.',
            'extra_cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        $lote = Lote::with('producto')->find($this->extra_lote_id);
        if (! $lote || $lote->cantidad_actual < $this->extra_cantidad) {
            $disp = $lote ? $lote->cantidad_actual : 0;
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Stock insuficiente',
                'message' => "El lote seleccionado solo dispone de {$disp} unidades.",
            ]);

            return;
        }

        // Si ya existe en la lista de extras con el mismo lote, acumular cantidad
        $yaExiste = false;
        foreach ($this->extrasItems as $key => $item) {
            if ($item['lote_id'] === $lote->id) {
                $nuevaCantidad = $item['cantidad'] + $this->extra_cantidad;
                if ($nuevaCantidad > $lote->cantidad_actual) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Stock insuficiente',
                        'message' => "La cantidad acumulada ({$nuevaCantidad}) excede las existencias del lote ({$lote->cantidad_actual}).",
                    ]);

                    return;
                }
                $this->extrasItems[$key]['cantidad'] = $nuevaCantidad;
                $this->extrasItems[$key]['subtotal'] = round($nuevaCantidad * $item['precio_unitario'], 2);
                $yaExiste = true;
                break;
            }
        }

        if (! $yaExiste) {
            $precio = (float) ($lote->producto->ultimo_precio_venta ?? 0);
            $this->extrasItems[] = [
                'producto_id' => $lote->producto_id,
                'producto_nombre' => $lote->producto->nombre,
                'unidad_medida' => $lote->producto->unidad_medida ?? 'Unidad',
                'lote_id' => $lote->id,
                'lote_codigo' => $lote->codigo_lote,
                'lote_vencimiento' => $lote->fecha_vencimiento?->format('d/m/Y') ?? 'S/F',
                'stock_disponible' => $lote->cantidad_actual,
                'cantidad' => $this->extra_cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($this->extra_cantidad * $precio, 2),
                'observaciones' => $this->extra_observaciones,
            ];
        }

        $this->extra_producto_id = null;
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;
        $this->extra_observaciones = null;
        $this->buscarExtraProducto = '';
    }

    public function eliminarItemExtra(int $index): void
    {
        unset($this->extrasItems[$index]);
        $this->extrasItems = array_values($this->extrasItems);
    }

    public function abrirModalDespacho(int $recetaId): void
    {
        $this->resetValidation();
        $this->recetaId = $recetaId;

        $receta = Receta::with(['detalles.producto', 'proforma.paciente'])->findOrFail($recetaId);
        $sucursalId = $receta->proforma->sucursal_id ?? Auth::user()->sucursal_id;

        $this->despachosItems = [];
        $this->extrasItems = [];
        $this->extra_producto_id = null;
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;
        $this->extra_observaciones = null;
        $this->buscarExtraProducto = '';

        foreach ($receta->detalles as $det) {
            // Calcular unidades ya despachadas por MovimientoInventario
            $despachadasPrevias = (int) MovimientoInventario::where('receta_id', $receta->id)
                ->where('producto_id', $det->producto_id)
                ->where('tipo_movimiento', 'Salida Receta')
                ->sum('cantidad');

            $saldoPendiente = max(0, $det->cantidad - $despachadasPrevias);

            // Buscar el mejor lote disponible según política (primer vencimiento)
            $loteSugerido = Lote::where('producto_id', $det->producto_id)
                ->where('sucursal_id', $sucursalId)
                ->where('cantidad_actual', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            $this->despachosItems[$det->id] = [
                'detalle_id' => $det->id,
                'producto_id' => $det->producto_id,
                'producto_nombre' => $det->producto->nombre ?? 'Medicamento',
                'unidad_medida' => $det->producto->unidad_medida ?? 'Unidad',
                'indicaciones' => $det->indicaciones,
                'cantidad_prescrita' => $det->cantidad,
                'despachadas_previas' => $despachadasPrevias,
                'saldo_pendiente' => $saldoPendiente,
                'lote_id' => $loteSugerido?->id ?? null,
                'cantidad_despachar' => $saldoPendiente > 0 && $loteSugerido ? 1 : 0, // Por defecto despacha 1 unidad si hay saldo
            ];
        }

        $this->modalDespachoOpen = true;
    }

    public function cerrarModalDespacho(): void
    {
        $this->modalDespachoOpen = false;
        $this->recetaId = null;
        $this->despachosItems = [];
        $this->extrasItems = [];
        $this->extra_producto_id = null;
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;
        $this->extra_observaciones = null;
        $this->buscarExtraProducto = '';
        $this->resetValidation();
    }

    public function procesarDespacho(): void
    {
        $receta = Receta::with(['proforma', 'detalles'])->findOrFail($this->recetaId);

        // Validar ítems de prescripción
        $hayDespachosPrescritos = false;
        foreach ($this->despachosItems as $item) {
            $cant = (int) ($item['cantidad_despachar'] ?? 0);
            if ($cant > 0) {
                $hayDespachosPrescritos = true;
                if (empty($item['lote_id'])) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Falta Lote',
                        'message' => "Debe seleccionar un lote con stock para {$item['producto_nombre']}.",
                    ]);

                    return;
                }

                $lote = Lote::find($item['lote_id']);
                if (! $lote || $lote->cantidad_actual < $cant) {
                    $stockDisp = $lote ? $lote->cantidad_actual : 0;
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Stock insuficiente',
                        'message' => "El lote seleccionado para {$item['producto_nombre']} solo tiene {$stockDisp} unidades disponibles.",
                    ]);

                    return;
                }

                if ($cant > $item['saldo_pendiente']) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Excede prescripción',
                        'message' => "La cantidad a despachar ({$cant}) excede el saldo pendiente ({$item['saldo_pendiente']}) de {$item['producto_nombre']}.",
                    ]);

                    return;
                }
            }
        }

        $hayExtras = ! empty($this->extrasItems);

        if (! $hayDespachosPrescritos && ! $hayExtras) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Sin unidades',
                'message' => 'Ingrese al menos una unidad a despachar en la receta o añada algún insumo extra.',
            ]);

            return;
        }

        // Validar existencias de cada extra
        foreach ($this->extrasItems as $extra) {
            $loteExtra = Lote::find($extra['lote_id']);
            if (! $loteExtra || $loteExtra->cantidad_actual < $extra['cantidad']) {
                $this->dispatch('swal', [
                    'type' => 'error',
                    'title' => 'Stock insuficiente en extras',
                    'message' => "El insumo extra {$extra['producto_nombre']} no cuenta con suficiente stock en el lote.",
                ]);

                return;
            }
        }

        DB::transaction(function () use ($receta) {
            // 1. Procesar entregas de prescripción médica
            foreach ($this->despachosItems as $detId => $item) {
                $cant = (int) ($item['cantidad_despachar'] ?? 0);
                if ($cant <= 0) {
                    continue;
                }

                $lote = Lote::findOrFail($item['lote_id']);

                // Descontar stock del lote
                $lote->decrement('cantidad_actual', $cant);

                // Crear Movimiento de Salida en Kardex
                MovimientoInventario::create([
                    'sucursal_id' => $lote->sucursal_id,
                    'producto_id' => $lote->producto_id,
                    'lote_id' => $lote->id,
                    'cantidad' => $cant,
                    'tipo_movimiento' => 'Salida Receta',
                    'receta_id' => $receta->id,
                    'proforma_id' => $receta->proforma_id,
                    'user_id' => Auth::id(),
                ]);

                // Verificar si se completó la totalidad de la prescripción para este detalle
                $detalle = RecetaDetalle::find($detId);
                if ($detalle) {
                    $totalAcumulado = (int) MovimientoInventario::where('receta_id', $receta->id)
                        ->where('producto_id', $detalle->producto_id)
                        ->where('tipo_movimiento', 'Salida Receta')
                        ->sum('cantidad');

                    if ($totalAcumulado >= $detalle->cantidad) {
                        $detalle->update(['despachado' => true]);
                    }
                }
            }

            // 2. Procesar insumos y medicamentos extras
            foreach ($this->extrasItems as $extra) {
                $loteExtra = Lote::findOrFail($extra['lote_id']);

                // Descontar stock del lote
                $loteExtra->decrement('cantidad_actual', $extra['cantidad']);

                // Asiento en Kardex de salida por consumo extra
                MovimientoInventario::create([
                    'sucursal_id' => $loteExtra->sucursal_id,
                    'producto_id' => $extra['producto_id'],
                    'lote_id' => $loteExtra->id,
                    'cantidad' => $extra['cantidad'],
                    'tipo_movimiento' => 'Consumo Extra',
                    'receta_id' => $receta->id,
                    'proforma_id' => $receta->proforma_id,
                    'user_id' => Auth::id(),
                ]);

                // Asiento de ConsumoExtra en la proforma
                ConsumoExtra::create([
                    'proforma_id' => $receta->proforma_id,
                    'producto_id' => $extra['producto_id'],
                    'cantidad' => $extra['cantidad'],
                    'precio_unitario' => $extra['precio_unitario'],
                    'user_id' => Auth::id(),
                    'observaciones' => ! empty($extra['observaciones'])
                        ? $extra['observaciones']
                        : "Despacho extra de farmacia (Receta #{$receta->id})",
                ]);
            }

            // 3. Recalcular costo total consolidado de la proforma
            if ($receta->proforma) {
                $receta->proforma->recalcularTotal();
            }
        });

        $this->cerrarModalDespacho();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Dispensación Realizada!',
            'message' => 'Despacho registrado en Kardex y cargado al costo cobrable de la proforma.',
        ]);
    }

    public function render(): View
    {
        // Traer recetas ACTIVAS de proformas EN CURSO
        $query = Receta::query()
            ->where('activo', true)
            ->whereHas('proforma', function (Builder $p) {
                $p->where('estado', 'En Curso');
            })
            ->with([
                'proforma.paciente',
                'proforma.sucursal',
                'doctor',
                'detalles.producto',
            ]);

        if (! empty($this->search)) {
            $search = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($search) {
                $q->whereHas('proforma.paciente', function (Builder $pac) use ($search) {
                    $pac->where('nombres', 'like', $search)
                        ->orWhere('apellido_paterno', 'like', $search)
                        ->orWhere('cedula', 'like', $search);
                })
                    ->orWhereHas('doctor', function (Builder $doc) use ($search) {
                        $doc->where('name', 'like', $search);
                    })
                    ->orWhere('id', 'like', $search);
            });
        }

        if ($this->filtroSucursal) {
            $query->whereHas('proforma', function (Builder $p) {
                $p->where('sucursal_id', $this->filtroSucursal);
            });
        }

        if ($this->filtroEstadoDespacho === 'pendientes') {
            $query->whereHas('detalles', function (Builder $d) {
                $d->where('despachado', false);
            });
        } elseif ($this->filtroEstadoDespacho === 'completadas') {
            $query->whereDoesntHave('detalles', function (Builder $d) {
                $d->where('despachado', false);
            });
        }

        $recetas = $query->latest('id')->paginate($this->perPage);

        // Métricas rápidas
        $sucursales = Sucursal::orderBy('nombre')->get();
        $totalPendientes = Receta::where('activo', true)
            ->whereHas('proforma', fn ($p) => $p->where('estado', 'En Curso'))
            ->whereHas('detalles', fn ($d) => $d->where('despachado', false))
            ->count();

        // Lotes e insumos disponibles para el modal de despacho
        $lotesDisponiblesPorItem = [];
        $productosParaExtras = collect();
        $lotesParaExtra = collect();

        if ($this->modalDespachoOpen && $this->recetaId) {
            $receta = Receta::with('proforma')->find($this->recetaId);
            $sucursalId = $receta?->proforma?->sucursal_id ?? Auth::user()->sucursal_id;

            foreach ($this->despachosItems as $detId => $item) {
                $lotesDisponiblesPorItem[$detId] = Lote::where('producto_id', $item['producto_id'])
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();
            }

            // Catálogo disponible para agregar como insumo o medicamento extra con búsqueda reactiva
            $pQuery = Producto::whereHas('lotes', function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
            })->with(['marca', 'lotes' => function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('cantidad_actual', '>', 0);
            }]);

            if (! empty($this->buscarExtraProducto)) {
                $searchExtra = '%'.trim($this->buscarExtraProducto).'%';
                $pQuery->where(function ($q) use ($searchExtra) {
                    $q->where('nombre', 'like', $searchExtra)
                        ->orWhere('descripcion', 'like', $searchExtra)
                        ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', $searchExtra));
                });
                $productosParaExtras = $pQuery->orderBy('nombre')->take(10)->get();
            } else {
                // Sugerencias inmediatas de insumos con stock
                $productosParaExtras = $pQuery->orderBy('nombre')->take(8)->get();
            }

            $productoExtraSeleccionado = $this->extra_producto_id
                ? Producto::with('marca')->find($this->extra_producto_id)
                : null;

            if ($this->extra_producto_id) {
                $lotesParaExtra = Lote::where('producto_id', $this->extra_producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();
            }
        }

        return view('livewire.farmacia.despachos-index', [
            'recetas' => $recetas,
            'totalPendientes' => $totalPendientes,
            'sucursales' => $sucursales,
            'lotesDisponiblesPorItem' => $lotesDisponiblesPorItem,
            'productosParaExtras' => $productosParaExtras,
            'productoExtraSeleccionado' => $productoExtraSeleccionado ?? null,
            'lotesParaExtra' => $lotesParaExtra,
        ]);
    }
}
