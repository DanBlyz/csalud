<?php

namespace App\Livewire\Proformas;

use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaCalendario;
use App\Models\ProformaPagoMedico;
use App\Models\ProformaServicio;
use App\Models\ProformaSolicitud;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Seccion;
use App\Models\Servicio;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ProformaDetalle extends Component
{
    use WithFileUploads;

    public Proforma $proforma;

    public string $tab = 'servicios'; // servicios, solicitudes, calendario, recetas, consumos, despachos

    // Modal Editar Cabecera Proforma
    public bool $modalEditarCabeceraOpen = false;

    public string $edit_pieza = '';

    public ?string $edit_fecha_salida = null;

    public string $edit_motivo = '';

    public string $edit_diagnostico = '';

    public string $edit_estado = 'En Curso';

    public array $edit_medicos = [];

    // Modal Agregar Servicios en Lote
    public bool $modalServicioOpen = false;

    public ?int $nuevo_servicio_id = null;

    public string $nuevo_servicio_costo = '0.00';

    public string $nuevo_servicio_observaciones = '';

    public string $buscarServicio = '';

    public array $cola_servicios = [];

    public bool $modalSolicitudOpen = false;

    public ?int $nuevo_tipo_solicitud_id = null;

    public string $nuevo_solicitud_observaciones = '';

    public $nuevo_solicitud_archivo = null;

    public array $cola_solicitudes = [];

    // Modal Actualizar / Subir Archivo de Solicitud
    public bool $modalArchivoSolicitudOpen = false;

    public ?int $solicitud_id_archivo = null;

    public ?string $solicitud_estudio_nombre = null;

    public $solicitud_nuevo_archivo = null;

    // Modal Agregar Eventos Calendario en Lote
    public bool $modalCalendarioOpen = false;

    public string $nuevo_evento_fecha = '';

    public string $nuevo_evento_hora = '';

    public string $nuevo_evento_descripcion = '';

    public string $nuevo_evento_estado = 'Programado';

    public array $cola_eventos = [];

    // Modal Prescribir Nueva Receta
    public bool $modalRecetaOpen = false;

    public ?int $receta_medico_id = null;

    public string $receta_observaciones = '';

    public array $receta_medicamentos = [];

    // Modal Registrar Consumos Extras en Lote
    public bool $modalConsumoOpen = false;

    public ?int $consumo_seccion_id = null;

    public ?int $consumo_producto_id = null;

    public int $consumo_cantidad = 1;

    public string $consumo_precio_unitario = '0.00';

    public string $consumo_observaciones = '';

    public int $consumo_stock_disponible = 0;

    public string $buscarInsumoConsumo = '';

    public array $cola_consumos = [];

    // Modal Despacho de Farmacia Directo en Proforma
    public bool $modalDespachoProformaOpen = false;

    public ?int $despacho_seccion_defecto_id = null;

    public array $despachosItems = [];

    public array $despachosExtrasItems = [];

    public ?int $despacho_extra_seccion_id = null;

    public ?int $despacho_extra_producto_id = null;

    public ?int $despacho_extra_lote_id = null;

    public int $despacho_extra_cantidad = 1;

    public ?string $despacho_extra_observaciones = null;

    public string $buscarDespachoExtraProducto = '';

    // Modal Honorarios / Pagos Médicos
    public bool $modalPagoMedicoOpen = false;

    public ?int $pago_medico_id = null;

    public string $pago_medico_monto = '';

    public string $pago_medico_observaciones = '';

    public string $pago_medico_fecha = '';

    public ?int $editando_pago_medico_id = null;

    /**
     * Mapeo de permisos requeridos para visualizar cada pestaña clínica.
     */
    public static array $tabPermissions = [
        'servicios' => 'proformas.servicios.ver',
        'solicitudes' => 'proformas.solicitudes.ver',
        'calendario' => 'proformas.cronograma.ver',
        'recetas' => 'proformas.recetas.ver',
        'consumos' => 'proformas.consumos.ver',
        'despachos' => 'despachar-farmacia',
        'pagos_medicos' => 'proformas.honorarios.ver',
    ];

    public function autorizarAccion(string $permiso, string $mensaje = 'No cuenta con autorización para realizar esta acción.'): bool
    {
        $user = Auth::user();
        if (! $user || ! $user->tienePermiso($permiso)) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Restringido',
                'text' => $mensaje,
            ]);

            return false;
        }

        return true;
    }

    public function mount(Proforma $proforma): void
    {
        $this->proforma = $proforma->load([
            'paciente.institucion',
            'medicos.especialidad',
            'sucursal',
            'servicios.servicio.categoria',
            'solicitudes.tipoSolicitud',
            'calendarios',
            'recetaActiva.detalles.producto',
            'recetas.detalles.producto',
            'consumosExtras.producto',
            'consumosExtras.user',
            'pagos',
            'pagosMedicos.medico.especialidad',
            'pagosMedicos.user',
            'movimientosInventario.producto.marca',
            'movimientosInventario.lote',
            'movimientosInventario.usuario',
            'movimientosInventario.receta',
        ]);

        $this->proforma->recalcularTotal();

        // Si el usuario no tiene permiso para la pestaña por defecto, cambiar a la primera que tenga permitida
        $user = Auth::user();
        if ($user && isset(self::$tabPermissions[$this->tab]) && ! $user->tienePermiso(self::$tabPermissions[$this->tab])) {
            foreach (self::$tabPermissions as $tabKey => $perm) {
                if ($user->tienePermiso($perm)) {
                    $this->tab = $tabKey;
                    break;
                }
            }
        }
    }

    public function cambiarTab(string $tab): void
    {
        if (isset(self::$tabPermissions[$tab])) {
            $user = Auth::user();
            if ($user && ! $user->tienePermiso(self::$tabPermissions[$tab])) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Acceso Restringido',
                    'text' => 'No dispone de permisos para acceder a esta pestaña clínica.',
                ]);

                return;
            }
        }

        $this->tab = $tab;
    }

    public function asegurarProformaModificable(): bool
    {
        if ($this->proforma->estado === 'Pagada') {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Proforma Cerrada',
                'text' => 'Esta proforma ya se encuentra Pagada y cerrada. No se pueden agregar, editar o eliminar ítems clínicos o insumos. Solo se permite gestionar honorarios médicos.',
            ]);

            return false;
        }

        return true;
    }

    // =========================================================================
    // 1. EDICIÓN DE CABECERA CLÍNICA
    // =========================================================================
    public function abrirModalEditarCabecera(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para editar los datos de cabecera de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->edit_pieza = $this->proforma->pieza ?? '';
        $this->edit_fecha_salida = $this->proforma->fecha_salida?->format('Y-m-d\TH:i');
        $this->edit_motivo = $this->proforma->motivo_consulta ?? '';
        $this->edit_diagnostico = $this->proforma->diagnostico ?? '';
        $this->edit_estado = $this->proforma->estado;
        $this->edit_medicos = $this->proforma->medicos->pluck('id')->toArray();

        $this->modalEditarCabeceraOpen = true;
    }

    public function cerrarModalEditarCabecera(): void
    {
        $this->modalEditarCabeceraOpen = false;
    }

    public function guardarCabecera(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para editar los datos de cabecera de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'edit_pieza' => ['nullable', 'string', 'max:100'],
            'edit_fecha_salida' => ['nullable', 'date'],
            'edit_motivo' => ['nullable', 'string', 'max:1000'],
            'edit_diagnostico' => ['nullable', 'string', 'max:1000'],
            'edit_estado' => ['required', 'in:En Curso,Pagada,Anulada'],
            'edit_medicos' => ['nullable', 'array'],
            'edit_medicos.*' => ['exists:users,id'],
        ]);

        $this->proforma->update([
            'pieza' => $this->edit_pieza,
            'fecha_salida' => $this->edit_fecha_salida,
            'motivo_consulta' => $this->edit_motivo,
            'diagnostico' => $this->edit_diagnostico,
            'estado' => $this->edit_estado,
        ]);

        $this->proforma->medicos()->sync($this->edit_medicos);
        $this->proforma->load('medicos.especialidad');

        $this->cerrarModalEditarCabecera();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Expediente Actualizado',
            'text' => 'Los datos generales de la proforma han sido actualizados.',
        ]);
    }

    // =========================================================================
    // 2. SERVICIOS Y PROCEDIMIENTOS CLÍNICOS (EN LOTE)
    // =========================================================================
    public function abrirModalServicio(): void
    {
        if (! $this->autorizarAccion('proformas.servicios.agregar', 'No cuenta con autorización para agregar servicios a la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->reset([
            'nuevo_servicio_id',
            'nuevo_servicio_costo',
            'nuevo_servicio_observaciones',
            'cola_servicios',
            'buscarServicio',
        ]);
        $this->nuevo_servicio_costo = '0.00';
        $this->modalServicioOpen = true;
    }

    public function cerrarModalServicio(): void
    {
        $this->modalServicioOpen = false;
        $this->buscarServicio = '';
    }

    public function seleccionarServicio(int $servicioId): void
    {
        $this->nuevo_servicio_id = $servicioId;
        $servicio = Servicio::find($servicioId);
        if ($servicio) {
            $this->nuevo_servicio_costo = number_format($servicio->precio_tentativo, 2, '.', '');
        }
        $this->buscarServicio = '';
    }

    public function limpiarServicioSeleccionado(): void
    {
        $this->nuevo_servicio_id = null;
        $this->nuevo_servicio_costo = '0.00';
        $this->buscarServicio = '';
    }

    public function updatedNuevoServicioId($value): void
    {
        if ($value) {
            $servicio = Servicio::find($value);
            if ($servicio) {
                $this->nuevo_servicio_costo = number_format($servicio->precio_tentativo, 2, '.', '');
            }
        }
    }

    public function agregarServicioACola(): void
    {
        $this->validate([
            'nuevo_servicio_id' => ['required', 'exists:servicios,id'],
            'nuevo_servicio_costo' => ['required', 'numeric', 'min:0'],
            'nuevo_servicio_observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'nuevo_servicio_id.required' => 'Debe seleccionar un servicio del catálogo.',
            'nuevo_servicio_costo.required' => 'El costo final es obligatorio.',
        ]);

        $servicio = Servicio::with('categoria')->find($this->nuevo_servicio_id);
        $this->cola_servicios[] = [
            'servicio_id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'categoria' => $servicio->categoria->nombre ?? 'General',
            'costo_final' => (float) $this->nuevo_servicio_costo,
            'observaciones' => $this->nuevo_servicio_observaciones,
        ];

        $this->reset(['nuevo_servicio_id', 'nuevo_servicio_observaciones', 'buscarServicio']);
        $this->nuevo_servicio_costo = '0.00';
    }

    public function eliminarServicioDeCola(int $index): void
    {
        unset($this->cola_servicios[$index]);
        $this->cola_servicios = array_values($this->cola_servicios);
    }

    public function agregarServicio(): void
    {
        if (! $this->autorizarAccion('proformas.servicios.agregar', 'No cuenta con autorización para agregar servicios a la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        if ($this->nuevo_servicio_id) {
            $this->agregarServicioACola();
        }

        if (empty($this->cola_servicios)) {
            $this->validate([
                'nuevo_servicio_id' => ['required', 'exists:servicios,id'],
            ], [
                'nuevo_servicio_id.required' => 'Debe seleccionar o encolar al menos un servicio.',
            ]);

            return;
        }

        foreach ($this->cola_servicios as $s) {
            ProformaServicio::create([
                'proforma_id' => $this->proforma->id,
                'servicio_id' => $s['servicio_id'],
                'costo_final' => $s['costo_final'],
                'observaciones' => $s['observaciones'],
            ]);
        }

        $cant = count($this->cola_servicios);
        $this->cola_servicios = [];
        $this->proforma->recalcularTotal();
        $this->cerrarModalServicio();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Servicios Agregados',
            'text' => "Se han añadido {$cant} procedimiento(s) a la proforma.",
        ]);
    }

    #[On('eliminarServicioProforma')]
    public function eliminarServicioProforma(int $id): void
    {
        if (! $this->autorizarAccion('proformas.servicios.eliminar', 'No cuenta con autorización para eliminar servicios de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $item = ProformaServicio::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $item->delete();

        $this->proforma->recalcularTotal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Servicio Removido',
            'text' => 'El servicio ha sido removido y el costo total recalculado.',
        ]);
    }

    // =========================================================================
    // 3. SOLICITUDES Y EXÁMENES CLÍNICOS (EN LOTE)
    // =========================================================================
    public function abrirModalSolicitud(): void
    {
        if (! $this->autorizarAccion('proformas.solicitudes.agregar', 'No cuenta con autorización para solicitar exámenes clínicos.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->reset([
            'nuevo_tipo_solicitud_id',
            'nuevo_solicitud_observaciones',
            'nuevo_solicitud_archivo',
            'cola_solicitudes',
        ]);
        $this->modalSolicitudOpen = true;
    }

    public function cerrarModalSolicitud(): void
    {
        $this->modalSolicitudOpen = false;
    }

    public function agregarSolicitudACola(): void
    {
        $this->validate([
            'nuevo_tipo_solicitud_id' => ['required', 'exists:tipos_solicitudes,id'],
            'nuevo_solicitud_observaciones' => ['nullable', 'string', 'max:1000'],
            'nuevo_solicitud_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'nuevo_tipo_solicitud_id.required' => 'Debe seleccionar el tipo de examen o estudio.',
            'nuevo_solicitud_archivo.mimes' => 'Solo se permiten documentos PDF o imágenes (JPG, PNG, WEBP).',
            'nuevo_solicitud_archivo.max' => 'El archivo no puede exceder 10MB.',
        ]);

        $tipo = TipoSolicitud::find($this->nuevo_tipo_solicitud_id);
        $this->cola_solicitudes[] = [
            'tipo_solicitud_id' => $tipo->id,
            'tipo_nombre' => $tipo->nombre,
            'observaciones' => $this->nuevo_solicitud_observaciones,
            'archivo' => $this->nuevo_solicitud_archivo,
        ];

        $this->reset(['nuevo_tipo_solicitud_id', 'nuevo_solicitud_observaciones', 'nuevo_solicitud_archivo']);
    }

    public function eliminarSolicitudDeCola(int $index): void
    {
        unset($this->cola_solicitudes[$index]);
        $this->cola_solicitudes = array_values($this->cola_solicitudes);
    }

    public function agregarSolicitud(): void
    {
        if (! $this->autorizarAccion('proformas.solicitudes.agregar', 'No cuenta con autorización para solicitar exámenes clínicos.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        if ($this->nuevo_tipo_solicitud_id) {
            $this->agregarSolicitudACola();
        }

        if (empty($this->cola_solicitudes)) {
            $this->validate([
                'nuevo_tipo_solicitud_id' => ['required', 'exists:tipos_solicitudes,id'],
            ], [
                'nuevo_tipo_solicitud_id.required' => 'Debe seleccionar o encolar al menos un examen.',
            ]);

            return;
        }

        foreach ($this->cola_solicitudes as $sol) {
            $rutaArchivo = null;
            if (isset($sol['archivo']) && $sol['archivo']) {
                $rutaArchivo = $sol['archivo']->store('solicitudes_clinicas', 'public');
            }

            ProformaSolicitud::create([
                'proforma_id' => $this->proforma->id,
                'tipo_solicitud_id' => $sol['tipo_solicitud_id'],
                'observaciones' => $sol['observaciones'],
                'archivo' => $rutaArchivo,
            ]);
        }

        $cant = count($this->cola_solicitudes);
        $this->cola_solicitudes = [];
        $this->cerrarModalSolicitud();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Solicitudes Registradas',
            'text' => "Se han adjuntado {$cant} solicitud(es) de examen a la proforma.",
        ]);
    }

    #[On('eliminarSolicitudProforma')]
    public function eliminarSolicitudProforma(int $id): void
    {
        if (! $this->autorizarAccion('proformas.solicitudes.eliminar', 'No cuenta con autorización para eliminar solicitudes de examen.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $solicitud = ProformaSolicitud::where('proforma_id', $this->proforma->id)->findOrFail($id);

        if ($solicitud->archivo) {
            Storage::disk('public')->delete($solicitud->archivo);
        }

        $solicitud->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Solicitud Eliminada',
            'text' => 'La orden de examen ha sido eliminada del expediente.',
        ]);
    }

    public function abrirModalSubirArchivo(int $solicitudId): void
    {
        if (! $this->autorizarAccion('proformas.solicitudes.agregar', 'No cuenta con autorización para adjuntar archivos a la solicitud.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $solicitud = ProformaSolicitud::where('proforma_id', $this->proforma->id)->with('tipoSolicitud')->findOrFail($solicitudId);
        $this->solicitud_id_archivo = $solicitud->id;
        $this->solicitud_estudio_nombre = $solicitud->tipoSolicitud?->nombre ?? 'Estudio Clínico';
        $this->solicitud_nuevo_archivo = null;
        $this->modalArchivoSolicitudOpen = true;
    }

    public function cerrarModalSubirArchivo(): void
    {
        $this->modalArchivoSolicitudOpen = false;
        $this->reset(['solicitud_id_archivo', 'solicitud_estudio_nombre', 'solicitud_nuevo_archivo']);
        $this->resetValidation();
    }

    public function guardarArchivoSolicitud(): void
    {
        if (! $this->autorizarAccion('proformas.solicitudes.agregar', 'No cuenta con autorización para adjuntar archivos a la solicitud.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'solicitud_nuevo_archivo' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'solicitud_nuevo_archivo.required' => 'Debe seleccionar un archivo para adjuntar.',
            'solicitud_nuevo_archivo.mimes' => 'Solo se permiten documentos PDF o imágenes (JPG, PNG, WEBP).',
            'solicitud_nuevo_archivo.max' => 'El archivo no puede exceder 10MB.',
        ]);

        $solicitud = ProformaSolicitud::where('proforma_id', $this->proforma->id)->findOrFail($this->solicitud_id_archivo);

        if ($solicitud->archivo) {
            Storage::disk('public')->delete($solicitud->archivo);
        }

        $ruta = $this->solicitud_nuevo_archivo->store('solicitudes_clinicas', 'public');
        $solicitud->update(['archivo' => $ruta]);

        $this->cerrarModalSubirArchivo();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Archivo Adjunto Actualizado!',
            'text' => 'El resultado o documento ha sido vinculado exitosamente a la orden médica.',
        ]);
    }

    // =========================================================================
    // 4. CALENDARIO Y AGENDA DE LA PROFORMA (EN LOTE)
    // =========================================================================
    public function abrirModalCalendario(): void
    {
        if (! $this->autorizarAccion('proformas.cronograma.agregar', 'No cuenta con autorización para programar citas en el cronograma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->nuevo_evento_fecha = now()->format('Y-m-d');
        $this->nuevo_evento_hora = now()->addHour()->format('H:00');
        $this->nuevo_evento_descripcion = '';
        $this->nuevo_evento_estado = 'Programado';
        $this->cola_eventos = [];
        $this->modalCalendarioOpen = true;
    }

    public function cerrarModalCalendario(): void
    {
        $this->modalCalendarioOpen = false;
    }

    public function agregarEventoACola(): void
    {
        $this->validate([
            'nuevo_evento_fecha' => ['required', 'date'],
            'nuevo_evento_hora' => ['required'],
            'nuevo_evento_descripcion' => ['required', 'string', 'min:3', 'max:255'],
            'nuevo_evento_estado' => ['required', 'in:Programado,Realizado,Cancelado'],
        ], [
            'nuevo_evento_descripcion.required' => 'La descripción del evento es obligatoria.',
        ]);

        $this->cola_eventos[] = [
            'fecha' => $this->nuevo_evento_fecha,
            'hora' => $this->nuevo_evento_hora,
            'descripcion' => $this->nuevo_evento_descripcion,
            'estado' => $this->nuevo_evento_estado,
        ];

        $this->nuevo_evento_descripcion = '';
    }

    public function eliminarEventoDeCola(int $index): void
    {
        unset($this->cola_eventos[$index]);
        $this->cola_eventos = array_values($this->cola_eventos);
    }

    public function agregarEventoCalendario(): void
    {
        if (! $this->autorizarAccion('proformas.cronograma.agregar', 'No cuenta con autorización para programar citas en el cronograma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        if (! empty(trim($this->nuevo_evento_descripcion))) {
            $this->agregarEventoACola();
        }

        if (empty($this->cola_eventos)) {
            $this->validate([
                'nuevo_evento_descripcion' => ['required', 'string', 'min:3', 'max:255'],
            ], [
                'nuevo_evento_descripcion.required' => 'Debe agregar al menos una actividad al cronograma.',
            ]);

            return;
        }

        foreach ($this->cola_eventos as $ev) {
            ProformaCalendario::create([
                'proforma_id' => $this->proforma->id,
                'fecha' => $ev['fecha'],
                'hora' => $ev['hora'],
                'descripcion' => $ev['descripcion'],
                'estado' => $ev['estado'],
            ]);
        }

        $cant = count($this->cola_eventos);
        $this->cola_eventos = [];
        $this->cerrarModalCalendario();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Cronograma Actualizado',
            'text' => "Se han programado {$cant} actividad(es) en el calendario.",
        ]);
    }

    public function toggleEstadoEvento(int $id): void
    {
        if (! $this->autorizarAccion('proformas.cronograma.gestionar', 'No cuenta con autorización para modificar actividades del cronograma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $evento = ProformaCalendario::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $nuevoEstado = $evento->estado === 'Realizado' ? 'Programado' : 'Realizado';
        $evento->update(['estado' => $nuevoEstado]);

        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => 'Estado de Actividad',
            'text' => "El evento ha sido marcado como {$nuevoEstado}.",
        ]);
    }

    #[On('eliminarEventoCalendario')]
    public function eliminarEventoCalendario(int $id): void
    {
        if (! $this->autorizarAccion('proformas.cronograma.gestionar', 'No cuenta con autorización para eliminar actividades del cronograma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $evento = ProformaCalendario::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $evento->delete();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Evento Eliminado',
            'text' => 'La actividad ha sido removida del cronograma.',
        ]);
    }

    // =========================================================================
    // 5. RECETAS MÉDICAS (REGLA: SOLO UNA RECETA ACTIVA)
    // =========================================================================
    public function abrirModalReceta(): void
    {
        if (! $this->autorizarAccion('emitir-receta', 'No cuenta con autorización para prescribir recetas médicas.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->receta_medico_id = Auth::id();
        $this->receta_observaciones = '';
        $this->receta_medicamentos = [
            ['producto_id' => '', 'producto_nombre' => '', 'unidad_medida' => '', 'busqueda' => '', 'cantidad' => 1, 'indicaciones' => ''],
        ];
        $this->modalRecetaOpen = true;
    }

    public function cerrarModalReceta(): void
    {
        $this->modalRecetaOpen = false;
    }

    public function agregarFilaMedicamento(): void
    {
        $this->receta_medicamentos[] = [
            'producto_id' => '',
            'producto_nombre' => '',
            'unidad_medida' => '',
            'busqueda' => '',
            'cantidad' => 1,
            'indicaciones' => '',
        ];
    }

    public function eliminarFilaMedicamento(int $index): void
    {
        unset($this->receta_medicamentos[$index]);
        $this->receta_medicamentos = array_values($this->receta_medicamentos);

        if (empty($this->receta_medicamentos)) {
            $this->agregarFilaMedicamento();
        }
    }

    public function seleccionarMedicamentoReceta(int $index, int $productoId): void
    {
        $producto = Producto::find($productoId);
        if ($producto && isset($this->receta_medicamentos[$index])) {
            $this->receta_medicamentos[$index]['producto_id'] = $producto->id;
            $this->receta_medicamentos[$index]['producto_nombre'] = $producto->nombre;
            $this->receta_medicamentos[$index]['unidad_medida'] = $producto->unidad_medida ?? 'Unidad';
            $this->receta_medicamentos[$index]['busqueda'] = '';
        }
    }

    public function limpiarMedicamentoReceta(int $index): void
    {
        if (isset($this->receta_medicamentos[$index])) {
            $this->receta_medicamentos[$index]['producto_id'] = '';
            $this->receta_medicamentos[$index]['producto_nombre'] = '';
            $this->receta_medicamentos[$index]['unidad_medida'] = '';
            $this->receta_medicamentos[$index]['busqueda'] = '';
        }
    }

    public function prescribirReceta(): void
    {
        if (! $this->autorizarAccion('emitir-receta', 'No cuenta con autorización para emitir recetas médicas.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'receta_medico_id' => ['required', 'exists:users,id'],
            'receta_observaciones' => ['nullable', 'string', 'max:1000'],
            'receta_medicamentos' => ['required', 'array', 'min:1'],
            'receta_medicamentos.*.producto_id' => ['required', 'exists:productos,id'],
            'receta_medicamentos.*.cantidad' => ['required', 'integer', 'min:1'],
            'receta_medicamentos.*.indicaciones' => ['required', 'string', 'min:2', 'max:255'],
        ], [
            'receta_medicamentos.*.producto_id.required' => 'Seleccione un fármaco en cada línea.',
            'receta_medicamentos.*.cantidad.min' => 'La cantidad mínima es 1.',
            'receta_medicamentos.*.indicaciones.required' => 'Indique la posología (dosis, horas y días).',
        ]);

        // Crear la nueva Receta con activo = true.
        // El Observer / booted() de Receta automáticamente desactiva las anteriores de esta proforma.
        $receta = Receta::create([
            'proforma_id' => $this->proforma->id,
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

        $this->proforma->recalcularTotal();
        $this->cerrarModalReceta();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Receta Prescrita con Éxito!',
            'text' => 'La nueva receta está ACTIVA para farmacia. Cualquier receta anterior fue archivada como histórica.',
        ]);
    }

    // =========================================================================
    // 6. CONSUMOS EXTRAS E INSUMOS DIRECTOS (EN LOTE)
    // =========================================================================
    public function abrirModalConsumo(): void
    {
        if (! $this->autorizarAccion('proformas.consumos.agregar', 'No cuenta con autorización para registrar consumos e insumos.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->reset([
            'consumo_producto_id',
            'consumo_observaciones',
            'cola_consumos',
            'buscarInsumoConsumo',
        ]);

        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;
        $seccionPrincipal = Seccion::where('sucursal_id', $sucursalId)->where('activo', true)->where('es_almacen_principal', true)->first()
            ?? Seccion::where('sucursal_id', $sucursalId)->where('activo', true)->first();

        if (! $seccionPrincipal) {
            $seccionPrincipal = Seccion::firstOrCreate(
                ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                ['nombre' => 'Farmacia Central', 'activo' => true]
            );
        }

        $this->consumo_seccion_id = $seccionPrincipal->id;
        $this->consumo_cantidad = 1;
        $this->consumo_precio_unitario = '0.00';
        $this->consumo_stock_disponible = 0;
        $this->modalConsumoOpen = true;
    }

    public function cerrarModalConsumo(): void
    {
        $this->modalConsumoOpen = false;
        $this->buscarInsumoConsumo = '';
        $this->consumo_stock_disponible = 0;
    }

    public function updatedConsumoSeccionId($val): void
    {
        if ($this->consumo_producto_id) {
            $this->updatedConsumoProductoId($this->consumo_producto_id);
        }
    }

    public function seleccionarInsumoConsumo(int $productoId): void
    {
        $this->consumo_producto_id = $productoId;
        $producto = Producto::find($productoId);
        if ($producto) {
            $this->consumo_precio_unitario = number_format($producto->ultimo_precio_venta, 2, '.', '');
            $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

            if ($this->consumo_seccion_id) {
                // Sincronizar lotes sin sección si existieran
                $lotesSinSec = Lote::where('producto_id', $productoId)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->whereDoesntHave('loteSecciones')
                    ->get();
                foreach ($lotesSinSec as $lss) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $lss->id, 'seccion_id' => $this->consumo_seccion_id],
                        ['cantidad_actual' => $lss->cantidad_actual]
                    );
                }

                $this->consumo_stock_disponible = (int) LoteSeccion::where('seccion_id', $this->consumo_seccion_id)
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
            } else {
                $this->consumo_stock_disponible = 0;
            }

            if ($this->consumo_stock_disponible <= 0) {
                $this->dispatch('swal', [
                    'icon' => 'warning',
                    'title' => 'Sin existencias en esta área',
                    'text' => "El insumo o medicamento {$producto->nombre} no cuenta con stock disponible en el área seleccionada.",
                ]);
            }
        }
        $this->buscarInsumoConsumo = '';
    }

    public function limpiarInsumoConsumo(): void
    {
        $this->consumo_producto_id = null;
        $this->consumo_precio_unitario = '0.00';
        $this->consumo_cantidad = 1;
        $this->consumo_stock_disponible = 0;
        $this->buscarInsumoConsumo = '';
    }

    public function updatedConsumoProductoId($value): void
    {
        if ($value && $this->consumo_seccion_id) {
            $producto = Producto::find($value);
            if ($producto) {
                $this->consumo_precio_unitario = number_format($producto->ultimo_precio_venta, 2, '.', '');
                $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

                $lotesSinSec = Lote::where('producto_id', $value)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->whereDoesntHave('loteSecciones')
                    ->get();
                foreach ($lotesSinSec as $lss) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $lss->id, 'seccion_id' => $this->consumo_seccion_id],
                        ['cantidad_actual' => $lss->cantidad_actual]
                    );
                }

                $this->consumo_stock_disponible = (int) LoteSeccion::where('seccion_id', $this->consumo_seccion_id)
                    ->whereHas('lote', function ($q) use ($value, $sucursalId) {
                        $q->where('producto_id', $value)
                            ->where('sucursal_id', $sucursalId)
                            ->where('cantidad_actual', '>', 0)
                            ->where(function ($vq) {
                                $vq->whereNull('fecha_vencimiento')
                                    ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                            });
                    })
                    ->sum('cantidad_actual');
            }
        } else {
            $this->consumo_stock_disponible = 0;
        }
    }

    public function agregarConsumoACola(): void
    {
        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

        if (! $this->consumo_seccion_id) {
            $sec = Seccion::firstOrCreate(
                ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                ['nombre' => 'Farmacia Central', 'activo' => true]
            );
            $this->consumo_seccion_id = $sec->id;
        }

        $this->validate([
            'consumo_seccion_id' => ['required', 'exists:secciones,id'],
            'consumo_producto_id' => ['required', 'exists:productos,id'],
            'consumo_cantidad' => ['required', 'integer', 'min:1'],
            'consumo_precio_unitario' => ['required', 'numeric', 'min:0'],
            'consumo_observaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'consumo_seccion_id.required' => 'Seleccione el área o sección de donde sale el insumo.',
            'consumo_producto_id.required' => 'Seleccione el insumo o material utilizado.',
            'consumo_cantidad.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        $prod = Producto::find($this->consumo_producto_id);
        $seccion = Seccion::find($this->consumo_seccion_id);

        // Sincronizar lotes sin sección si existieran
        $lotesSinSec = Lote::where('producto_id', $this->consumo_producto_id)
            ->where('sucursal_id', $sucursalId)
            ->where('cantidad_actual', '>', 0)
            ->whereDoesntHave('loteSecciones')
            ->get();
        foreach ($lotesSinSec as $lss) {
            LoteSeccion::firstOrCreate(
                ['lote_id' => $lss->id, 'seccion_id' => $this->consumo_seccion_id],
                ['cantidad_actual' => $lss->cantidad_actual]
            );
        }

        $stockDisponible = (int) LoteSeccion::where('seccion_id', $this->consumo_seccion_id)
            ->whereHas('lote', function ($q) use ($sucursalId) {
                $q->where('producto_id', $this->consumo_producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
            })
            ->sum('cantidad_actual');

        // Validar acumulación en cola para el mismo producto y misma sección
        $yaEnCola = 0;
        foreach ($this->cola_consumos as $itemCola) {
            if ($itemCola['producto_id'] === $this->consumo_producto_id && $itemCola['seccion_id'] === $this->consumo_seccion_id) {
                $yaEnCola += (int) $itemCola['cantidad'];
            }
        }

        $totalRequerido = $yaEnCola + (int) $this->consumo_cantidad;

        if ($totalRequerido > $stockDisponible) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Stock insuficiente en el área',
                'text' => "No hay existencias suficientes de {$prod->nombre} en {$seccion->nombre}. Stock disponible: {$stockDisponible} unidades".($yaEnCola > 0 ? " (ya tiene {$yaEnCola} en lista)." : '.'),
            ]);

            return;
        }

        $this->cola_consumos[] = [
            'producto_id' => $prod->id,
            'nombre' => $prod->nombre,
            'seccion_id' => $this->consumo_seccion_id,
            'seccion_nombre' => $seccion?->nombre ?? 'Almacén',
            'cantidad' => (int) $this->consumo_cantidad,
            'precio_unitario' => (float) $this->consumo_precio_unitario,
            'observaciones' => $this->consumo_observaciones,
        ];

        $this->reset(['consumo_producto_id', 'consumo_observaciones', 'buscarInsumoConsumo']);
        $this->consumo_cantidad = 1;
        $this->consumo_precio_unitario = '0.00';
        $this->consumo_stock_disponible = 0;
    }

    public function eliminarConsumoDeCola(int $index): void
    {
        unset($this->cola_consumos[$index]);
        $this->cola_consumos = array_values($this->cola_consumos);
    }

    public function registrarConsumoExtra(): void
    {
        if (! $this->autorizarAccion('proformas.consumos.agregar', 'No cuenta con autorización para registrar consumos e insumos.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        if ($this->consumo_producto_id) {
            $this->agregarConsumoACola();
        }

        if (empty($this->cola_consumos)) {
            $this->validate([
                'consumo_producto_id' => ['required', 'exists:productos,id'],
            ], [
                'consumo_producto_id.required' => 'Debe seleccionar o encolar al menos un insumo.',
            ]);

            return;
        }

        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

        try {
            DB::transaction(function () use ($sucursalId) {
                foreach ($this->cola_consumos as $c) {
                    // Validar y descargar stock FEFO de los lotes disponibles en esa sección específica
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
                        throw new \Exception("Stock insuficiente para '{$c['nombre']}' en {$c['seccion_nombre']}. Disponible en esa área: {$stockTotal} unidades.");
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

                        // Descontar atómicamente de la sección y del lote con registro en Kardex
                        $lote->descontarDeSeccion(
                            $c['seccion_id'],
                            $descontar,
                            'Consumo Extra',
                            $this->proforma->id,
                            $this->proforma->recetaActiva?->id,
                            Auth::id()
                        );

                        $cantPendiente -= $descontar;
                    }

                    $obsTexto = ! empty($c['observaciones'])
                        ? "[{$c['seccion_nombre']}] {$c['observaciones']}"
                        : "Consumo hospitalario de {$c['seccion_nombre']}";

                    ConsumoExtra::create([
                        'proforma_id' => $this->proforma->id,
                        'producto_id' => $c['producto_id'],
                        'cantidad' => $c['cantidad'],
                        'precio_unitario' => $c['precio_unitario'],
                        'user_id' => Auth::id(),
                        'observaciones' => $obsTexto,
                    ]);
                }

                $this->proforma->recalcularTotal();
            });
        } catch (\Exception $e) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Error de Inventario',
                'text' => $e->getMessage(),
            ]);

            return;
        }

        $cant = count($this->cola_consumos);
        $this->cola_consumos = [];
        $this->cerrarModalConsumo();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Insumos Registrados',
            'text' => "Se han cargado {$cant} insumo(s) hospitalarios y se descontaron del inventario del área seleccionada.",
        ]);
    }

    #[On('eliminarConsumoExtra')]
    public function eliminarConsumoExtra(int $id): void
    {
        if (! $this->autorizarAccion('proformas.consumos.eliminar', 'No cuenta con autorización para eliminar consumos extras.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $consumo = ConsumoExtra::where('proforma_id', $this->proforma->id)->findOrFail($id);

        DB::transaction(function () use ($consumo) {
            // Reintegrar las existencias a la sección y lote de origen a través del Kardex
            $movimientos = MovimientoInventario::where('proforma_id', $this->proforma->id)
                ->where('producto_id', $consumo->producto_id)
                ->where('tipo_movimiento', 'Consumo Extra')
                ->latest('id')
                ->get();

            $cantidadAReintegrar = (int) $consumo->cantidad;
            foreach ($movimientos as $mov) {
                if ($cantidadAReintegrar <= 0) {
                    break;
                }

                $reintegrar = min($cantidadAReintegrar, $mov->cantidad);
                $lote = Lote::find($mov->lote_id);
                if ($lote) {
                    $secOrigen = $mov->seccion_origen_id ?? $this->proforma->sucursal?->secciones()->where('es_almacen_principal', true)->value('id');
                    if ($secOrigen) {
                        $lote->reintegrarASeccion($secOrigen, $reintegrar, 'Ajuste', $this->proforma->id, null, Auth::id());
                    } else {
                        $lote->increment('cantidad_actual', $reintegrar);
                    }
                }

                $cantidadAReintegrar -= $reintegrar;
            }

            $consumo->delete();
            $this->proforma->recalcularTotal();
        });

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Consumo Eliminado',
            'text' => 'El insumo fue removido de la proforma, las existencias fueron reintegradas al inventario y se recalculó el total.',
        ]);
    }

    // =========================================================================
    // 7. DESPACHOS DE FARMACIA E INSUMOS (INTEGRACIÓN DIRECTA)
    // =========================================================================
    public function abrirModalDespacho(): void
    {
        if (! $this->autorizarAccion('despachar-farmacia', 'No cuenta con autorización para dispensar medicamentos de farmacia.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->resetValidation();
        $this->proforma->load(['recetaActiva.detalles.producto', 'sucursal']);
        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

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

        $this->despacho_seccion_defecto_id = $secciones->where('es_almacen_principal', true)->first()?->id ?? $secciones->first()?->id;
        $this->despacho_extra_seccion_id = $this->despacho_seccion_defecto_id;

        $this->despachosItems = [];
        $this->despachosExtrasItems = [];
        $this->despacho_extra_producto_id = null;
        $this->despacho_extra_lote_id = null;
        $this->despacho_extra_cantidad = 1;
        $this->despacho_extra_observaciones = null;
        $this->buscarDespachoExtraProducto = '';

        if ($this->proforma->recetaActiva) {
            foreach ($this->proforma->recetaActiva->detalles as $det) {
                $despachadasPrevias = (int) MovimientoInventario::where('receta_id', $this->proforma->recetaActiva->id)
                    ->where('producto_id', $det->producto_id)
                    ->where('tipo_movimiento', 'Salida Receta')
                    ->sum('cantidad');

                $saldoPendiente = max(0, $det->cantidad - $despachadasPrevias);

                // Determinar la sección inicial sugerida
                $seccionElegida = $this->despacho_seccion_defecto_id;
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

                // Fallback para lotes sin lote_secciones previo
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
        }

        $this->modalDespachoProformaOpen = true;
    }

    public function cerrarModalDespacho(): void
    {
        $this->modalDespachoProformaOpen = false;
        $this->despachosItems = [];
        $this->despachosExtrasItems = [];
        $this->despacho_extra_producto_id = null;
        $this->despacho_extra_lote_id = null;
        $this->despacho_extra_cantidad = 1;
        $this->despacho_extra_observaciones = null;
        $this->buscarDespachoExtraProducto = '';
        $this->resetValidation();
    }

    public function updatedDespachoSeccionDefectoId($val): void
    {
        if (! $val) {
            return;
        }

        $this->despacho_extra_seccion_id = (int) $val;

        foreach ($this->despachosItems as $detId => $item) {
            $this->cambiarSeccionItemDespacho((int) $detId, (int) $val);
        }

        if ($this->despacho_extra_producto_id) {
            $this->updatedDespachoExtraProductoId($this->despacho_extra_producto_id);
        }
    }

    public function cambiarSeccionItemDespacho(int $detId, int $seccionId): void
    {
        if (! isset($this->despachosItems[$detId])) {
            return;
        }

        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

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

        if (! $lote) {
            $lote = Lote::where('producto_id', $this->despachosItems[$detId]['producto_id'])
                ->where('sucursal_id', $sucursalId)
                ->where('cantidad_actual', '>', 0)
                ->where(function ($q) {
                    $q->whereNull('fecha_vencimiento')
                        ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                })
                ->orderBy('fecha_vencimiento', 'asc')
                ->first();

            if ($lote) {
                LoteSeccion::firstOrCreate(
                    ['lote_id' => $lote->id, 'seccion_id' => $seccionId],
                    ['cantidad_actual' => $lote->cantidad_actual]
                );
            }
        }

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

    public function updatedDespachoExtraSeccionId($val): void
    {
        if ($this->despacho_extra_producto_id) {
            $this->updatedDespachoExtraProductoId($this->despacho_extra_producto_id);
        }
    }

    public function seleccionarDespachoExtraProducto(int $productoId): void
    {
        $this->despacho_extra_producto_id = $productoId;
        $this->buscarDespachoExtraProducto = '';
        $this->updatedDespachoExtraProductoId($productoId);
    }

    public function limpiarDespachoExtraProducto(): void
    {
        $this->despacho_extra_producto_id = null;
        $this->despacho_extra_lote_id = null;
        $this->despacho_extra_cantidad = 1;
        $this->despacho_extra_observaciones = null;
        $this->buscarDespachoExtraProducto = '';
    }

    public function updatedDespachoExtraProductoId($val): void
    {
        $this->despacho_extra_lote_id = null;
        $this->despacho_extra_cantidad = 1;

        if ($val && $this->despacho_extra_seccion_id) {
            $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;

            $primerLote = Lote::where('producto_id', $val)
                ->where('sucursal_id', $sucursalId)
                ->whereHas('loteSecciones', function ($q) {
                    $q->where('seccion_id', $this->despacho_extra_seccion_id)->where('cantidad_actual', '>', 0);
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

                if ($primerLote && $this->despacho_extra_seccion_id) {
                    LoteSeccion::firstOrCreate(
                        ['lote_id' => $primerLote->id, 'seccion_id' => $this->despacho_extra_seccion_id],
                        ['cantidad_actual' => $primerLote->cantidad_actual]
                    );
                }
            }

            $this->despacho_extra_lote_id = $primerLote?->id ?? null;
        }
    }

    public function agregarDespachoExtraItem(): void
    {
        $this->validate([
            'despacho_extra_seccion_id' => ['required', 'exists:secciones,id'],
            'despacho_extra_producto_id' => ['required', 'exists:productos,id'],
            'despacho_extra_lote_id' => ['required', 'exists:lotes,id'],
            'despacho_extra_cantidad' => ['required', 'integer', 'min:1'],
        ], [
            'despacho_extra_seccion_id.required' => 'Seleccione la sección o almacén de origen.',
            'despacho_extra_producto_id.required' => 'Seleccione un insumo o medicamento.',
            'despacho_extra_lote_id.required' => 'Seleccione un lote con existencias en esa área.',
            'despacho_extra_cantidad.min' => 'La cantidad mínima es 1.',
        ]);

        $lote = Lote::with('producto')->find($this->despacho_extra_lote_id);
        $stockEnSeccion = $lote ? $lote->stockEnSeccion($this->despacho_extra_seccion_id) : 0;

        if (! $lote || $stockEnSeccion < $this->despacho_extra_cantidad) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Stock insuficiente en la sección',
                'text' => "El lote seleccionado solo dispone de {$stockEnSeccion} unidades en esta sección.",
            ]);

            return;
        }

        $seccion = Seccion::find($this->despacho_extra_seccion_id);

        $yaExiste = false;
        foreach ($this->despachosExtrasItems as $key => $item) {
            if ($item['lote_id'] === $lote->id && $item['seccion_id'] === $this->despacho_extra_seccion_id) {
                $nuevaCantidad = $item['cantidad'] + $this->despacho_extra_cantidad;
                if ($nuevaCantidad > $stockEnSeccion) {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Stock insuficiente',
                        'text' => "La cantidad acumulada ({$nuevaCantidad}) excede las existencias del lote en la sección ({$stockEnSeccion}).",
                    ]);

                    return;
                }
                $this->despachosExtrasItems[$key]['cantidad'] = $nuevaCantidad;
                $this->despachosExtrasItems[$key]['subtotal'] = round($nuevaCantidad * $item['precio_unitario'], 2);
                $yaExiste = true;
                break;
            }
        }

        if (! $yaExiste) {
            $precio = (float) ($lote->producto->ultimo_precio_venta ?? 0);
            $this->despachosExtrasItems[] = [
                'producto_id' => $lote->producto_id,
                'producto_nombre' => $lote->producto->nombre,
                'unidad_medida' => $lote->producto->unidad_medida ?? 'Unidad',
                'lote_id' => $lote->id,
                'lote_codigo' => $lote->codigo_lote,
                'lote_vencimiento' => $lote->fecha_vencimiento?->format('d/m/Y') ?? 'S/F',
                'seccion_id' => $this->despacho_extra_seccion_id,
                'seccion_nombre' => $seccion?->nombre ?? 'Almacén',
                'stock_disponible' => $stockEnSeccion,
                'cantidad' => $this->despacho_extra_cantidad,
                'precio_unitario' => $precio,
                'subtotal' => round($this->despacho_extra_cantidad * $precio, 2),
                'observaciones' => $this->despacho_extra_observaciones,
            ];
        }

        $this->despacho_extra_producto_id = null;
        $this->despacho_extra_lote_id = null;
        $this->despacho_extra_cantidad = 1;
        $this->despacho_extra_observaciones = null;
        $this->buscarDespachoExtraProducto = '';
    }

    public function eliminarDespachoExtraItem(int $index): void
    {
        unset($this->despachosExtrasItems[$index]);
        $this->despachosExtrasItems = array_values($this->despachosExtrasItems);
    }

    public function procesarDespacho(): void
    {
        if (! $this->autorizarAccion('despachar-farmacia', 'No cuenta con autorización para dispensar medicamentos de farmacia.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $hayDespachosPrescritos = false;
        foreach ($this->despachosItems as $item) {
            $cant = (int) ($item['cantidad_despachar'] ?? 0);
            if ($cant > 0) {
                $hayDespachosPrescritos = true;
                if (empty($item['lote_id']) || empty($item['seccion_id'])) {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Datos incompletos',
                        'text' => "Debe seleccionar área y lote con stock para {$item['producto_nombre']}.",
                    ]);

                    return;
                }

                $lote = Lote::find($item['lote_id']);
                $stockEnSeccion = $lote ? $lote->stockEnSeccion($item['seccion_id']) : 0;

                if (! $lote || $stockEnSeccion < $cant) {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Stock insuficiente en área',
                        'text' => "El lote seleccionado para {$item['producto_nombre']} solo tiene {$stockEnSeccion} unidades en el área seleccionada.",
                    ]);

                    return;
                }

                if ($cant > $item['saldo_pendiente']) {
                    $this->dispatch('swal', [
                        'icon' => 'error',
                        'title' => 'Excede prescripción',
                        'text' => "La cantidad a despachar ({$cant}) excede el saldo pendiente ({$item['saldo_pendiente']}) de {$item['producto_nombre']}.",
                    ]);

                    return;
                }
            }
        }

        $hayExtras = ! empty($this->despachosExtrasItems);

        if (! $hayDespachosPrescritos && ! $hayExtras) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Sin unidades',
                'text' => 'Ingrese al menos una unidad a despachar en la receta o añada algún insumo extra.',
            ]);

            return;
        }

        // Validar existencias de cada extra en su sección
        foreach ($this->despachosExtrasItems as $extra) {
            $loteExtra = Lote::find($extra['lote_id']);
            $stockEnSec = $loteExtra ? $loteExtra->stockEnSeccion($extra['seccion_id']) : 0;

            if (! $loteExtra || $stockEnSec < $extra['cantidad']) {
                $this->dispatch('swal', [
                    'icon' => 'error',
                    'title' => 'Stock insuficiente en extras',
                    'text' => "El insumo extra {$extra['producto_nombre']} no cuenta con suficiente stock en {$extra['seccion_nombre']}.",
                ]);

                return;
            }
        }

        DB::transaction(function () {
            $receta = $this->proforma->recetaActiva;
            $userId = Auth::id() ?? $this->proforma->usuario_creador_id ?? $this->proforma->medicos->first()?->id ?? User::first()?->id;

            // 1. Salidas de prescripción médica con descuento atómico por sección
            foreach ($this->despachosItems as $detId => $item) {
                $cant = (int) ($item['cantidad_despachar'] ?? 0);
                if ($cant <= 0) {
                    continue;
                }

                $lote = Lote::findOrFail($item['lote_id']);

                $lote->descontarDeSeccion(
                    $item['seccion_id'],
                    $cant,
                    'Salida Receta',
                    $this->proforma->id,
                    $receta?->id,
                    $userId
                );

                $detalle = RecetaDetalle::find($detId);
                if ($detalle && $receta) {
                    $totalAcumulado = (int) MovimientoInventario::where('receta_id', $receta->id)
                        ->where('producto_id', $detalle->producto_id)
                        ->where('tipo_movimiento', 'Salida Receta')
                        ->sum('cantidad');

                    if ($totalAcumulado >= $detalle->cantidad) {
                        $detalle->update(['despachado' => true]);
                    }
                }
            }

            // 2. Insumos y medicamentos extras con descuento atómico por sección
            foreach ($this->despachosExtrasItems as $extra) {
                $loteExtra = Lote::findOrFail($extra['lote_id']);

                $loteExtra->descontarDeSeccion(
                    $extra['seccion_id'],
                    $extra['cantidad'],
                    'Consumo Extra',
                    $this->proforma->id,
                    $receta?->id,
                    $userId
                );

                $obsTexto = ! empty($extra['observaciones'])
                    ? "[{$extra['seccion_nombre']}] {$extra['observaciones']}"
                    : "Despacho extra de {$extra['seccion_nombre']} en proforma";

                ConsumoExtra::create([
                    'proforma_id' => $this->proforma->id,
                    'producto_id' => $extra['producto_id'],
                    'cantidad' => $extra['cantidad'],
                    'precio_unitario' => $extra['precio_unitario'],
                    'user_id' => $userId,
                    'observaciones' => $obsTexto,
                ]);
            }

            $this->proforma->recalcularTotal();
        });

        $this->cerrarModalDespacho();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Despacho Realizado!',
            'text' => 'Los medicamentos e insumos han sido dispensados y descontados del inventario del área asignada.',
        ]);
    }

    // =========================================================================
    // 7. HONORARIOS Y PAGOS MÉDICOS
    // =========================================================================
    public function abrirModalPagoMedico(?int $medicoId = null): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.gestionar', 'No cuenta con autorización para gestionar honorarios médicos.')) {
            return;
        }

        $this->resetValidation();
        $this->editando_pago_medico_id = null;
        $this->pago_medico_id = $medicoId;
        $this->pago_medico_monto = '';
        $this->pago_medico_observaciones = '';
        $this->pago_medico_fecha = now()->toDateString();
        $this->modalPagoMedicoOpen = true;
    }

    public function cerrarModalPagoMedico(): void
    {
        $this->modalPagoMedicoOpen = false;
        $this->resetValidation();
        $this->reset([
            'pago_medico_id',
            'pago_medico_monto',
            'pago_medico_observaciones',
            'pago_medico_fecha',
            'editando_pago_medico_id',
        ]);
    }

    public function editarPagoMedico(int $pagoId): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.gestionar', 'No cuenta con autorización para gestionar honorarios médicos.')) {
            return;
        }

        $this->resetValidation();
        $pago = ProformaPagoMedico::where('proforma_id', $this->proforma->id)->findOrFail($pagoId);

        $this->editando_pago_medico_id = $pago->id;
        $this->pago_medico_id = $pago->medico_id;
        $this->pago_medico_monto = number_format((float) $pago->monto, 2, '.', '');
        $this->pago_medico_observaciones = $pago->observaciones ?? '';
        $this->pago_medico_fecha = $pago->fecha_pago ? $pago->fecha_pago->toDateString() : now()->toDateString();
        $this->modalPagoMedicoOpen = true;
    }

    public function guardarPagoMedico(): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.gestionar', 'No cuenta con autorización para gestionar honorarios médicos.')) {
            return;
        }

        $this->validate([
            'pago_medico_id' => ['required', 'exists:users,id'],
            'pago_medico_monto' => ['required', 'numeric', 'min:0.01'],
            'pago_medico_fecha' => ['required', 'date'],
            'pago_medico_observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'pago_medico_id.required' => 'Debe seleccionar el médico asignado.',
            'pago_medico_monto.required' => 'Debe indicar el monto de honorarios a pagar.',
            'pago_medico_monto.min' => 'El monto debe ser mayor a 0.',
            'pago_medico_fecha.required' => 'La fecha de liquidación es requerida.',
        ]);

        if ($this->editando_pago_medico_id) {
            $pago = ProformaPagoMedico::where('proforma_id', $this->proforma->id)->findOrFail($this->editando_pago_medico_id);
            $pago->update([
                'medico_id' => $this->pago_medico_id,
                'monto' => (float) $this->pago_medico_monto,
                'observaciones' => $this->pago_medico_observaciones ? trim($this->pago_medico_observaciones) : null,
                'fecha_pago' => $this->pago_medico_fecha,
            ]);

            $mensaje = 'El registro de honorarios médicos fue actualizado correctamente.';
        } else {
            ProformaPagoMedico::create([
                'proforma_id' => $this->proforma->id,
                'medico_id' => $this->pago_medico_id,
                'monto' => (float) $this->pago_medico_monto,
                'observaciones' => $this->pago_medico_observaciones ? trim($this->pago_medico_observaciones) : null,
                'fecha_pago' => $this->pago_medico_fecha,
                'user_id' => Auth::id(),
            ]);

            $mensaje = 'El honorario médico fue registrado con éxito.';
        }

        $this->proforma->load('pagosMedicos.medico.especialidad', 'pagosMedicos.user');
        $this->cerrarModalPagoMedico();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Honorario Registrado',
            'text' => $mensaje,
        ]);
    }

    public function eliminarPagoMedico(int $pagoId): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.eliminar', 'No cuenta con autorización para eliminar pagos de honorarios médicos.')) {
            return;
        }

        $pago = ProformaPagoMedico::where('proforma_id', $this->proforma->id)->findOrFail($pagoId);
        $pago->delete();

        $this->proforma->load('pagosMedicos.medico.especialidad', 'pagosMedicos.user');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro Eliminado',
            'text' => 'El honorario médico fue retirado de la proforma.',
        ]);
    }

    public function render(): View
    {
        // Refrescar relaciones clave
        $this->proforma->load([
            'servicios.servicio.categoria',
            'solicitudes.tipoSolicitud',
            'calendarios' => fn ($q) => $q->orderBy('fecha')->orderBy('hora'),
            'recetaActiva.detalles.producto',
            'recetas' => fn ($q) => $q->with(['detalles.producto', 'doctor'])->latest(),
            'consumosExtras.producto',
            'consumosExtras.user',
            'pagosMedicos.medico.especialidad',
            'pagosMedicos.user',
            'movimientosInventario.producto.marca',
            'movimientosInventario.lote',
            'movimientosInventario.usuario',
            'movimientosInventario.receta',
        ]);

        $tiposSolicitudes = TipoSolicitud::orderBy('nombre')->get();
        $medicos = User::where('activo', true)->whereHas('rol', fn ($r) => $r->whereIn('nombre', ['Médico', 'Admin']))->get();

        // 1. Catálogo filtrado reactivo para Servicios
        $serviciosFiltrados = collect();
        if ($this->modalServicioOpen) {
            $sQuery = Servicio::where('estado', true)->with('categoria');
            if (! empty($this->buscarServicio)) {
                $sTerm = '%'.trim($this->buscarServicio).'%';
                $sQuery->where(function ($q) use ($sTerm) {
                    $q->where('nombre', 'like', $sTerm)
                        ->orWhere('descripcion', 'like', $sTerm)
                        ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', $sTerm));
                });
                $serviciosFiltrados = $sQuery->orderBy('nombre')->take(10)->get();
            } else {
                $serviciosFiltrados = $sQuery->orderBy('nombre')->take(8)->get();
            }
        }
        $servicioSeleccionado = $this->nuevo_servicio_id ? Servicio::with('categoria')->find($this->nuevo_servicio_id) : null;

        // 2. Catálogo reactivo para Recetas
        $sugerenciasMedicamentosReceta = [];
        if ($this->modalRecetaOpen) {
            foreach ($this->receta_medicamentos as $idx => $rItem) {
                $term = trim($rItem['busqueda'] ?? '');
                if (! empty($term)) {
                    $sugerenciasMedicamentosReceta[$idx] = Producto::where(function ($q) use ($term) {
                        $q->where('nombre', 'like', "%{$term}%")
                            ->orWhere('descripcion', 'like', "%{$term}%");
                    })->orderBy('nombre')->take(8)->get();
                } else {
                    $sugerenciasMedicamentosReceta[$idx] = collect();
                }
            }
        }

        // 3. Catálogo reactivo para Consumos Extras (Filtro estricto por stock disponible en sucursal y sección)
        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()->sucursal_id;
        $seccionesSucursal = Seccion::where('sucursal_id', $sucursalId)
            ->where('activo', true)
            ->orderBy('es_almacen_principal', 'desc')
            ->orderBy('nombre', 'asc')
            ->get();

        if ($seccionesSucursal->isEmpty()) {
            $central = Seccion::firstOrCreate(
                ['sucursal_id' => $sucursalId, 'es_almacen_principal' => true],
                ['nombre' => 'Farmacia Central', 'activo' => true]
            );
            $seccionesSucursal = collect([$central]);
        }

        $productosParaConsumo = collect();
        if ($this->modalConsumoOpen) {
            $cSecId = $this->consumo_seccion_id;
            $cQuery = Producto::whereHas('lotes', function ($q) use ($sucursalId, $cSecId) {
                $q->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
                if ($cSecId) {
                    $q->where(function ($sq) use ($cSecId) {
                        $sq->whereHas('secciones', fn ($ssq) => $ssq->where('secciones.id', $cSecId)->where('lote_secciones.cantidad_actual', '>', 0))
                            ->orWhereDoesntHave('secciones');
                    });
                }
            })->with(['marca', 'lotes' => function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
            }]);

            if (! empty($this->buscarInsumoConsumo)) {
                $cTerm = '%'.trim($this->buscarInsumoConsumo).'%';
                $cQuery->where(function ($q) use ($cTerm) {
                    $q->where('nombre', 'like', $cTerm)
                        ->orWhere('descripcion', 'like', $cTerm)
                        ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', $cTerm));
                });
                $productosParaConsumo = $cQuery->orderBy('nombre')->take(10)->get();
            } else {
                $productosParaConsumo = $cQuery->orderBy('nombre')->take(8)->get();
            }
        }
        $productoConsumoSeleccionado = $this->consumo_producto_id ? Producto::with('marca')->find($this->consumo_producto_id) : null;

        // 4. Parámetros para Modal de Despacho
        $lotesDisponiblesPorItem = [];
        $productosParaDespachoExtras = collect();
        $lotesParaDespachoExtra = collect();
        $productoDespachoExtraSeleccionado = null;

        if ($this->modalDespachoProformaOpen) {
            if ($this->proforma->recetaActiva) {
                foreach ($this->proforma->recetaActiva->detalles as $det) {
                    $itemSecId = $this->despachosItems[$det->id]['seccion_id'] ?? null;
                    $lQuery = Lote::where('producto_id', $det->producto_id)
                        ->where('sucursal_id', $sucursalId)
                        ->where('cantidad_actual', '>', 0)
                        ->where(function ($q) {
                            $q->whereNull('fecha_vencimiento')
                                ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                        });

                    if ($itemSecId) {
                        $lQuery->where(function ($sq) use ($itemSecId) {
                            $sq->whereHas('secciones', fn ($ssq) => $ssq->where('secciones.id', $itemSecId)->where('lote_secciones.cantidad_actual', '>', 0))
                                ->orWhereDoesntHave('secciones');
                        });
                    }

                    $lotesDisponiblesPorItem[$det->id] = $lQuery->orderBy('fecha_vencimiento', 'asc')->get();
                }
            }

            $dSecId = $this->despacho_extra_seccion_id;
            $pExtraQuery = Producto::whereHas('lotes', function ($q) use ($sucursalId, $dSecId) {
                $q->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($vq) {
                        $vq->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });
                if ($dSecId) {
                    $q->where(function ($sq) use ($dSecId) {
                        $sq->whereHas('secciones', fn ($ssq) => $ssq->where('secciones.id', $dSecId)->where('lote_secciones.cantidad_actual', '>', 0))
                            ->orWhereDoesntHave('secciones');
                    });
                }
            })->with(['marca', 'lotes' => function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId)->where('cantidad_actual', '>', 0);
            }]);

            if (! empty($this->buscarDespachoExtraProducto)) {
                $searchExtra = '%'.trim($this->buscarDespachoExtraProducto).'%';
                $pExtraQuery->where(function ($q) use ($searchExtra) {
                    $q->where('nombre', 'like', $searchExtra)
                        ->orWhere('descripcion', 'like', $searchExtra)
                        ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', $searchExtra));
                });
                $productosParaDespachoExtras = $pExtraQuery->orderBy('nombre')->take(10)->get();
            } else {
                $productosParaDespachoExtras = $pExtraQuery->orderBy('nombre')->take(8)->get();
            }

            if ($this->despacho_extra_producto_id) {
                $productoDespachoExtraSeleccionado = Producto::with('marca')->find($this->despacho_extra_producto_id);
                $lExtraQuery = Lote::where('producto_id', $this->despacho_extra_producto_id)
                    ->where('sucursal_id', $sucursalId)
                    ->where('cantidad_actual', '>', 0)
                    ->where(function ($q) {
                        $q->whereNull('fecha_vencimiento')
                            ->orWhere('fecha_vencimiento', '>=', now()->toDateString());
                    });

                if ($dSecId) {
                    $lExtraQuery->where(function ($sq) use ($dSecId) {
                        $sq->whereHas('secciones', fn ($ssq) => $ssq->where('secciones.id', $dSecId)->where('lote_secciones.cantidad_actual', '>', 0))
                            ->orWhereDoesntHave('secciones');
                    });
                }

                $lotesParaDespachoExtra = $lExtraQuery->orderBy('fecha_vencimiento', 'asc')->get();
            }
        }

        // 5. Historial de Despachos de esta Proforma
        $movimientosDespacho = $this->proforma->movimientosInventario()
            ->with(['producto.marca', 'lote', 'usuario', 'receta', 'seccionOrigen', 'seccionDestino'])
            ->latest('id')
            ->get();

        // Totales por rubro para el consolidado financiero
        $subtotalServicios = (float) $this->proforma->servicios->sum('costo_final');
        $subtotalConsumos = (float) $this->proforma->consumosExtras->sum(fn ($c) => $c->cantidad * $c->precio_unitario);
        $subtotalFarmacia = $this->proforma->totalDespachosFarmacia();
        $totalPrescripcionReferencial = $this->proforma->totalPrescripcionReferencial();

        return view('livewire.proformas.proforma-detalle', [
            'tiposSolicitudes' => $tiposSolicitudes,
            'medicos' => $medicos,
            'serviciosFiltrados' => $serviciosFiltrados,
            'servicioSeleccionado' => $servicioSeleccionado,
            'sugerenciasMedicamentosReceta' => $sugerenciasMedicamentosReceta,
            'seccionesSucursal' => $seccionesSucursal,
            'productosParaConsumo' => $productosParaConsumo,
            'productoConsumoSeleccionado' => $productoConsumoSeleccionado,
            'lotesDisponiblesPorItem' => $lotesDisponiblesPorItem,
            'productosParaDespachoExtras' => $productosParaDespachoExtras,
            'lotesParaDespachoExtra' => $lotesParaDespachoExtra,
            'productoDespachoExtraSeleccionado' => $productoDespachoExtraSeleccionado,
            'movimientosDespacho' => $movimientosDespacho,
            'subtotalServicios' => $subtotalServicios,
            'subtotalConsumos' => $subtotalConsumos,
            'subtotalFarmacia' => $subtotalFarmacia,
            'totalPrescripcionReferencial' => $totalPrescripcionReferencial,
        ]);
    }
}
