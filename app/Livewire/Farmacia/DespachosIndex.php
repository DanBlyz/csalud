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
            // Calcular unidades ya despachadas por MovimientoInventario
            $despachadasPrevias = (int) MovimientoInventario::where('receta_id', $receta->id)
                ->where('producto_id', $det->producto_id)
                ->where('tipo_movimiento', 'Salida Receta')
                ->sum('cantidad');

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

        return view('livewire.farmacia.despachos-index', [
            'recetas' => $recetas,
            'totalPendientes' => $totalPendientes,
            'sucursales' => $sucursales,
            'seccionesSucursal' => $seccionesSucursal,
            'lotesDisponiblesPorItem' => $lotesDisponiblesPorItem,
            'productosParaExtras' => $productosParaExtras,
            'productoExtraSeleccionado' => $productoExtraSeleccionado ?? null,
            'lotesParaExtra' => $lotesParaExtra,
        ]);
    }
}
