<?php

namespace App\Livewire\Farmacia;

use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
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

    public ?int $seccion_defecto_id = null;

    public array $despachosItems = []; // [detalle_id => ['lote_id' => X, 'seccion_id' => S, 'cantidad_despachar' => Y]]

    // Insumos y Medicamentos Extras / Adicionales
    public array $extrasItems = [];

    public ?int $extra_seccion_id = null;

    public ?int $extra_producto_id = null;

    public ?int $extra_lote_id = null;

    public int $extra_cantidad = 1;

    public ?string $extra_observaciones = null;

    public string $buscarExtraProducto = '';

    // =========================================================================
    // MODAL: CREAR RECETA MÉDICA
    // =========================================================================
    public bool $modalCrearRecetaOpen = false;

    public ?int $receta_proforma_id = null;

    public string $buscarProformaReceta = '';

    public ?int $receta_medico_id = null;

    public string $receta_observaciones = '';

    public array $receta_medicamentos = [];

    public ?int $receta_nuevo_producto_id = null;

    public string $receta_nuevo_producto_nombre = '';

    public string $receta_nuevo_unidad_medida = '';

    public string $receta_nuevo_marca_nombre = '';

    public int $receta_nuevo_stock_disponible = 0;

    public int $receta_nuevo_cantidad = 1;

    public string $receta_nuevo_indicaciones = '';

    public string $buscarMedicamentoReceta = '';

    // =========================================================================
    // MODAL: DESPACHAR INSUMOS EXTRAS DIRECTOS
    // =========================================================================
    public bool $modalDespachoExtraDirectoOpen = false;

    public ?int $extra_directo_proforma_id = null;

    public string $buscarProformaExtraDirecto = '';

    public ?int $extra_directo_seccion_id = null;

    public ?int $extra_directo_producto_id = null;

    public string $extra_directo_producto_nombre = '';

    public string $extra_directo_unidad_medida = '';

    public string $extra_directo_precio_unitario = '0.00';

    public int $extra_directo_stock_disponible = 0;

    public int $extra_directo_cantidad = 1;

    public string $extra_directo_observaciones = '';

    public string $buscarInsumoExtraDirecto = '';

    public array $cola_despachos_extras_directos = [];

    #[Computed]
    public function totalRecetaDespachar(): float
    {
        $total = 0.0;
        foreach ($this->despachosItems as $item) {
            $cant = (int) ($item['cantidad_despachar'] ?? 0);
            $precio = (float) ($item['precio_unitario'] ?? 0);
            $total += ($cant * $precio);
        }

        return round($total, 2);
    }

    #[Computed]
    public function totalExtrasDespachar(): float
    {
        return round((float) array_sum(array_column($this->extrasItems, 'subtotal')), 2);
    }

    #[Computed]
    public function costoTotalDespacho(): float
    {
        return round($this->totalRecetaDespachar + $this->totalExtrasDespachar, 2);
    }

    #[Computed]
    public function totalExtraDirectoDespachar(): float
    {
        return round((float) array_sum(array_column($this->cola_despachos_extras_directos, 'subtotal')), 2);
    }

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

    public function updatedSeccionDefectoId($val): void
    {
        if (! $val) {
            return;
        }

        $this->extra_seccion_id = (int) $val;

        foreach ($this->despachosItems as $detId => $item) {
            $this->cambiarSeccionItem((int) $detId, (int) $val);
        }

        if ($this->extra_producto_id) {
            $this->updatedExtraProductoId($this->extra_producto_id);
        }
    }

    public function updatedExtraSeccionId($val): void
    {
        if ($this->extra_producto_id) {
            $this->updatedExtraProductoId($this->extra_producto_id);
        }
    }

    public function cambiarSeccionItem(int $detId, int $seccionId): void
    {
        if (! isset($this->despachosItems[$detId])) {
            return;
        }

        $receta = Receta::with('proforma')->find($this->recetaId);
        $sucursalId = $receta?->proforma?->sucursal_id ?? Auth::user()->sucursal_id;

        $this->despachosItems[$detId]['seccion_id'] = $seccionId;

        $lote = Lote::where('producto_id', $this->despachosItems[$detId]['producto_id'])
            ->where('sucursal_id', $sucursalId)
            ->whereHas('loteSecciones', function ($q) use ($seccionId) {
                $q->where('seccion_id', $seccionId)->where('cantidad_actual', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->orderBy('fecha_vencimiento', 'asc')
            ->first();

        $this->despachosItems[$detId]['lote_id'] = $lote?->id ?? null;
        $stockDisp = $lote ? $lote->stockEnSeccion($seccionId) : 0;
        $saldo = $this->despachosItems[$detId]['saldo_pendiente'];

        if ($saldo > 0 && $lote && $stockDisp > 0) {
            if ($this->despachosItems[$detId]['cantidad_despachar'] <= 0) {
                $this->despachosItems[$detId]['cantidad_despachar'] = min($saldo, $stockDisp, 1);
            } elseif ($this->despachosItems[$detId]['cantidad_despachar'] > $stockDisp) {
                $this->despachosItems[$detId]['cantidad_despachar'] = min($saldo, $stockDisp);
            }
        } else {
            $this->despachosItems[$detId]['cantidad_despachar'] = 0;
        }
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

        if ($val && $this->recetaId && $this->extra_seccion_id) {
            $receta = Receta::with('proforma')->find($this->recetaId);
            $sucursalId = $receta?->proforma?->sucursal_id ?? Auth::user()->sucursal_id;

            $primerLote = Lote::where('producto_id', $val)
                ->where('sucursal_id', $sucursalId)
                ->whereHas('loteSecciones', function ($q) {
                    $q->where('seccion_id', $this->extra_seccion_id)->where('cantidad_actual', '>', 0);
                })
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            if (! $primerLote) {
                $primerLote = Lote::where('producto_id', $val)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->first();

                if ($primerLote && $this->extra_seccion_id) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $primerLote->id, 'seccion_id' => $this->extra_seccion_id],
                        ['cantidad_actual' => $primerLote->cantidad_actual]
                    );
                }
            }

            $this->extra_lote_id = $primerLote?->id ?? null;
        }
    }

    public function agregarItemExtra(): void
    {
        $this->validate([
            'extra_seccion_id' => ['required', 'exists:secciones,id'],
            'extra_producto_id' => ['required', 'exists:productos,id'],
            'extra_lote_id' => ['required', 'exists:lotes,id'],
            'extra_cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'extra_seccion_id.required' => 'Seleccione la sección o almacén de origen.',
            'extra_producto_id.required' => 'Seleccione un insumo o medicamento.',
            'extra_lote_id.required' => 'Seleccione un lote con existencias en esa área.',
            'extra_cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        $lote = Lote::with('producto')->find($this->extra_lote_id);
        $stockEnSeccion = $lote ? $lote->stockEnSeccion($this->extra_seccion_id) : 0;

        if (! $lote || $stockEnSeccion < $this->extra_cantidad) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Stock insuficiente en la sección',
                'message' => "El lote seleccionado solo dispone de {$stockEnSeccion} unidades en esta sección.",
            ]);

            return;
        }

        $seccion = Seccion::find($this->extra_seccion_id);

        // Si ya existe en la lista de extras con el mismo lote y misma sección, acumular cantidad
        $yaExiste = false;
        foreach ($this->extrasItems as $key => $item) {
            if ($item['lote_id'] === $lote->id && $item['seccion_id'] === $this->extra_seccion_id) {
                $nuevaCantidad = $item['cantidad'] + $this->extra_cantidad;
                if ($nuevaCantidad > $stockEnSeccion) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Stock insuficiente',
                        'message' => "La cantidad acumulada ({$nuevaCantidad}) excede las existencias del lote en la sección ({$stockEnSeccion}).",
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
                'seccion_id' => $this->extra_seccion_id,
                'seccion_nombre' => $seccion?->nombre ?? 'Almacén',
                'stock_disponible' => $stockEnSeccion,
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
        if (! Auth::user()?->tienePermiso('despachar-farmacia')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para despachar medicamentos e insumos de farmacia.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->recetaId = $recetaId;

        $receta = Receta::with(['detalles.producto', 'proforma.paciente'])->findOrFail($recetaId);
        $sucursalId = $receta->proforma->sucursal_id ?? Auth::user()->sucursal_id;

        $secciones = Seccion::where('sucursal_id', $sucursalId)
            ->where('activo', true)
            ->orderByDesc('es_almacen_principal')
            ->orderBy('nombre')
            ->get();

        if ($secciones->isEmpty()) {
            $central = Seccion::firstOrCreate(
                ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                ['nombre' => 'Farmacia Central', 'activo' => true]
            );
            $secciones = collect([$central]);
        }

        $this->seccion_defecto_id = $secciones->where('es_almacen_principal', true)->first()?->id ?? $secciones->first()?->id;
        $this->extra_seccion_id = $this->seccion_defecto_id;

        $this->despachosItems = [];
        $this->extrasItems = [];
        $this->extra_producto_id = null;
        $this->extra_lote_id = null;
        $this->extra_cantidad = 1;
        $this->extra_observaciones = null;
        $this->buscarExtraProducto = '';

        foreach ($receta->detalles as $det) {
            // Calcular unidades ya despachadas netas por MovimientoInventario (salidas menos devoluciones)
            $salidas = (int) MovimientoInventario::where('receta_id', $receta->id)
                ->where('producto_id', $det->producto_id)
                ->where('tipo_movimiento', 'Salida Receta')
                ->sum('cantidad');

            $devoluciones = (int) MovimientoInventario::where('receta_id', $receta->id)
                ->where('producto_id', $det->producto_id)
                ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])
                ->sum('cantidad');

            $despachadasPrevias = max(0, $salidas - $devoluciones);
            $saldoPendiente = max(0, $det->cantidad - $despachadasPrevias);

            // Determinar la sección inicial (preferir la sección por defecto si tiene stock, sino la primera con stock)
            $seccionElegida = $this->seccion_defecto_id;
            $loteSugerido = null;

            if ($seccionElegida) {
                $loteSugerido = Lote::where('producto_id', $det->producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->whereHas('loteSecciones', function ($q) use ($seccionElegida) {
                        $q->where('seccion_id', $seccionElegida)->where('cantidad_actual', '>', 0);
                    })
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->first();
            }

            // Si la sección por defecto no tiene stock de este medicamento, buscar si otra sección tiene
            if (! $loteSugerido) {
                $otroLote = Lote::where('producto_id', $det->producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->whereHas('loteSecciones', fn ($q) => $q->where('cantidad_actual', '>', 0))
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->with(['loteSecciones' => fn ($q) => $q->where('cantidad_actual', '>', 0)])
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->first();

                if ($otroLote && $otroLote->loteSecciones->isNotEmpty()) {
                    $loteSugerido = $otroLote;
                    $seccionElegida = $otroLote->loteSecciones->first()->seccion_id;
                }
            }

            // Fallback para lotes sin lote_secciones previo (legacy / factories en tests)
            if (! $loteSugerido) {
                $loteSugerido = Lote::where('producto_id', $det->producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->first();

                if ($loteSugerido && $seccionElegida) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $loteSugerido->id, 'seccion_id' => $seccionElegida],
                        ['cantidad_actual' => $loteSugerido->cantidad_actual]
                    );
                }
            }

            $stockEnSeccion = ($loteSugerido && $seccionElegida) ? $loteSugerido->stockEnSeccion($seccionElegida) : 0;
            $precioUnitario = (float) ($det->costo_unitario ?: ($loteSugerido?->precio_venta ?: ($det->producto->ultimo_precio_venta ?? 0)));

            $this->despachosItems[$det->id] = [
                'detalle_id' => $det->id,
                'producto_id' => $det->producto_id,
                'producto_nombre' => $det->producto->nombre ?? 'Medicamento',
                'unidad_medida' => $det->producto->unidad_medida ?? 'Unidad',
                'indicaciones' => $det->indicaciones,
                'cantidad_prescrita' => $det->cantidad,
                'despachadas_previas' => $despachadasPrevias,
                'saldo_pendiente' => $saldoPendiente,
                'seccion_id' => $seccionElegida,
                'lote_id' => $loteSugerido?->id ?? null,
                'cantidad_despachar' => $saldoPendiente > 0 && $loteSugerido && $stockEnSeccion > 0 ? 1 : 0,
                'precio_unitario' => $precioUnitario,
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
        if (! Auth::user()?->tienePermiso('despachar-farmacia')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para procesar despachos de farmacia.',
            ]);

            return;
        }

        $receta = Receta::with(['proforma', 'detalles'])->findOrFail($this->recetaId);

        // Validar ítems de prescripción
        $hayDespachosPrescritos = false;
        foreach ($this->despachosItems as $item) {
            $cant = (int) ($item['cantidad_despachar'] ?? 0);
            if ($cant > 0) {
                $hayDespachosPrescritos = true;
                if (empty($item['lote_id']) || empty($item['seccion_id'])) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Datos Incompletos',
                        'message' => "Debe seleccionar área y lote con stock para {$item['producto_nombre']}.",
                    ]);

                    return;
                }

                $lote = Lote::find($item['lote_id']);
                $stockEnSeccion = $lote ? $lote->stockEnSeccion($item['seccion_id']) : 0;

                if (! $lote || $stockEnSeccion < $cant) {
                    $this->dispatch('swal', [
                        'type' => 'error',
                        'title' => 'Stock insuficiente en área',
                        'message' => "El lote seleccionado para {$item['producto_nombre']} solo tiene {$stockEnSeccion} unidades en el área seleccionada.",
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

        // Validar existencias de cada extra en su sección asignada
        foreach ($this->extrasItems as $extra) {
            $loteExtra = Lote::find($extra['lote_id']);
            $stockEnSec = $loteExtra ? $loteExtra->stockEnSeccion($extra['seccion_id']) : 0;

            if (! $loteExtra || $stockEnSec < $extra['cantidad']) {
                $this->dispatch('swal', [
                    'type' => 'error',
                    'title' => 'Stock insuficiente en extras',
                    'message' => "El insumo extra {$extra['producto_nombre']} no cuenta con suficiente stock en {$extra['seccion_nombre']}.",
                ]);

                return;
            }
        }

        DB::transaction(function () use ($receta) {
            // 1. Procesar entregas de prescripción médica con descuento atómico por sección
            foreach ($this->despachosItems as $detId => $item) {
                $cant = (int) ($item['cantidad_despachar'] ?? 0);
                if ($cant <= 0) {
                    continue;
                }

                $lote = Lote::findOrFail($item['lote_id']);

                // Descontar tanto del lote consolidado como de la sección correspondiente
                $lote->descontarDeSeccion(
                    $item['seccion_id'],
                    $cant,
                    'Salida Receta',
                    $receta->proforma_id,
                    $receta->id,
                    Auth::id()
                );

                // Verificar si se completó la totalidad de la prescripción para este detalle (neto de devoluciones)
                $detalle = RecetaDetalle::find($detId);
                if ($detalle) {
                    $totalSalidas = (int) MovimientoInventario::where('receta_id', $receta->id)
                        ->where('producto_id', $detalle->producto_id)
                        ->where('tipo_movimiento', 'Salida Receta')
                        ->sum('cantidad');

                    $totalDevoluciones = (int) MovimientoInventario::where('receta_id', $receta->id)
                        ->where('producto_id', $detalle->producto_id)
                        ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])
                        ->sum('cantidad');

                    $totalNeto = max(0, $totalSalidas - $totalDevoluciones);

                    $detalle->update(['despachado' => $totalNeto >= $detalle->cantidad]);
                }
            }

            // 2. Procesar insumos y medicamentos extras
            foreach ($this->extrasItems as $extra) {
                $loteExtra = Lote::findOrFail($extra['lote_id']);

                $loteExtra->descontarDeSeccion(
                    $extra['seccion_id'],
                    $extra['cantidad'],
                    'Consumo Extra',
                    $receta->proforma_id,
                    $receta->id,
                    Auth::id()
                );

                // Asiento de ConsumoExtra en la proforma con indicación de la sección
                $obsTexto = ! empty($extra['observaciones'])
                    ? "[{$extra['seccion_nombre']}] {$extra['observaciones']}"
                    : "Despacho extra de {$extra['seccion_nombre']} (Receta #{$receta->id})";

                ConsumoExtra::create([
                    'proforma_id' => $receta->proforma_id,
                    'producto_id' => $extra['producto_id'],
                    'cantidad' => $extra['cantidad'],
                    'precio_unitario' => $extra['precio_unitario'],
                    'user_id' => Auth::id(),
                    'observaciones' => $obsTexto,
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
            'message' => 'Despacho registrado en Kardex por sección y cargado al costo cobrable de la proforma.',
        ]);
    }

    // =========================================================================
    // ACCIONES: MODAL CREAR RECETA MÉDICA
    // =========================================================================
    public function abrirModalCrearReceta(): void
    {
        if (! Auth::user()?->tienePermiso('despachar-farmacia') && ! Auth::user()?->tienePermiso('emitir-receta')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para prescribir recetas médicas.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->modalCrearRecetaOpen = true;
        $this->receta_proforma_id = null;
        $this->buscarProformaReceta = '';
        $this->receta_medico_id = Auth::id();
        $this->receta_observaciones = '';
        $this->receta_medicamentos = [];
        $this->limpiarMedicamentoSeleccionadoReceta();
    }

    public function cerrarModalCrearReceta(): void
    {
        $this->modalCrearRecetaOpen = false;
        $this->receta_proforma_id = null;
        $this->buscarProformaReceta = '';
        $this->receta_medicamentos = [];
        $this->limpiarMedicamentoSeleccionadoReceta();
        $this->resetValidation();
    }

    public function seleccionarProformaReceta(int $id): void
    {
        $proforma = Proforma::with(['paciente', 'sucursal', 'medicos'])->find($id);
        if (! $proforma || $proforma->estado !== 'En Curso') {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Proforma no disponible',
                'text' => 'La proforma no existe o no se encuentra en estado En Curso.',
            ]);

            return;
        }

        $this->receta_proforma_id = $proforma->id;
        $this->receta_medico_id = $proforma->medicos->first()?->id ?? Auth::id();
        $this->buscarProformaReceta = '';
        $this->limpiarMedicamentoSeleccionadoReceta();
    }

    public function limpiarProformaSeleccionadaReceta(): void
    {
        $this->receta_proforma_id = null;
        $this->buscarProformaReceta = '';
        $this->receta_medicamentos = [];
        $this->limpiarMedicamentoSeleccionadoReceta();
    }

    public function limpiarMedicamentoSeleccionadoReceta(): void
    {
        $this->receta_nuevo_producto_id = null;
        $this->receta_nuevo_producto_nombre = '';
        $this->receta_nuevo_unidad_medida = '';
        $this->receta_nuevo_marca_nombre = '';
        $this->receta_nuevo_stock_disponible = 0;
        $this->receta_nuevo_cantidad = 1;
        $this->receta_nuevo_indicaciones = '';
        $this->buscarMedicamentoReceta = '';
    }

    public function seleccionarMedicamentoReceta(int $productoId): void
    {
        $producto = Producto::with('marca')->find($productoId);
        if (! $producto) {
            return;
        }

        $prof = Proforma::find($this->receta_proforma_id);
        $sucId = $prof?->sucursal_id ?? Auth::user()?->sucursal_id;

        $stock = (int) Lote::where('producto_id', $producto->id)
            ->when($sucId, fn ($q) => $q->where('sucursal_id', $sucId))
            ->where('cantidad_actual', '>', 0)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->sum('cantidad_actual');

        if ($stock <= 0) {
            $this->limpiarMedicamentoSeleccionadoReceta();
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Producto Sin Stock',
                'text' => "El medicamento '{$producto->nombre}' no cuenta con existencias vigentes en farmacia.",
            ]);

            return;
        }

        $this->receta_nuevo_producto_id = $producto->id;
        $this->receta_nuevo_producto_nombre = $producto->nombre;
        $this->receta_nuevo_unidad_medida = $producto->unidad_medida ?? 'Unidad';
        $this->receta_nuevo_marca_nombre = $producto->marca?->nombre ?? '';
        $this->receta_nuevo_stock_disponible = $stock;
        $this->buscarMedicamentoReceta = '';
        $this->receta_nuevo_cantidad = 1;
        $this->receta_nuevo_indicaciones = '';
    }

    public function agregarMedicamentoAReceta(): void
    {
        if (! $this->receta_nuevo_producto_id) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Seleccione un Medicamento',
                'text' => 'Debe buscar y seleccionar un medicamento antes de agregarlo.',
            ]);

            return;
        }

        $this->validate([
            'receta_nuevo_producto_id' => ['required', 'exists:productos,id'],
            'receta_nuevo_cantidad' => ['required', 'integer', 'min:1'],
            'receta_nuevo_indicaciones' => ['required', 'string', 'min:2', 'max:255'],
        ], [
            'receta_nuevo_producto_id.required' => 'Seleccione un medicamento.',
            'receta_nuevo_cantidad.required' => 'Indique la cantidad a prescribir.',
            'receta_nuevo_cantidad.min' => 'La cantidad mínima es 1.',
            'receta_nuevo_indicaciones.required' => 'Indique la posología e indicaciones.',
        ]);

        $prof = Proforma::find($this->receta_proforma_id);
        $sucId = $prof?->sucursal_id ?? Auth::user()?->sucursal_id;

        $stock = (int) Lote::where('producto_id', $this->receta_nuevo_producto_id)
            ->when($sucId, fn ($q) => $q->where('sucursal_id', $sucId))
            ->where('cantidad_actual', '>', 0)
            ->where(function ($q) {
                $q->whereNull('fecha_vencimiento')
                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
            })
            ->sum('cantidad_actual');

        if ($stock <= 0) {
            $nombre = $this->receta_nuevo_producto_nombre;
            $this->limpiarMedicamentoSeleccionadoReceta();
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Producto Sin Stock',
                'text' => "El medicamento '{$nombre}' no cuenta con existencias vigentes en farmacia.",
            ]);

            return;
        }

        $yaExiste = false;
        foreach ($this->receta_medicamentos as $k => $item) {
            if ($item['producto_id'] == $this->receta_nuevo_producto_id) {
                $this->receta_medicamentos[$k]['cantidad'] += (int) $this->receta_nuevo_cantidad;
                $this->receta_medicamentos[$k]['indicaciones'] = trim($this->receta_nuevo_indicaciones);
                $yaExiste = true;
                break;
            }
        }

        if (! $yaExiste) {
            $this->receta_medicamentos[] = [
                'producto_id' => $this->receta_nuevo_producto_id,
                'producto_nombre' => $this->receta_nuevo_producto_nombre,
                'unidad_medida' => $this->receta_nuevo_unidad_medida,
                'marca_nombre' => $this->receta_nuevo_marca_nombre,
                'stock_disponible' => $stock,
                'cantidad' => (int) $this->receta_nuevo_cantidad,
                'indicaciones' => trim($this->receta_nuevo_indicaciones),
            ];
        }

        $this->limpiarMedicamentoSeleccionadoReceta();
    }

    public function removerMedicamentoReceta(int $index): void
    {
        if (isset($this->receta_medicamentos[$index])) {
            unset($this->receta_medicamentos[$index]);
            $this->receta_medicamentos = array_values($this->receta_medicamentos);
        }
    }

    public function guardarNuevaReceta(): void
    {
        if (! Auth::user()?->tienePermiso('despachar-farmacia') && ! Auth::user()?->tienePermiso('emitir-receta')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para prescribir recetas médicas.',
            ]);

            return;
        }

        if ($this->receta_nuevo_producto_id && (int) $this->receta_nuevo_cantidad > 0 && ! empty(trim($this->receta_nuevo_indicaciones))) {
            $this->agregarMedicamentoAReceta();
        }

        $this->validate([
            'receta_proforma_id' => ['required', 'exists:proformas,id'],
            'receta_medico_id' => ['required', 'exists:users,id'],
            'receta_observaciones' => ['nullable', 'string', 'max:1000'],
            'receta_medicamentos' => ['required', 'array', 'min:1'],
            'receta_medicamentos.*.producto_id' => ['required', 'exists:productos,id'],
            'receta_medicamentos.*.cantidad' => ['required', 'integer', 'min:1'],
            'receta_medicamentos.*.indicaciones' => ['required', 'string', 'min:2', 'max:255'],
        ], [
            'receta_proforma_id.required' => 'Debe seleccionar una proforma en curso.',
            'receta_medico_id.required' => 'Debe seleccionar un médico prescriptor.',
            'receta_medicamentos.required' => 'Debe agregar al menos un medicamento a la receta.',
            'receta_medicamentos.min' => 'Debe agregar al menos un medicamento a la receta.',
            'receta_medicamentos.*.producto_id.required' => 'Seleccione un fármaco válido en cada línea.',
            'receta_medicamentos.*.cantidad.min' => 'La cantidad mínima es 1.',
            'receta_medicamentos.*.indicaciones.required' => 'Indique la posología e indicaciones.',
        ]);

        $proforma = Proforma::findOrFail($this->receta_proforma_id);
        if ($proforma->estado !== 'En Curso') {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Proforma No Modificable',
                'text' => 'Solo se pueden emitir recetas para proformas en estado "En Curso".',
            ]);

            return;
        }

        DB::transaction(function () use ($proforma) {
            $receta = Receta::create([
                'proforma_id' => $proforma->id,
                'user_id' => $this->receta_medico_id,
                'activo' => true,
                'observaciones' => $this->receta_observaciones,
            ]);

            foreach ($this->receta_medicamentos as $med) {
                RecetaDetalle::create([
                    'receta_id' => $receta->id,
                    'producto_id' => $med['producto_id'],
                    'cantidad' => $med['cantidad'],
                    'indicaciones' => $med['indicaciones'],
                    'despachado' => false,
                ]);
            }

            $proforma->recalcularTotal();
        });

        $this->cerrarModalCrearReceta();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Receta Creada con Éxito!',
            'text' => "Se ha creado la receta médica para el paciente de la Proforma #{$proforma->id}. Ya se encuentra disponible en la bandeja de despacho.",
        ]);
    }

    // =========================================================================
    // ACCIONES: MODAL DESPACHO INSUMOS EXTRAS DIRECTOS
    // =========================================================================
    public function abrirModalDespachoExtraDirecto(): void
    {
        if (! Auth::user()?->tienePermiso('despachar-farmacia') && ! Auth::user()?->tienePermiso('proformas.consumos.agregar')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para despachar insumos de farmacia.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->modalDespachoExtraDirectoOpen = true;
        $this->extra_directo_proforma_id = null;
        $this->buscarProformaExtraDirecto = '';
        $this->extra_directo_seccion_id = null;
        $this->cola_despachos_extras_directos = [];
        $this->limpiarInsumoExtraDirecto();
    }

    public function cerrarModalDespachoExtraDirecto(): void
    {
        $this->modalDespachoExtraDirectoOpen = false;
        $this->extra_directo_proforma_id = null;
        $this->buscarProformaExtraDirecto = '';
        $this->extra_directo_seccion_id = null;
        $this->cola_despachos_extras_directos = [];
        $this->limpiarInsumoExtraDirecto();
        $this->resetValidation();
    }

    public function seleccionarProformaExtraDirecto(int $id): void
    {
        $proforma = Proforma::with(['paciente', 'sucursal'])->find($id);
        if (! $proforma || $proforma->estado !== 'En Curso') {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Proforma no disponible',
                'text' => 'La proforma no existe o no se encuentra en estado En Curso.',
            ]);

            return;
        }

        $this->extra_directo_proforma_id = $proforma->id;
        $this->buscarProformaExtraDirecto = '';
        $this->cola_despachos_extras_directos = [];
        $this->limpiarInsumoExtraDirecto();

        $sucursalId = $proforma->sucursal_id ?? Auth::user()->sucursal_id;
        $secciones = Seccion::where('sucursal_id', $sucursalId)
            ->where('activo', true)
            ->orderByDesc('es_almacen_principal')
            ->orderBy('nombre')
            ->get();

        if ($secciones->isEmpty()) {
            $central = Seccion::firstOrCreate(
                ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                ['nombre' => 'Farmacia Central', 'activo' => true]
            );
            $secciones = collect([$central]);
        }

        $this->extra_directo_seccion_id = $secciones->where('es_almacen_principal', true)->first()?->id ?? $secciones->first()?->id;
    }

    public function limpiarProformaSeleccionadaExtraDirecto(): void
    {
        $this->extra_directo_proforma_id = null;
        $this->buscarProformaExtraDirecto = '';
        $this->extra_directo_seccion_id = null;
        $this->cola_despachos_extras_directos = [];
        $this->limpiarInsumoExtraDirecto();
    }

    public function updatedExtraDirectoSeccionId($val): void
    {
        if ($this->extra_directo_producto_id) {
            $this->seleccionarInsumoExtraDirecto($this->extra_directo_producto_id);
        }
    }

    public function limpiarInsumoExtraDirecto(): void
    {
        $this->extra_directo_producto_id = null;
        $this->extra_directo_producto_nombre = '';
        $this->extra_directo_unidad_medida = '';
        $this->extra_directo_precio_unitario = '0.00';
        $this->extra_directo_stock_disponible = 0;
        $this->extra_directo_cantidad = 1;
        $this->extra_directo_observaciones = '';
        $this->buscarInsumoExtraDirecto = '';
    }

    public function seleccionarInsumoExtraDirecto(int $productoId): void
    {
        $producto = Producto::with('marca')->find($productoId);
        if (! $producto) {
            return;
        }

        $proforma = Proforma::find($this->extra_directo_proforma_id);
        $sucursalId = $proforma?->sucursal_id ?? Auth::user()->sucursal_id;
        $secId = $this->extra_directo_seccion_id;

        if (! $secId) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Seleccione una Sección',
                'text' => 'Debe seleccionar una sección o almacén de origen antes de seleccionar el producto.',
            ]);

            return;
        }

        // Sincronizar lotes sin sección si existieran
        $lotesSinSec = Lote::where('producto_id', $producto->id)
            ->where('sucursal_id', $sucursalId)
            ->where('cantidad_actual', '>', 0)
            ->whereDoesntHave('loteSecciones')
            ->get();
        foreach ($lotesSinSec as $lss) {
            LoteSeccion::firstOrCreate(
                ['lote_id' => $lss->id, 'seccion_id' => $secId],
                ['cantidad_actual' => $lss->cantidad_actual]
            );
        }

        $stock = (int) LoteSeccion::where('seccion_id', $secId)
            ->whereHas('lote', function ($q) use ($productoId, $sucursalId) {
                $q->where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
            })
            ->sum('cantidad_actual');

        if ($stock <= 0) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Sin existencias en esta área',
                'text' => "El producto '{$producto->nombre}' no cuenta con stock disponible en el área seleccionada.",
            ]);

            return;
        }

        $this->extra_directo_producto_id = $producto->id;
        $this->extra_directo_producto_nombre = $producto->nombre;
        $this->extra_directo_unidad_medida = $producto->unidad_medida ?? 'Unidad';
        $this->extra_directo_precio_unitario = (string) number_format((float) ($producto->ultimo_precio_venta ?? 0), 2, '.', '');
        $this->extra_directo_stock_disponible = $stock;
        $this->extra_directo_cantidad = 1;
        $this->extra_directo_observaciones = '';
        $this->buscarInsumoExtraDirecto = '';
    }

    public function agregarItemExtraDirecto(): void
    {
        $this->validate([
            'extra_directo_proforma_id' => ['required', 'exists:proformas,id'],
            'extra_directo_seccion_id' => ['required', 'exists:secciones,id'],
            'extra_directo_producto_id' => ['required', 'exists:productos,id'],
            'extra_directo_cantidad' => ['required', 'integer', 'min:1'],
            'extra_directo_precio_unitario' => ['required', 'numeric', 'min:0'],
            'extra_directo_observaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'extra_directo_proforma_id.required' => 'Seleccione una proforma.',
            'extra_directo_seccion_id.required' => 'Seleccione la sección de origen.',
            'extra_directo_producto_id.required' => 'Seleccione un insumo o medicamento.',
            'extra_directo_cantidad.min' => 'La cantidad mínima es 1.',
            'extra_directo_precio_unitario.min' => 'El precio no puede ser negativo.',
        ]);

        $prod = Producto::find($this->extra_directo_producto_id);
        $seccion = Seccion::find($this->extra_directo_seccion_id);
        $proforma = Proforma::find($this->extra_directo_proforma_id);
        $sucursalId = $proforma?->sucursal_id ?? Auth::user()->sucursal_id;

        $stockDisponible = (int) LoteSeccion::where('seccion_id', $this->extra_directo_seccion_id)
            ->whereHas('lote', function ($q) use ($prod, $sucursalId) {
                $q->where('producto_id', $prod->id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
            })
            ->sum('cantidad_actual');

        $yaEnCola = 0;
        foreach ($this->cola_despachos_extras_directos as $item) {
            if ($item['producto_id'] === $this->extra_directo_producto_id && $item['seccion_id'] === $this->extra_directo_seccion_id) {
                $yaEnCola += (int) $item['cantidad'];
            }
        }

        $totalRequerido = $yaEnCola + (int) $this->extra_directo_cantidad;
        if ($totalRequerido > $stockDisponible) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Stock insuficiente en el área',
                'text' => "No hay existencias suficientes de {$prod->nombre} en {$seccion->nombre}. Stock disponible: {$stockDisponible} unidades".($yaEnCola > 0 ? " (ya tiene {$yaEnCola} en lista)." : '.'),
            ]);

            return;
        }

        $cant = (int) $this->extra_directo_cantidad;
        $precio = (float) $this->extra_directo_precio_unitario;

        $this->cola_despachos_extras_directos[] = [
            'producto_id' => $prod->id,
            'producto_nombre' => $prod->nombre,
            'unidad_medida' => $prod->unidad_medida ?? 'Unidad',
            'seccion_id' => $this->extra_directo_seccion_id,
            'seccion_nombre' => $seccion?->nombre ?? 'Almacén',
            'cantidad' => $cant,
            'precio_unitario' => $precio,
            'subtotal' => round($cant * $precio, 2),
            'observaciones' => $this->extra_directo_observaciones,
        ];

        $this->limpiarInsumoExtraDirecto();
    }

    public function removerItemExtraDirecto(int $index): void
    {
        if (isset($this->cola_despachos_extras_directos[$index])) {
            unset($this->cola_despachos_extras_directos[$index]);
            $this->cola_despachos_extras_directos = array_values($this->cola_despachos_extras_directos);
        }
    }

    public function procesarDespachoExtraDirecto(): void
    {
        if (! Auth::user()?->tienePermiso('despachar-farmacia') && ! Auth::user()?->tienePermiso('proformas.consumos.agregar')) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para procesar despachos de insumos.',
            ]);

            return;
        }

        if ($this->extra_directo_producto_id) {
            $this->agregarItemExtraDirecto();
        }

        if (empty($this->cola_despachos_extras_directos)) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Lista vacía',
                'text' => 'Debe agregar al menos un insumo o medicamento a la lista de despacho.',
            ]);

            return;
        }

        $proforma = Proforma::findOrFail($this->extra_directo_proforma_id);
        if ($proforma->estado !== 'En Curso') {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Proforma No Modificable',
                'text' => 'Solo se pueden registrar consumos para proformas en estado "En Curso".',
            ]);

            return;
        }

        $sucursalId = $proforma->sucursal_id ?? Auth::user()->sucursal_id;

        try {
            DB::transaction(function () use ($proforma, $sucursalId) {
                foreach ($this->cola_despachos_extras_directos as $c) {
                    $lotes = Lote::where('producto_id', $c['producto_id'])
                        ->where('sucursal_id', $sucursalId)
                        ->whereHas('loteSecciones', function ($q) use ($c) {
                            $q->where('seccion_id', $c['seccion_id'])->where('cantidad_actual', '>', 0);
                        })
                        ->where(function ($q) {
                            $q->whereNull('fecha_vencimiento')
                                ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                        })
                        ->with(['loteSecciones' => function ($q) use ($c) {
                            $q->where('seccion_id', $c['seccion_id']);
                        }])
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $stockTotal = $lotes->sum(fn ($l) => $l->stockEnSeccion($c['seccion_id']));
                    if ($stockTotal < $c['cantidad']) {
                        throw new \Exception("Stock insuficiente para '{$c['producto_nombre']}' en {$c['seccion_nombre']}. Disponible: {$stockTotal} unidades.");
                    }

                    $cantPendiente = (int) $c['cantidad'];
                    foreach ($lotes as $lote) {
                        if ($cantPendiente <= 0) {
                            break;
                        }

                        $dispLote = $lote->stockEnSeccion($c['seccion_id']);
                        if ($dispLote <= 0) {
                            continue;
                        }

                        $descontar = min($cantPendiente, $dispLote);

                        $lote->descontarDeSeccion(
                            $c['seccion_id'],
                            $descontar,
                            'Consumo Extra',
                            $proforma->id,
                            $proforma->recetaActiva?->id,
                            Auth::id()
                        );

                        $cantPendiente -= $descontar;
                    }

                    $obsTexto = ! empty($c['observaciones'])
                        ? "[{$c['seccion_nombre']}] {$c['observaciones']}"
                        : "Despacho extra desde {$c['seccion_nombre']} (Farmacia)";

                    ConsumoExtra::create([
                        'proforma_id' => $proforma->id,
                        'producto_id' => $c['producto_id'],
                        'cantidad' => $c['cantidad'],
                        'precio_unitario' => $c['precio_unitario'],
                        'user_id' => Auth::id(),
                        'observaciones' => $obsTexto,
                    ]);
                }

                $proforma->recalcularTotal();
            });
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Error de Inventario',
                'text' => $e->getMessage(),
            ]);

            return;
        }

        $this->cerrarModalDespachoExtraDirecto();

        $this->dispatch('swal', [
            'type' => 'success',
            'title' => '¡Insumos Despachados con Éxito!',
            'text' => "Se despacharon los insumos extras para la Proforma #{$proforma->id} y se descargaron de sus respectivas secciones en inventario.",
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
        $seccionesSucursal = collect();
        $lotesDisponiblesPorItem = [];
        $productosParaExtras = collect();
        $lotesParaExtra = collect();

        if ($this->modalDespachoOpen && $this->recetaId) {
            $receta = Receta::with('proforma')->find($this->recetaId);
            $sucursalId = $receta?->proforma?->sucursal_id ?? Auth::user()->sucursal_id;

            $seccionesSucursal = Seccion::where('sucursal_id', $sucursalId)
                ->where('activo', true)
                ->orderByDesc('es_almacen_principal')
                ->orderBy('nombre')
                ->get();

            if ($seccionesSucursal->isEmpty()) {
                $central = Seccion::firstOrCreate(
                    ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                    ['nombre' => 'Farmacia Central', 'activo' => true]
                );
                $seccionesSucursal = collect([$central]);
            }

            foreach ($this->despachosItems as $detId => $item) {
                $secId = $item['seccion_id'] ?? $this->seccion_defecto_id;

                if ($secId) {
                    $lotes = Lote::where('producto_id', $item['producto_id'])
                        ->where('sucursal_id', $sucursalId)
                        ->whereHas('loteSecciones', function ($q) use ($secId) {
                            $q->where('seccion_id', $secId)->where('cantidad_actual', '>', 0);
                        })
                        ->where(function ($q) {
                            $q->whereNull('fecha_vencimiento')
                                ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                        })
                        ->with(['loteSecciones' => function ($q) use ($secId) {
                            $q->where('seccion_id', $secId);
                        }])
                        ->orderBy('fecha_vencimiento', 'asc')
                        ->get();

                    if ($lotes->isEmpty()) {
                        $lotesHeredados = Lote::where('producto_id', $item['producto_id'])
                            ->where('sucursal_id', $sucursalId)
                            ->where('cantidad_actual', '>', 0)
                            ->where(function ($q) {
                                $q->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            })
                            ->orderBy('fecha_vencimiento', 'asc')
                            ->get();

                        foreach ($lotesHeredados as $lh) {
                            LoteSeccion::firstOrCreate(
                                ['lote_id' => $lh->id, 'seccion_id' => $secId],
                                ['cantidad_actual' => $lh->cantidad_actual]
                            );
                        }

                        if ($lotesHeredados->isNotEmpty()) {
                            $lotes = $lotesHeredados;
                        }
                    }

                    $lotesDisponiblesPorItem[$detId] = $lotes;
                } else {
                    $lotesDisponiblesPorItem[$detId] = collect();
                }
            }

            // Catálogo disponible para extras filtrado por la sección de extra seleccionada
            $secExtraId = $this->extra_seccion_id ?? $this->seccion_defecto_id;

            $pQuery = Producto::whereHas('lotes', function ($q) use ($secExtraId, $sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
                if ($secExtraId) {
                    $q->where(function ($sq) use ($secExtraId) {
                        $sq->whereHas('loteSecciones', fn ($lsq) => $lsq->where('seccion_id', $secExtraId)->where('cantidad_actual', '>', 0))
                            ->orWhereDoesntHave('loteSecciones');
                    });
                }
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
                $productosParaExtras = $pQuery->orderBy('nombre')->take(8)->get();
            }

            $productoExtraSeleccionado = $this->extra_producto_id
                ? Producto::with('marca')->find($this->extra_producto_id)
                : null;

            if ($this->extra_producto_id && $secExtraId) {
                $lotesParaExtra = Lote::where('producto_id', $this->extra_producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    })
                    ->orderBy('fecha_vencimiento', 'asc')
                    ->get();

                foreach ($lotesParaExtra as $le) {
                    if (! $le->loteSecciones()->where('seccion_id', $secExtraId)->exists()) {
                        LoteSeccion::firstOrCreate(
                            ['lote_id' => $le->id, 'seccion_id' => $secExtraId],
                            ['cantidad_actual' => $le->cantidad_actual]
                        );
                    }
                }
            }
        }

        // Modal 1: Sugerencias de Proformas para Receta
        $proformasSugeridasReceta = collect();
        if ($this->modalCrearRecetaOpen && ! $this->receta_proforma_id) {
            $term = trim($this->buscarProformaReceta);
            if (! empty($term)) {
                $proformasSugeridasReceta = Proforma::where('estado', 'En Curso')
                    ->where(function ($q) use ($term) {
                        $q->where('id', 'like', "%{$term}%")
                            ->orWhereHas('paciente', function ($qp) use ($term) {
                                $qp->where('nombres', 'like', "%{$term}%")
                                    ->orWhere('apellido_paterno', 'like', "%{$term}%")
                                    ->orWhere('apellido_materno', 'like', "%{$term}%")
                                    ->orWhere('cedula', 'like', "%{$term}%");
                            });
                    })
                    ->with(['paciente', 'sucursal', 'medicos'])
                    ->latest('id')
                    ->take(8)
                    ->get();
            }
        }

        $proformaSeleccionadaReceta = $this->receta_proforma_id
            ? Proforma::with(['paciente', 'sucursal', 'medicos'])->find($this->receta_proforma_id)
            : null;

        $sugerenciasMedicamentosReceta = collect();
        if ($this->modalCrearRecetaOpen && $this->receta_proforma_id && ! $this->receta_nuevo_producto_id) {
            $term = trim($this->buscarMedicamentoReceta);
            if (! empty($term)) {
                $sucId = $proformaSeleccionadaReceta?->sucursal_id ?? Auth::user()?->sucursal_id;
                $sugerenciasMedicamentosReceta = Producto::with([
                    'marca',
                    'lotes' => function ($q) use ($sucId) {
                        $q->when($sucId, fn ($sq) => $sq->where('sucursal_id', $sucId))
                            ->where('cantidad_actual', '>', 0)
                            ->where(function ($vq) {
                                $vq->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            });
                    },
                ])
                    ->where(function ($q) use ($term) {
                        $q->where('nombre', 'like', "%{$term}%")
                            ->orWhere('descripcion', 'like', "%{$term}%");
                    })
                    ->orderBy('nombre')
                    ->take(10)
                    ->get();
            }
        }

        $medicos = User::where('activo', true)
            ->whereHas('rol', fn ($r) => $r->whereIn('nombre', ['Médico', 'Admin']))
            ->get();
        if ($medicos->isEmpty()) {
            $medicos = User::where('activo', true)->orderBy('name')->get();
        }

        // Modal 2: Sugerencias de Proformas para Despacho Extra Directo
        $proformasSugeridasExtraDirecto = collect();
        if ($this->modalDespachoExtraDirectoOpen && ! $this->extra_directo_proforma_id) {
            $term = trim($this->buscarProformaExtraDirecto);
            if (! empty($term)) {
                $proformasSugeridasExtraDirecto = Proforma::where('estado', 'En Curso')
                    ->where(function ($q) use ($term) {
                        $q->where('id', 'like', "%{$term}%")
                            ->orWhereHas('paciente', function ($qp) use ($term) {
                                $qp->where('nombres', 'like', "%{$term}%")
                                    ->orWhere('apellido_paterno', 'like', "%{$term}%")
                                    ->orWhere('apellido_materno', 'like', "%{$term}%")
                                    ->orWhere('cedula', 'like', "%{$term}%");
                            });
                    })
                    ->with(['paciente', 'sucursal'])
                    ->latest('id')
                    ->take(8)
                    ->get();
            }
        }

        $proformaSeleccionadaExtraDirecto = $this->extra_directo_proforma_id
            ? Proforma::with(['paciente', 'sucursal'])->find($this->extra_directo_proforma_id)
            : null;

        $seccionesSucursalExtraDirecto = collect();
        $productosParaExtraDirecto = collect();
        if ($this->modalDespachoExtraDirectoOpen && $this->extra_directo_proforma_id) {
            $sucDirectoId = $proformaSeleccionadaExtraDirecto?->sucursal_id ?? Auth::user()->sucursal_id;
            $seccionesSucursalExtraDirecto = Seccion::where('sucursal_id', $sucDirectoId)
                ->where('activo', true)
                ->orderByDesc('es_almacen_principal')
                ->orderBy('nombre')
                ->get();

            $secDirectoId = $this->extra_directo_seccion_id;
            if ($secDirectoId && ! $this->extra_directo_producto_id) {
                $pedQuery = Producto::whereHas('lotes', function ($q) use ($secDirectoId, $sucDirectoId) {
                    $q->where('sucursal_id', $sucDirectoId)
                        ->where('cantidad_actual', '>', 0)
                        ->where(function ($vq) {
                            $vq->whereNull('fecha_vencimiento')
                                ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                        })
                        ->where(function ($sq) use ($secDirectoId) {
                            $sq->whereHas('loteSecciones', fn ($lsq) => $lsq->where('seccion_id', $secDirectoId)->where('cantidad_actual', '>', 0))
                                ->orWhereDoesntHave('loteSecciones');
                        });
                })->with(['marca', 'lotes' => function ($q) use ($sucDirectoId) {
                    $q->where('sucursal_id', $sucDirectoId)->where('cantidad_actual', '>', 0);
                }]);

                if (! empty($this->buscarInsumoExtraDirecto)) {
                    $searchExtraDir = '%'.trim($this->buscarInsumoExtraDirecto).'%';
                    $pedQuery->where(function ($q) use ($searchExtraDir) {
                        $q->where('nombre', 'like', $searchExtraDir)
                            ->orWhere('descripcion', 'like', $searchExtraDir)
                            ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', $searchExtraDir));
                    });
                    $productosParaExtraDirecto = $pedQuery->orderBy('nombre')->take(10)->get();
                } else {
                    $productosParaExtraDirecto = $pedQuery->orderBy('nombre')->take(8)->get();
                }
            }
        }

        return view('livewire.farmacia.despachos-index', [
            'recetas' => $recetas,
            'totalPendientes' => $totalPendientes,
            'sucursales' => $sucursales,
            'seccionesSucursal' => $seccionesSucursal,
            'lotesDisponiblesPorItem' => $lotesDisponiblesPorItem,
            'productosParaExtras' => $productosParaExtras,
            'productoExtraSeleccionado' => $productoExtraSeleccionado ?? null,
            'lotesParaExtra' => $lotesParaExtra,
            'proformasSugeridasReceta' => $proformasSugeridasReceta,
            'proformaSeleccionadaReceta' => $proformaSeleccionadaReceta,
            'sugerenciasMedicamentosReceta' => $sugerenciasMedicamentosReceta,
            'medicos' => $medicos,
            'proformasSugeridasExtraDirecto' => $proformasSugeridasExtraDirecto,
            'proformaSeleccionadaExtraDirecto' => $proformaSeleccionadaExtraDirecto,
            'seccionesSucursalExtraDirecto' => $seccionesSucursalExtraDirecto,
            'productosParaExtraDirecto' => $productosParaExtraDirecto,
        ]);
    }
}
