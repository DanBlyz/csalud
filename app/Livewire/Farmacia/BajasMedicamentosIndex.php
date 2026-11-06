<?php

namespace App\Livewire\Farmacia;

use App\Models\BajaMedicamento;
use App\Models\BajaMedicamentoDetalle;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Seccion;
use App\Models\Sucursal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class BajasMedicamentosIndex extends Component
{
    use WithPagination;

    // Filtros y Búsqueda
    public string $search = '';

    public string $filtroEstado = ''; // '' = Todos, 'Pendiente', 'Aprobado', 'Rechazado'

    public string $filtroMotivo = ''; // '' = Todos, 'Vencimiento', 'Deterioro / Rotura', etc.

    public ?int $filtroSucursal = null;

    public ?string $filtroFechaDesde = null;

    public ?string $filtroFechaHasta = null;

    public int $perPage = 10;

    // Modal Crear / Editar Solicitud de Baja
    public bool $modalBajaOpen = false;

    public ?int $baja_id_en_edicion = null;

    public ?int $sucursal_id = null;

    public string $fecha_registro = '';

    public string $area_solicitante = 'Farmacia Central';

    public string $responsable = '';

    public string $motivo = 'Vencimiento';

    public string $justificacion = '';

    // Cola de Ítems / Medicamentos para la Baja
    public array $cola_detalles = [];

    // Inputs para agregar medicamento a la cola
    public string $buscarProductoLote = '';

    public ?int $item_lote_id = null;

    public ?int $item_producto_id = null;

    public string $item_producto_nombre = '';

    public string $item_lote_codigo = '';

    public string $item_forma_farmaceutica = '';

    public string $item_concentracion = '';

    public ?string $item_fecha_vencimiento = null;

    public ?int $item_seccion_id = null;

    public int $item_cantidad = 1;

    public int $item_stock_disponible = 0;

    public string $item_observaciones = '';

    // Modal Detalle / Visualización
    public bool $modalDetalleOpen = false;

    public ?int $bajaSeleccionadaId = null;

    // Modal Aprobación
    public bool $modalAprobarOpen = false;

    public ?int $baja_id_para_aprobar = null;

    public string $observaciones_aprobacion = '';

    // Modal Rechazo
    public bool $modalRechazarOpen = false;

    public ?int $baja_id_para_rechazar = null;

    public string $motivo_rechazo = '';

    public function mount(): void
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.bajas.ver') && ! $user->tienePermiso(9)) {
            abort(403, 'No cuenta con autorización para acceder al módulo de bajas de medicamentos.');
        }

        $this->sucursal_id = $user?->sucursal_id ?? Sucursal::first()?->id;
        $this->responsable = $user?->nombre_completo ?? $user?->name ?? 'Responsable Farmacia';
        $this->fecha_registro = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function setFiltroEstado(string $estado): void
    {
        $this->filtroEstado = ($this->filtroEstado === $estado) ? '' : $estado;
        $this->resetPage();
    }

    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroMotivo(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroSucursal(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaDesde(): void
    {
        $this->resetPage();
    }

    public function updatingFiltroFechaHasta(): void
    {
        $this->resetPage();
    }

    // ==========================================
    // GESTIÓN DEL MODAL CREAR / EDITAR BAJA
    // ==========================================

    public function abrirModalCrear(): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.crear')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para registrar solicitudes de baja de medicamentos.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->reset([
            'baja_id_en_edicion',
            'justificacion',
            'cola_detalles',
            'buscarProductoLote',
            'item_lote_id',
            'item_producto_id',
            'item_producto_nombre',
            'item_lote_codigo',
            'item_forma_farmaceutica',
            'item_concentracion',
            'item_fecha_vencimiento',
            'item_seccion_id',
            'item_cantidad',
            'item_stock_disponible',
            'item_observaciones',
        ]);

        $this->sucursal_id = Auth::user()?->sucursal_id ?? Sucursal::first()?->id;
        $this->responsable = Auth::user()?->nombre_completo ?? Auth::user()?->name ?? 'Responsable Farmacia';
        $this->fecha_registro = now()->toDateString();
        $this->area_solicitante = 'Farmacia Central';
        $this->motivo = 'Vencimiento';

        $this->modalBajaOpen = true;
    }

    public function abrirModalEditar(int $bajaId): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.editar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para editar solicitudes de baja de medicamentos.',
            ]);

            return;
        }

        $baja = BajaMedicamento::with(['detalles.producto', 'detalles.lote', 'detalles.seccion'])->findOrFail($bajaId);

        if (! $baja->esPendiente()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no permitida',
                'text' => 'Solo se pueden editar solicitudes de baja que se encuentren en estado Pendiente.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->baja_id_en_edicion = $baja->id;
        $this->sucursal_id = $baja->sucursal_id;
        $this->fecha_registro = $baja->fecha_registro->format('Y-m-d');
        $this->area_solicitante = $baja->area_solicitante;
        $this->responsable = $baja->responsable;
        $this->motivo = $baja->motivo;
        $this->justificacion = $baja->justificacion;

        $this->cola_detalles = [];
        foreach ($baja->detalles as $det) {
            $this->cola_detalles[] = [
                'producto_id' => $det->producto_id,
                'producto_nombre' => $det->producto?->nombre ?? 'Medicamento',
                'lote_id' => $det->lote_id,
                'lote_codigo' => $det->lote?->codigo_lote ?? 'S/L',
                'seccion_id' => $det->seccion_id,
                'seccion_nombre' => $det->seccion?->nombre ?? 'Almacén',
                'cantidad' => $det->cantidad,
                'forma_farmaceutica' => $det->forma_farmaceutica ?? ($det->producto?->unidad_medida ?? ''),
                'concentracion' => $det->concentracion ?? ($det->producto?->descripcion ?? ''),
                'fecha_vencimiento' => $det->fecha_vencimiento ? $det->fecha_vencimiento->format('Y-m-d') : null,
                'observaciones' => $det->observaciones ?? '',
            ];
        }

        $this->reset([
            'buscarProductoLote',
            'item_lote_id',
            'item_producto_id',
            'item_producto_nombre',
            'item_lote_codigo',
            'item_forma_farmaceutica',
            'item_concentracion',
            'item_fecha_vencimiento',
            'item_seccion_id',
            'item_cantidad',
            'item_stock_disponible',
            'item_observaciones',
        ]);

        $this->modalBajaOpen = true;
    }

    public function cerrarModalBaja(): void
    {
        $this->modalBajaOpen = false;
        $this->resetValidation();
    }

    // ==========================================
    // SELECCIÓN Y ADICIÓN DE ÍTEMS EN COLA
    // ==========================================

    public function seleccionarLoteParaItem(int $loteId): void
    {
        $lote = Lote::with(['producto', 'loteSecciones.seccion'])->find($loteId);
        if (! $lote) {
            return;
        }

        $this->item_lote_id = $lote->id;
        $this->item_producto_id = $lote->producto_id;
        $this->item_producto_nombre = $lote->producto->nombre ?? 'Medicamento';
        $this->item_lote_codigo = $lote->codigo_lote ?? 'S/L';
        $this->item_forma_farmaceutica = $lote->producto->unidad_medida ?? 'Unidad';
        $this->item_concentracion = $lote->producto->descripcion ?? '';
        $this->item_fecha_vencimiento = $lote->fecha_vencimiento ? $lote->fecha_vencimiento->format('Y-m-d') : null;

        // Determinar sección de origen preferente con existencias
        $seccionConStock = $lote->loteSecciones->where('cantidad_actual', '>', 0)->first();
        if ($seccionConStock) {
            $this->item_seccion_id = $seccionConStock->seccion_id;
            $this->item_stock_disponible = (int) $seccionConStock->cantidad_actual;
        } else {
            $primeraSec = $lote->loteSecciones->first();
            $this->item_seccion_id = $primeraSec?->seccion_id ?? Seccion::where('sucursal_id', $this->sucursal_id)->first()?->id;
            $this->item_stock_disponible = (int) ($primeraSec?->cantidad_actual ?? $lote->cantidad_actual);
        }

        $this->item_cantidad = 1;
        $this->buscarProductoLote = '';
    }

    public function updatedItemSeccionId($seccionId): void
    {
        if (! $this->item_lote_id || ! $seccionId) {
            $this->item_stock_disponible = 0;

            return;
        }

        $loteSeccion = LoteSeccion::where('lote_id', $this->item_lote_id)
            ->where('seccion_id', $seccionId)
            ->first();

        $this->item_stock_disponible = $loteSeccion ? (int) $loteSeccion->cantidad_actual : 0;
    }

    public function agregarItemACola(): void
    {
        if (! $this->item_lote_id || ! $this->item_producto_id) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Medicamento no seleccionado',
                'text' => 'Debe buscar y seleccionar un lote de medicamento del catálogo.',
            ]);

            return;
        }

        if (! $this->item_seccion_id) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Sección de origen obligatoria',
                'text' => 'Debe seleccionar el área o sección de inventario de donde se dará de baja el fármaco.',
            ]);

            return;
        }

        if ($this->item_cantidad <= 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cantidad inválida',
                'text' => 'La cantidad a dar de baja debe ser mayor a 0.',
            ]);

            return;
        }

        // Validación preventiva de existencias actuales
        $loteSeccion = LoteSeccion::where('lote_id', $this->item_lote_id)
            ->where('seccion_id', $this->item_seccion_id)
            ->first();

        $stockDisponible = $loteSeccion ? (int) $loteSeccion->cantidad_actual : 0;
        if ($this->item_cantidad > $stockDisponible) {
            $seccion = Seccion::find($this->item_seccion_id);
            $nombreSec = $seccion?->nombre ?? 'Sección seleccionada';
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Stock insuficiente en la sección',
                'text' => "La cantidad ingresada ({$this->item_cantidad}) supera las existencias físicas disponibles ({$stockDisponible}) en {$nombreSec}.",
            ]);

            return;
        }

        $seccion = Seccion::find($this->item_seccion_id);

        // Si ya existe el mismo lote y misma sección en la cola, acumular cantidad
        $yaExiste = false;
        foreach ($this->cola_detalles as $idx => $item) {
            if ($item['lote_id'] === $this->item_lote_id && $item['seccion_id'] === $this->item_seccion_id) {
                $nuevaCantidad = $item['cantidad'] + $this->item_cantidad;
                if ($nuevaCantidad > $stockDisponible) {
                    $this->dispatch('swal', [
                        'icon' => 'warning',
                        'title' => 'Stock acumulado insuficiente',
                        'text' => "La cantidad acumulada ({$nuevaCantidad}) excede el stock disponible ({$stockDisponible}).",
                    ]);

                    return;
                }
                $this->cola_detalles[$idx]['cantidad'] = $nuevaCantidad;
                $this->cola_detalles[$idx]['forma_farmaceutica'] = $this->item_forma_farmaceutica;
                $this->cola_detalles[$idx]['concentracion'] = $this->item_concentracion;
                $this->cola_detalles[$idx]['observaciones'] = $this->item_observaciones ?: $item['observaciones'];
                $yaExiste = true;
                break;
            }
        }

        if (! $yaExiste) {
            $this->cola_detalles[] = [
                'producto_id' => $this->item_producto_id,
                'producto_nombre' => $this->item_producto_nombre,
                'lote_id' => $this->item_lote_id,
                'lote_codigo' => $this->item_lote_codigo,
                'seccion_id' => $this->item_seccion_id,
                'seccion_nombre' => $seccion?->nombre ?? 'Almacén',
                'cantidad' => $this->item_cantidad,
                'forma_farmaceutica' => $this->item_forma_farmaceutica,
                'concentracion' => $this->item_concentracion,
                'fecha_vencimiento' => $this->item_fecha_vencimiento,
                'observaciones' => $this->item_observaciones,
            ];
        }

        // Limpiar inputs del ítem
        $this->reset([
            'buscarProductoLote',
            'item_lote_id',
            'item_producto_id',
            'item_producto_nombre',
            'item_lote_codigo',
            'item_forma_farmaceutica',
            'item_concentracion',
            'item_fecha_vencimiento',
            'item_seccion_id',
            'item_cantidad',
            'item_stock_disponible',
            'item_observaciones',
        ]);
    }

    public function quitarItemDeCola(int $index): void
    {
        unset($this->cola_detalles[$index]);
        $this->cola_detalles = array_values($this->cola_detalles);
    }

    // ==========================================
    // PERSISTENCIA DE LA SOLICITUD DE BAJA
    // ==========================================

    public function guardarBaja(): void
    {
        $reglas = [
            'sucursal_id' => 'required|exists:sucursales,id',
            'fecha_registro' => 'required|date',
            'area_solicitante' => 'required|string|max:150',
            'responsable' => 'required|string|max:150',
            'motivo' => 'required|string|max:100',
            'justificacion' => 'required|string|min:5|max:1000',
            'cola_detalles' => 'required|array|min:1',
        ];

        $this->validate($reglas, [
            'cola_detalles.min' => 'Debe agregar al menos un medicamento o insumo al formulario de baja.',
            'justificacion.required' => 'La justificación de la baja es obligatoria.',
        ]);

        if ($this->baja_id_en_edicion) {
            if (! Auth::user()?->tienePermiso('farmacia.bajas.editar')) {
                return;
            }
            $baja = BajaMedicamento::findOrFail($this->baja_id_en_edicion);
            if (! $baja->esPendiente()) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Acción no permitida',
                    'text' => 'Solo se pueden editar solicitudes en estado Pendiente.',
                ]);

                return;
            }

            DB::transaction(function () use ($baja): void {
                $baja->update([
                    'sucursal_id' => $this->sucursal_id,
                    'fecha_registro' => $this->fecha_registro,
                    'area_solicitante' => trim($this->area_solicitante),
                    'responsable' => trim($this->responsable),
                    'motivo' => $this->motivo,
                    'justificacion' => trim($this->justificacion),
                ]);

                // Reemplazar detalles
                $baja->detalles()->delete();

                foreach ($this->cola_detalles as $det) {
                    $baja->detalles()->create([
                        'producto_id' => $det['producto_id'],
                        'lote_id' => $det['lote_id'],
                        'seccion_id' => $det['seccion_id'],
                        'cantidad' => $det['cantidad'],
                        'forma_farmaceutica' => $det['forma_farmaceutica'] ?: null,
                        'concentracion' => $det['concentracion'] ?: null,
                        'fecha_vencimiento' => $det['fecha_vencimiento'] ?: null,
                        'observaciones' => $det['observaciones'] ? trim($det['observaciones']) : null,
                    ]);
                }
            });

            $this->cerrarModalBaja();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Solicitud Actualizada',
                'text' => "El formulario de baja {$baja->codigo} fue actualizado exitosamente.",
            ]);
        } else {
            if (! Auth::user()?->tienePermiso('farmacia.bajas.crear')) {
                return;
            }

            $codigo = BajaMedicamento::generarSiguienteCodigo();

            $baja = DB::transaction(function () use ($codigo) {
                $nuevaBaja = BajaMedicamento::create([
                    'sucursal_id' => $this->sucursal_id,
                    'codigo' => $codigo,
                    'fecha_registro' => $this->fecha_registro,
                    'area_solicitante' => trim($this->area_solicitante),
                    'responsable' => trim($this->responsable),
                    'motivo' => $this->motivo,
                    'justificacion' => trim($this->justificacion),
                    'estado' => 'Pendiente',
                ]);

                foreach ($this->cola_detalles as $det) {
                    $nuevaBaja->detalles()->create([
                        'producto_id' => $det['producto_id'],
                        'lote_id' => $det['lote_id'],
                        'seccion_id' => $det['seccion_id'],
                        'cantidad' => $det['cantidad'],
                        'forma_farmaceutica' => $det['forma_farmaceutica'] ?: null,
                        'concentracion' => $det['concentracion'] ?: null,
                        'fecha_vencimiento' => $det['fecha_vencimiento'] ?: null,
                        'observaciones' => $det['observaciones'] ? trim($det['observaciones']) : null,
                    ]);
                }

                return $nuevaBaja;
            });

            $this->cerrarModalBaja();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Baja Registrada',
                'text' => "Se ha generado el formulario {$baja->codigo} en estado Pendiente para revisión y aprobación.",
            ]);
        }
    }

    // ==========================================
    // DETALLE / VISUALIZACIÓN
    // ==========================================

    public function abrirModalDetalle(int $bajaId): void
    {
        $this->bajaSeleccionadaId = $bajaId;
        $this->modalDetalleOpen = true;
    }

    public function cerrarModalDetalle(): void
    {
        $this->modalDetalleOpen = false;
        $this->bajaSeleccionadaId = null;
    }

    // ==========================================
    // APROBACIÓN DE LA BAJA (DESCUENTO REAL)
    // ==========================================

    public function abrirModalAprobar(int $bajaId): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.aprobar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para aprobar bajas de medicamentos.',
            ]);

            return;
        }

        $baja = BajaMedicamento::findOrFail($bajaId);
        if (! $baja->esPendiente()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no disponible',
                'text' => 'Solo se pueden aprobar actas en estado Pendiente.',
            ]);

            return;
        }

        $this->baja_id_para_aprobar = $baja->id;
        $this->observaciones_aprobacion = '';
        $this->modalAprobarOpen = true;
    }

    public function cerrarModalAprobar(): void
    {
        $this->modalAprobarOpen = false;
        $this->baja_id_para_aprobar = null;
    }

    public function procesarAprobacion(): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.aprobar')) {
            return;
        }

        if (! $this->baja_id_para_aprobar) {
            return;
        }

        $baja = BajaMedicamento::findOrFail($this->baja_id_para_aprobar);

        try {
            $baja->aprobar(Auth::user(), $this->observaciones_aprobacion);

            $this->cerrarModalAprobar();
            $this->dispatch('swal', [
                'icon' => 'success',
                'title' => 'Baja Aprobada y Ejecutada',
                'text' => "El acta {$baja->codigo} fue aprobada. Las existencias fueron descontadas de los lotes y secciones correspondientes, y los movimientos de Kardex quedaron asentados.",
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al Aprobar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // ==========================================
    // RECHAZO DE LA BAJA
    // ==========================================

    public function abrirModalRechazar(int $bajaId): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.aprobar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para resolver solicitudes de baja.',
            ]);

            return;
        }

        $baja = BajaMedicamento::findOrFail($bajaId);
        if (! $baja->esPendiente()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no disponible',
                'text' => 'Solo se pueden rechazar solicitudes en estado Pendiente.',
            ]);

            return;
        }

        $this->baja_id_para_rechazar = $baja->id;
        $this->motivo_rechazo = '';
        $this->modalRechazarOpen = true;
    }

    public function cerrarModalRechazar(): void
    {
        $this->modalRechazarOpen = false;
        $this->baja_id_para_rechazar = null;
    }

    public function procesarRechazo(): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.aprobar')) {
            return;
        }

        if (! $this->baja_id_para_rechazar) {
            return;
        }

        $baja = BajaMedicamento::findOrFail($this->baja_id_para_rechazar);

        try {
            $baja->rechazar(Auth::user(), $this->motivo_rechazo);

            $this->cerrarModalRechazar();
            $this->dispatch('swal', [
                'icon' => 'info',
                'title' => 'Solicitud Rechazada',
                'text' => "El formulario de baja {$baja->codigo} fue marcado como Rechazado sin alterar el inventario.",
            ]);
        } catch (\Throwable $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error al Rechazar',
                'text' => $e->getMessage(),
            ]);
        }
    }

    // ==========================================
    // ELIMINACIÓN DE SOLICITUDES PENDIENTES
    // ==========================================

    #[On('eliminarBaja')]
    public function eliminarBaja(int $bajaId): void
    {
        if (! Auth::user()?->tienePermiso('farmacia.bajas.eliminar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso denegado',
                'text' => 'No cuenta con autorización para eliminar solicitudes de baja.',
            ]);

            return;
        }

        $baja = BajaMedicamento::findOrFail($bajaId);
        if (! $baja->esPendiente()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Acción no permitida',
                'text' => 'Solo se pueden eliminar formularios de baja en estado Pendiente.',
            ]);

            return;
        }

        DB::transaction(function () use ($baja): void {
            $baja->detalles()->delete();
            $baja->delete();
        });

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Formulario Eliminado',
            'text' => "La solicitud de baja {$baja->codigo} fue eliminada del sistema.",
        ]);
    }

    public function render(): View
    {
        // 1. Consulta Principal Paginada
        $query = BajaMedicamento::with([
            'sucursal',
            'aprobadoPor',
            'usuarioCreador',
            'detalles.producto.marca',
            'detalles.lote',
            'detalles.seccion',
        ]);

        if (! empty($this->search)) {
            $term = '%'.trim($this->search).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('codigo', 'like', $term)
                    ->orWhere('responsable', 'like', $term)
                    ->orWhere('area_solicitante', 'like', $term)
                    ->orWhere('justificacion', 'like', $term)
                    ->orWhereHas('detalles.producto', fn (Builder $pq) => $pq->where('nombre', 'like', $term))
                    ->orWhereHas('detalles.lote', fn (Builder $lq) => $lq->where('codigo_lote', 'like', $term));
            });
        }

        if (! empty($this->filtroEstado)) {
            $query->where('estado', $this->filtroEstado);
        }

        if (! empty($this->filtroMotivo)) {
            $query->where('motivo', $this->filtroMotivo);
        }

        if ($this->filtroSucursal) {
            $query->where('sucursal_id', $this->filtroSucursal);
        }

        if ($this->filtroFechaDesde) {
            $query->whereDate('fecha_registro', '>=', $this->filtroFechaDesde);
        }

        if ($this->filtroFechaHasta) {
            $query->whereDate('fecha_registro', '<=', $this->filtroFechaHasta);
        }

        $bajas = $query->latest('id')->paginate($this->perPage);

        // 2. Métricas
        $totalBajas = BajaMedicamento::count();
        $totalPendientes = BajaMedicamento::where('estado', 'Pendiente')->count();
        $totalAprobadas = BajaMedicamento::where('estado', 'Aprobado')->count();
        $totalUnidadesDadasDeBaja = (int) BajaMedicamentoDetalle::whereHas('bajaMedicamento', fn ($q) => $q->where('estado', 'Aprobado'))->sum('cantidad');

        // 3. Catálogos para el modal
        $sucursales = Sucursal::orderBy('nombre')->get();
        $seccionesSucursal = Seccion::where('sucursal_id', $this->sucursal_id)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // 4. Búsqueda predictiva de lotes con stock para agregar ítems
        $sugerenciasLotes = collect();
        if ($this->modalBajaOpen && ! empty(trim($this->buscarProductoLote))) {
            $sTerm = '%'.trim($this->buscarProductoLote).'%';
            $sugerenciasLotes = Lote::with(['producto.marca', 'loteSecciones.seccion'])
                ->where('sucursal_id', $this->sucursal_id)
                ->where(function (Builder $q) use ($sTerm): void {
                    $q->where('codigo_lote', 'like', $sTerm)
                        ->orWhereHas('producto', fn (Builder $pq) => $pq->where('nombre', 'like', $sTerm));
                })
                ->orderBy('fecha_vencimiento', 'asc')
                ->take(10)
                ->get();
        }

        // Secciones disponibles para el lote actualmente seleccionado en modal
        $seccionesDelLoteSeleccionado = collect();
        if ($this->item_lote_id) {
            $loteActivo = Lote::with('loteSecciones.seccion')->find($this->item_lote_id);
            if ($loteActivo) {
                $seccionesDelLoteSeleccionado = $loteActivo->loteSecciones;
            }
        }

        $bajaSeleccionada = $this->bajaSeleccionadaId
            ? BajaMedicamento::with(['sucursal', 'aprobadoPor', 'usuarioCreador', 'detalles.producto.marca', 'detalles.lote', 'detalles.seccion'])->find($this->bajaSeleccionadaId)
            : null;

        return view('livewire.farmacia.bajas-medicamentos-index', [
            'bajas' => $bajas,
            'totalBajas' => $totalBajas,
            'totalPendientes' => $totalPendientes,
            'totalAprobadas' => $totalAprobadas,
            'totalUnidadesDadasDeBaja' => $totalUnidadesDadasDeBaja,
            'sucursales' => $sucursales,
            'seccionesSucursal' => $seccionesSucursal,
            'sugerenciasLotes' => $sugerenciasLotes,
            'seccionesDelLoteSeleccionado' => $seccionesDelLoteSeleccionado,
            'bajaSeleccionada' => $bajaSeleccionada,
        ]);
    }
}
