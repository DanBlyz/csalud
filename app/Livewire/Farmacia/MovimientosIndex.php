<?php

namespace App\Livewire\Farmacia;

use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
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
class MovimientosIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda
    public string $search = '';

    public int $perPage = 10;

    public ?int $filtroSucursal = null;

    public ?int $filtroSeccion = null;

    public ?int $filtroProducto = null;

    public string $filtroTipo = ''; // 'Entrada Compra', 'Salida Receta', 'Consumo Extra', 'Transferencia Interna', 'Devolución', 'Merma por Vencimiento', 'Ajuste de Inventario'

    public ?string $fechaDesde = null;

    public ?string $fechaHasta = null;

    // ==========================================
    // MODAL DE DEVOLUCIÓN A PROFORMA
    // ==========================================
    public bool $modalDevolucionOpen = false;

    public ?int $movimientoIdParaDevolver = null;

    public int $devolucion_cantidad = 1;

    public ?int $devolucion_seccion_id = null;

    public string $devolucion_motivo = '';

    // Datos informativos para el modal
    public string $dev_producto_nombre = '';

    public string $dev_producto_unidad = '';

    public string $dev_lote_codigo = '';

    public string $dev_lote_vence = '';

    public int $dev_cantidad_original = 0;

    public int $dev_cantidad_disponible = 0;

    public ?int $dev_proforma_id = null;

    public string $dev_paciente_nombre = '';

    public string $dev_seccion_origen_nombre = '';

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
        $this->filtroSeccion = null;
        $this->resetPage();
    }

    public function updatingFiltroSeccion(): void
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
        $this->filtroSeccion = null;
        $this->filtroTipo = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->resetPage();
    }

    // ==========================================
    // GESTIÓN DE DEVOLUCIÓN DE DESPACHOS
    // ==========================================

    public function abrirModalDevolucion(int $movimientoId): void
    {
        if (! Auth::user()?->tieneAlgunPermiso(['farmacia.despachos.revertir', 'despachar-farmacia'])) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No cuenta con el permiso requerido para procesar devoluciones de farmacia.',
            ]);

            return;
        }

        $mov = MovimientoInventario::with(['producto.marca', 'lote', 'proforma.paciente', 'seccionOrigen'])
            ->findOrFail($movimientoId);

        if (! $mov->proforma_id || ! $mov->proforma) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Sin Proforma Vinculada',
                'text' => 'Este movimiento no está asociado a una proforma médica.',
            ]);

            return;
        }

        if ($mov->proforma->estado !== 'En Curso') {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Proforma No Editable',
                'text' => "La Proforma #{$mov->proforma_id} se encuentra en estado '{$mov->proforma->estado}'. Solo se admiten devoluciones para proformas 'En Curso'.",
            ]);

            return;
        }

        if (! in_array($mov->tipo_movimiento, ['Salida Receta', 'Consumo Extra'], true)) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Tipo No Retornable',
                'text' => 'Solo se pueden procesar devoluciones sobre salidas de recetas médicas o consumos extras en piso.',
            ]);

            return;
        }

        $disponible = $mov->cantidadDisponibleParaDevolucion();
        if ($disponible <= 0) {
            $this->dispatch('swal', [
                'type' => 'warning',
                'title' => 'Totalmente Devuelto',
                'text' => 'La totalidad de las unidades de este movimiento ya fue devuelta al inventario.',
            ]);

            return;
        }

        $this->movimientoIdParaDevolver = $mov->id;
        $this->dev_producto_nombre = $mov->producto->nombre ?? 'Medicamento no especificado';
        $this->dev_producto_unidad = ($mov->producto?->unidad_medida ?? 'Unidad').' • '.($mov->producto?->marca?->nombre ?? 'Genérico');
        $this->dev_lote_codigo = $mov->lote?->codigo_lote ?? 'S/L';
        $this->dev_lote_vence = $mov->lote?->fecha_vencimiento?->format('d/m/Y') ?? 'S/F';
        $this->dev_cantidad_original = (int) $mov->cantidad;
        $this->dev_cantidad_disponible = $disponible;
        $this->dev_proforma_id = $mov->proforma_id;
        $this->dev_paciente_nombre = $mov->proforma->paciente?->nombre_completo ?? 'Paciente';
        $this->dev_seccion_origen_nombre = $mov->seccionOrigen?->nombre ?? 'Farmacia Central';

        $this->devolucion_cantidad = 1;
        $this->devolucion_seccion_id = $mov->seccion_origen_id
            ?? Seccion::where('sucursal_id', $mov->sucursal_id)->where('es_almacen_principal', true)->value('id')
            ?? Seccion::where('sucursal_id', $mov->sucursal_id)->value('id');
        $this->devolucion_motivo = '';

        $this->modalDevolucionOpen = true;
    }

    public function setDevolverTodo(): void
    {
        $this->devolucion_cantidad = $this->dev_cantidad_disponible;
    }

    public function cerrarModalDevolucion(): void
    {
        $this->modalDevolucionOpen = false;
        $this->movimientoIdParaDevolver = null;
        $this->resetValidation();
    }

    public function procesarDevolucion(): void
    {
        if (! Auth::user()?->tieneAlgunPermiso(['farmacia.despachos.revertir', 'despachar-farmacia'])) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene autorización para procesar devoluciones.',
            ]);

            return;
        }

        $this->validate([
            'movimientoIdParaDevolver' => ['required', 'exists:movimientos_inventario,id'],
            'devolucion_cantidad' => ['required', 'integer', 'min:1', 'max:'.$this->dev_cantidad_disponible],
            'devolucion_seccion_id' => ['required', 'exists:secciones,id'],
            'devolucion_motivo' => ['nullable', 'string', 'max:500'],
        ], [
            'devolucion_cantidad.required' => 'Indique la cantidad a devolver.',
            'devolucion_cantidad.min' => 'La cantidad mínima a devolver es 1 unidad.',
            'devolucion_cantidad.max' => "La cantidad máxima permitida para este movimiento es {$this->dev_cantidad_disponible}.",
            'devolucion_seccion_id.required' => 'Seleccione el área o sección receptora del reingreso.',
        ]);

        try {
            DB::transaction(function () {
                $mov = MovimientoInventario::with(['producto', 'lote', 'proforma'])->findOrFail($this->movimientoIdParaDevolver);
                $proforma = $mov->proforma;

                if (! $proforma || $proforma->estado !== 'En Curso') {
                    throw new \DomainException('La proforma ya no se encuentra en curso.');
                }

                $disponible = $mov->cantidadDisponibleParaDevolucion();
                if ($this->devolucion_cantidad > $disponible) {
                    throw new \DomainException("La cantidad a devolver ({$this->devolucion_cantidad}) excede el saldo disponible ({$disponible}).");
                }

                $cantDevolver = (int) $this->devolucion_cantidad;
                $lote = Lote::findOrFail($mov->lote_id);

                // 1. Reingreso físico de stock al Lote consolidado
                $lote->increment('cantidad_actual', $cantDevolver);

                // 2. Reingreso físico a la sección receptora en lote_secciones
                $loteSec = LoteSeccion::firstOrCreate(
                    ['lote_id' => $lote->id, 'seccion_id' => $this->devolucion_seccion_id],
                    ['cantidad_actual' => 0]
                );
                $loteSec->increment('cantidad_actual', $cantDevolver);

                // 3. Registrar el Asiento inmutable de Devolución en Kardex
                $seccionReceptora = Seccion::find($this->devolucion_seccion_id);
                $motivoTexto = ! empty($this->devolucion_motivo) ? trim($this->devolucion_motivo) : 'Dosis no administrada / alta anticipada';
                $obsAsiento = "Devolución a {$seccionReceptora?->nombre} (Proforma #{$proforma->id}): {$motivoTexto}";

                MovimientoInventario::create([
                    'sucursal_id' => $mov->sucursal_id,
                    'producto_id' => $mov->producto_id,
                    'lote_id' => $mov->lote_id,
                    'cantidad' => $cantDevolver,
                    'tipo_movimiento' => 'Devolución',
                    'receta_id' => $mov->receta_id,
                    'proforma_id' => $mov->proforma_id,
                    'movimiento_origen_id' => $mov->id,
                    'seccion_origen_id' => $mov->seccion_origen_id,
                    'seccion_destino_id' => $this->devolucion_seccion_id,
                    'user_id' => Auth::id() ?? 1,
                    'observaciones' => $obsAsiento,
                ]);

                // 4. Si fue Salida Receta, actualizar el detalle de la prescripción médica
                if ($mov->tipo_movimiento === 'Salida Receta' && $mov->receta_id) {
                    $detalle = RecetaDetalle::where('receta_id', $mov->receta_id)
                        ->where('producto_id', $mov->producto_id)
                        ->first();

                    if ($detalle && $detalle->despachado) {
                        $despachadasNetas = (int) MovimientoInventario::where('receta_id', $mov->receta_id)
                            ->where('producto_id', $mov->producto_id)
                            ->where('tipo_movimiento', 'Salida Receta')
                            ->sum('cantidad')
                            - (int) MovimientoInventario::where('receta_id', $mov->receta_id)
                                ->where('producto_id', $mov->producto_id)
                                ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])
                                ->sum('cantidad');

                        if ($despachadasNetas < $detalle->cantidad) {
                            $detalle->update(['despachado' => false]);
                        }
                    }
                } elseif ($mov->tipo_movimiento === 'Consumo Extra') {
                    // Si fue Consumo Extra, reducir la cantidad imputada a la proforma
                    $consumo = ConsumoExtra::where('proforma_id', $proforma->id)
                        ->where('producto_id', $mov->producto_id)
                        ->first();

                    if ($consumo) {
                        if ($consumo->cantidad > $cantDevolver) {
                            $consumo->decrement('cantidad', $cantDevolver);
                        } else {
                            $consumo->delete();
                        }
                    }
                }

                // 5. Recalcular de forma automática el costo consolidado de la proforma
                $proforma->recalcularTotal();
            });

            $this->dispatch('swal', [
                'type' => 'success',
                'title' => 'Devolución Procesada Exitosamente',
                'text' => "Se reingresaron {$this->devolucion_cantidad} unidades al inventario y se actualizó la Proforma #{$this->dev_proforma_id}.",
            ]);

            $this->cerrarModalDevolucion();
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'type' => 'error',
                'title' => 'Error al procesar devolución',
                'text' => $e->getMessage(),
            ]);
        }
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
                'seccionOrigen',
                'seccionDestino',
                'devoluciones',
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
                    ->orWhere('tipo_movimiento', 'like', $search)
                    ->orWhere('observaciones', 'like', $search);
            });
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroSeccion) {
            $query->where(function (Builder $q) {
                $q->where('seccion_origen_id', $this->filtroSeccion)
                    ->orWhere('seccion_destino_id', $this->filtroSeccion);
            });
        }

        if ($this->filtroProducto) {
            $query->where('producto_id', $this->filtroProducto);
        }

        if (! empty($this->filtroTipo)) {
            if ($this->filtroTipo === 'Devolución') {
                $query->whereIn('tipo_movimiento', ['Devolución', 'Devolucion']);
            } else {
                $query->where('tipo_movimiento', $this->filtroTipo);
            }
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
        $totalDevoluciones = MovimientoInventario::whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])->count();
        $totalMermas = MovimientoInventario::whereIn('tipo_movimiento', ['Merma por Vencimiento', 'Ajuste de Inventario', 'Baja por Deterioro'])->count();

        // Colecciones para filtros
        $sucursales = Sucursal::orderBy('nombre')->get();
        $productos = Producto::orderBy('nombre')->get(['id', 'nombre']);

        $sucursalActivaId = $this->filtroSucursal ?? Auth::user()->sucursal_id;
        $secciones = Seccion::where('activo', true)
            ->when($sucursalActivaId, fn ($q) => $q->where('sucursal_id', $sucursalActivaId))
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre')
            ->get();

        return view('livewire.farmacia.movimientos-index', [
            'movimientos' => $movimientos,
            'totalMovimientos' => $totalMovimientos,
            'totalEntradas' => $totalEntradas,
            'totalSalidas' => $totalSalidas,
            'totalDevoluciones' => $totalDevoluciones,
            'totalMermas' => $totalMermas,
            'sucursales' => $sucursales,
            'secciones' => $secciones,
            'productos' => $productos,
        ]);
    }
}
