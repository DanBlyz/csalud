<?php

namespace App\Livewire\Proformas;

use App\Models\Caja;
use App\Models\Cirugia;
use App\Models\ConsumoExtra;
use App\Models\Epicrisis;
use App\Models\Evolucion;
use App\Models\HistoriaClinica;
use App\Models\Indicacion;
use App\Models\Institucion;
use App\Models\Internacion;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\MovimientoInventario;
use App\Models\Pago;
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
use Illuminate\Database\Eloquent\Collection;
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

    // Modal / Gestión Historia Clínica
    public bool $modalHistoriaClinicaOpen = false;

    public string $hc_procedencia = '';

    public string $hc_residencia = '';

    public string $hc_fuente_informacion = 'Paciente';

    public string $hc_motivo_consulta = '';

    public string $hc_enfermedad_actual = '';

    public string $hc_antecedentes_patologicos = '';

    public string $hc_antecedentes_no_patologicos = '';

    public string $hc_menarca = '';

    public string $hc_fum = '';

    public string $hc_gestas = '';

    public string $hc_partos = '';

    public string $hc_cesareas = '';

    public string $hc_abortos = '';

    public string $hc_pap = '';

    public string $hc_gineco_otros = '';

    public string $hc_pa = '';

    public string $hc_fc = '';

    public string $hc_fr = '';

    public string $hc_temperatura = '';

    public string $hc_sato2 = '';

    public string $hc_peso = '';

    public string $hc_talla = '';

    public string $hc_imc = '';

    public string $hc_examen_fisico = '';

    public string $hc_diagnostico = '';

    public string $hc_conducta = '';

    // Modal / Gestión de Epicrisis (Alta y Egreso del Paciente)
    public bool $modalEpicrisisOpen = false;

    public string $epi_cama = '';

    public string $epi_servicio = '';

    public ?string $epi_fecha_egreso = null;

    public string $epi_condiciones_ingreso = '';

    public string $epi_diagnostico_ingreso = '';

    public string $epi_condiciones_egreso = 'Estable';

    public string $epi_diagnostico_egreso = '';

    public string $epi_causa_egreso = 'Alta médica';

    public string $epi_estudios_complementarios = '';

    public string $epi_antecedentes_relevantes = '';

    public string $epi_historia_medica_actual = '';

    public string $epi_procedimientos = '';

    public string $epi_tratamiento_realizado = '';

    public string $epi_indicaciones_alta = '';

    public string $epi_proxima_cita = '';

    public string $epi_observaciones = '';

    // ==========================================
    // MODAL CIRUGÍAS / DESCARGO DE QUIRÓFANO
    // ==========================================
    public bool $modalCirugiaOpen = false;

    public ?int $cirugia_id_en_edicion = null;

    public string $cir_numero = '';

    public bool $cir_es_institucional = true;

    public bool $cir_es_convenio = false;

    public string $cir_convenio_nombre = '';

    public ?string $cir_fecha = null;

    public string $cir_especialidad = '';

    public string $cir_sala = '';

    public string $cir_cama = '';

    public string $cir_hora_ingreso_quirofano = '';

    public string $cir_hora_salida_quirofano = '';

    public string $cir_hora_inicio_anestesia = '';

    public string $cir_hora_fin_anestesia = '';

    public string $cir_hora_inicio_cirugia = '';

    public string $cir_hora_fin_cirugia = '';

    public string $cir_diagnostico_operacion = '';

    public string $cir_cirugia_efectuada = '';

    public string $cir_tipo_cirugia = 'Media';

    public string $cir_tipo_anestesia = '';

    public string $cir_analgesia = '';

    public ?int $cir_cirujano_id = null;

    public ?int $cir_primer_ayudante_id = null;

    public ?int $cir_segundo_ayudante_id = null;

    public ?int $cir_anestesiologo_id = null;

    public ?int $cir_instrumentador_id = null;

    public ?int $cir_circulante_id = null;

    public bool $cir_torre_laparoscopia = false;

    public bool $cir_bomba_infusion = false;

    public bool $cir_maquina_aspiracion = false;

    public string $cir_material_importadora = '';

    public bool $cir_intensificador_imagenes = false;

    public bool $cir_maquina_anestesiologia = false;

    public bool $cir_monitor_multiparametrico = false;

    public bool $cir_ecografo = false;

    public bool $cir_rayos_x = false;

    public bool $cir_oxigeno_central = false;

    public bool $cir_electrobisturi = false;

    public string $cir_patologia = '';

    public string $cir_otros_equipos = '';

    public string $cir_observaciones = '';

    // Selector y cola de medicamentos para el descargo de la cirugía
    public string $buscarMedicamentoCirugia = '';

    public ?int $cir_medicamento_producto_id = null;

    public ?int $cir_medicamento_seccion_id = null;

    public string $cir_medicamento_area = 'Anestesiología'; // Anestesiología o Enfermería

    public int $cir_medicamento_cantidad = 1;

    public string $cir_medicamento_costo_unitario = '0.00';

    public string $cir_medicamento_indicaciones = '';

    public array $cola_medicamentos_cirugia = [];

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

    // Modal Hoja de Evolución
    public bool $modalEvolucionOpen = false;

    public ?int $evolucion_id = null;

    public string $evolucion_fecha_hora = '';

    public string $evolucion_descripcion = '';

    // Modal Hoja de Indicaciones
    public bool $modalIndicacionOpen = false;

    public ?int $indicacion_id = null;

    public string $indicacion_fecha_hora = '';

    public string $indicacion_descripcion = '';

    // Modal Hoja de Internación
    public bool $modalInternacionOpen = false;

    public ?int $internacion_id = null;

    public string $internacion_fecha_hora = '';

    public string $internacion_descripcion = '';

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

    public string $buscarMedicamentoReceta = '';

    public ?int $receta_nuevo_producto_id = null;

    public string $receta_nuevo_producto_nombre = '';

    public string $receta_nuevo_unidad_medida = '';

    public string $receta_nuevo_marca_nombre = '';

    public int $receta_nuevo_stock_disponible = 0;

    public int|string $receta_nuevo_cantidad = 1;

    public string $receta_nuevo_indicaciones = '';

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

    public ?int $pago_medico_caja_id = null;

    public string $pago_medico_tipo_pago = 'Efectivo';

    public string $pago_medico_numero_referencia = '';

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
        'evoluciones' => 'proformas.evolucion.ver',
        'indicaciones' => 'proformas.indicacion.ver',
        'internaciones' => 'proformas.internacion.ver',
        'calendario' => 'proformas.cronograma.ver',
        'cirugias' => 'proformas.cirugia.ver',
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
            'historiaClinica.usuarioCreador',
            'epicrisis.usuarioCreador',
            'solicitudes.tipoSolicitud',
            'evoluciones.usuarioCreador',
            'indicaciones.usuarioCreador',
            'internaciones.usuarioCreador',
            'calendarios',
            'cirugias.cirujano',
            'cirugias.primerAyudante',
            'cirugias.segundoAyudante',
            'cirugias.anestesiologo',
            'cirugias.instrumentador',
            'cirugias.circulante',
            'cirugias.receta.detalles.producto',
            'cirugias.receta.detalles.seccion',
            'cirugias.usuarioCreador',
            'recetaActiva.detalles.producto',
            'recetaActiva.doctor',
            'recetas.detalles.producto',
            'recetas.doctor',
            'consumosExtras.producto',
            'consumosExtras.user',
            'pagos',
            'pagosMedicos.medico.especialidad',
            'pagosMedicos.user',
            'pagosMedicos.caja.user',
            'pagosMedicos.pago',
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
    // GESTIÓN DE HISTORIA CLÍNICA DE LA PROFORMA
    // =========================================================================
    public function abrirModalHistoriaClinica(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para editar la historia clínica de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $hc = $this->proforma->historiaClinica;

        $this->hc_procedencia = $hc?->procedencia ?? '';
        $this->hc_residencia = $hc?->residencia ?? ($this->proforma->paciente->direccion ?? '');
        $this->hc_fuente_informacion = $hc?->fuente_informacion ?? 'Paciente';
        $this->hc_motivo_consulta = $hc?->motivo_consulta ?? ($this->proforma->motivo_consulta ?? '');
        $this->hc_enfermedad_actual = $hc?->enfermedad_actual ?? '';
        $this->hc_antecedentes_patologicos = $hc?->antecedentes_patologicos ?? ($this->proforma->paciente->antecedentes_alergias ? 'Alergias / Antecedentes: '.$this->proforma->paciente->antecedentes_alergias : '');
        $this->hc_antecedentes_no_patologicos = $hc?->antecedentes_no_patologicos ?? '';
        $this->hc_menarca = $hc?->menarca ?? '';
        $this->hc_fum = $hc?->fum ?? '';
        $this->hc_gestas = $hc?->gestas ?? '';
        $this->hc_partos = $hc?->partos ?? '';
        $this->hc_cesareas = $hc?->cesareas ?? '';
        $this->hc_abortos = $hc?->abortos ?? '';
        $this->hc_pap = $hc?->pap ?? '';
        $this->hc_gineco_otros = $hc?->gineco_otros ?? '';
        $this->hc_pa = $hc?->pa ?? '';
        $this->hc_fc = $hc?->fc ?? '';
        $this->hc_fr = $hc?->fr ?? '';
        $this->hc_temperatura = $hc?->temperatura ?? '';
        $this->hc_sato2 = $hc?->sato2 ?? '';
        $this->hc_peso = $hc?->peso ?? '';
        $this->hc_talla = $hc?->talla ?? '';
        $this->hc_imc = $hc?->imc ?? '';
        $this->hc_examen_fisico = $hc?->examen_fisico ?? '';
        $this->hc_diagnostico = $hc?->diagnostico ?? ($this->proforma->diagnostico ?? '');
        $this->hc_conducta = $hc?->conducta ?? '';

        $this->modalHistoriaClinicaOpen = true;
    }

    public function cerrarModalHistoriaClinica(): void
    {
        $this->modalHistoriaClinicaOpen = false;
    }

    public function updatedHcPeso(): void
    {
        $this->calcularHcImc();
    }

    public function updatedHcTalla(): void
    {
        $this->calcularHcImc();
    }

    protected function calcularHcImc(): void
    {
        $peso = (float) str_replace(',', '.', $this->hc_peso);
        $talla = (float) str_replace(',', '.', $this->hc_talla);

        if ($peso > 0 && $talla > 0) {
            $tallaM = $talla > 3 ? $talla / 100 : $talla;
            $imc = $peso / ($tallaM * $tallaM);
            $this->hc_imc = number_format($imc, 1, '.', '');
        }
    }

    public function guardarHistoriaClinica(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para editar la historia clínica de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'hc_procedencia' => ['nullable', 'string', 'max:150'],
            'hc_residencia' => ['nullable', 'string', 'max:255'],
            'hc_fuente_informacion' => ['nullable', 'string', 'max:100'],
            'hc_motivo_consulta' => ['nullable', 'string', 'max:1000'],
            'hc_enfermedad_actual' => ['nullable', 'string', 'max:5000'],
            'hc_antecedentes_patologicos' => ['nullable', 'string', 'max:5000'],
            'hc_antecedentes_no_patologicos' => ['nullable', 'string', 'max:5000'],
            'hc_menarca' => ['nullable', 'string', 'max:50'],
            'hc_fum' => ['nullable', 'string', 'max:50'],
            'hc_gestas' => ['nullable', 'string', 'max:20'],
            'hc_partos' => ['nullable', 'string', 'max:20'],
            'hc_cesareas' => ['nullable', 'string', 'max:20'],
            'hc_abortos' => ['nullable', 'string', 'max:20'],
            'hc_pap' => ['nullable', 'string', 'max:100'],
            'hc_gineco_otros' => ['nullable', 'string', 'max:255'],
            'hc_pa' => ['nullable', 'string', 'max:30'],
            'hc_fc' => ['nullable', 'string', 'max:30'],
            'hc_fr' => ['nullable', 'string', 'max:30'],
            'hc_temperatura' => ['nullable', 'string', 'max:30'],
            'hc_sato2' => ['nullable', 'string', 'max:30'],
            'hc_peso' => ['nullable', 'string', 'max:30'],
            'hc_talla' => ['nullable', 'string', 'max:30'],
            'hc_imc' => ['nullable', 'string', 'max:30'],
            'hc_examen_fisico' => ['nullable', 'string', 'max:5000'],
            'hc_diagnostico' => ['nullable', 'string', 'max:1000'],
            'hc_conducta' => ['nullable', 'string', 'max:5000'],
        ]);

        $hc = HistoriaClinica::updateOrCreate(
            ['proforma_id' => $this->proforma->id],
            [
                'procedencia' => $this->hc_procedencia ?: null,
                'residencia' => $this->hc_residencia ?: null,
                'fuente_informacion' => $this->hc_fuente_informacion ?: 'Paciente',
                'motivo_consulta' => $this->hc_motivo_consulta ?: null,
                'enfermedad_actual' => $this->hc_enfermedad_actual ?: null,
                'antecedentes_patologicos' => $this->hc_antecedentes_patologicos ?: null,
                'antecedentes_no_patologicos' => $this->hc_antecedentes_no_patologicos ?: null,
                'menarca' => $this->hc_menarca ?: null,
                'fum' => $this->hc_fum ?: null,
                'gestas' => $this->hc_gestas ?: null,
                'partos' => $this->hc_partos ?: null,
                'cesareas' => $this->hc_cesareas ?: null,
                'abortos' => $this->hc_abortos ?: null,
                'pap' => $this->hc_pap ?: null,
                'gineco_otros' => $this->hc_gineco_otros ?: null,
                'pa' => $this->hc_pa ?: null,
                'fc' => $this->hc_fc ?: null,
                'fr' => $this->hc_fr ?: null,
                'temperatura' => $this->hc_temperatura ?: null,
                'sato2' => $this->hc_sato2 ?: null,
                'peso' => $this->hc_peso ?: null,
                'talla' => $this->hc_talla ?: null,
                'imc' => $this->hc_imc ?: null,
                'examen_fisico' => $this->hc_examen_fisico ?: null,
                'diagnostico' => $this->hc_diagnostico ?: null,
                'conducta' => $this->hc_conducta ?: null,
                'usuario_modificador_id' => Auth::id(),
            ]
        );

        if (! $hc->usuario_creador_id) {
            $hc->update(['usuario_creador_id' => Auth::id()]);
        }

        // Sincronizar motivo y diagnóstico en la proforma
        $this->proforma->update([
            'motivo_consulta' => $this->hc_motivo_consulta ?: $this->proforma->motivo_consulta,
            'diagnostico' => $this->hc_diagnostico ?: $this->proforma->diagnostico,
        ]);

        $this->proforma->load('historiaClinica.usuarioCreador');

        $this->cerrarModalHistoriaClinica();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Historia Clínica Guardada',
            'text' => 'Los antecedentes, examen físico y datos de la historia clínica se han guardado exitosamente.',
        ]);
    }

    // =========================================================================
    // GESTIÓN DE EPICRISIS (ALTA / EGRESO DEL PACIENTE)
    // =========================================================================
    public function abrirModalEpicrisis(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para gestionar la epicrisis de la proforma.')) {
            return;
        }

        $epi = $this->proforma->epicrisis;

        if ($epi) {
            $this->epi_cama = $epi->cama ?? '';
            $this->epi_servicio = $epi->servicio ?? '';
            $this->epi_fecha_egreso = $epi->fecha_egreso?->format('Y-m-d\TH:i');
            $this->epi_condiciones_ingreso = $epi->condiciones_ingreso ?? '';
            $this->epi_diagnostico_ingreso = $epi->diagnostico_ingreso ?? '';
            $this->epi_condiciones_egreso = $epi->condiciones_egreso ?? 'Estable';
            $this->epi_diagnostico_egreso = $epi->diagnostico_egreso ?? '';
            $this->epi_causa_egreso = $epi->causa_egreso ?? 'Alta médica';
            $this->epi_estudios_complementarios = $epi->estudios_complementarios ?? '';
            $this->epi_antecedentes_relevantes = $epi->antecedentes_relevantes ?? '';
            $this->epi_historia_medica_actual = $epi->historia_medica_actual ?? '';
            $this->epi_procedimientos = $epi->procedimientos ?? '';
            $this->epi_tratamiento_realizado = $epi->tratamiento_realizado ?? '';
            $this->epi_indicaciones_alta = $epi->indicaciones_alta ?? '';
            $this->epi_proxima_cita = $epi->proxima_cita ?? '';
            $this->epi_observaciones = $epi->observaciones ?? '';
        } else {
            // Precarga inteligente basada en el expediente clínico
            $this->epi_cama = $this->proforma->pieza ?: ($this->proforma->tipo_atencion === 'Internacion' ? 'Sala 1 - Cama General' : 'Consultorio Externo');
            $this->epi_servicio = $this->proforma->tipo_atencion === 'Internacion' ? 'Hospitalización' : 'Medicina General';
            $this->epi_fecha_egreso = $this->proforma->fecha_salida ? $this->proforma->fecha_salida->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->epi_condiciones_ingreso = 'Paciente hemodinámicamente estable al ingreso.';
            $this->epi_diagnostico_ingreso = $this->proforma->diagnostico ?: ($this->proforma->historiaClinica?->diagnostico ?: '');
            $this->epi_condiciones_egreso = 'Paciente afebril, hemodinámicamente estable, con evolución favorable y buena tolerancia oral.';
            $this->epi_diagnostico_egreso = $this->proforma->diagnostico ?: ($this->proforma->historiaClinica?->diagnostico ?: '');
            $this->epi_causa_egreso = 'Alta médica por mejoría clínica';

            $solicitudes = $this->proforma->solicitudes->pluck('tipoSolicitud.nombre')->filter()->unique()->implode(', ');
            $this->epi_estudios_complementarios = $solicitudes ?: 'Estudios rutinarios dentro de parámetros normales.';

            $antecedentes = $this->proforma->historiaClinica?->antecedentes_patologicos ?: ($this->proforma->paciente->antecedentes_alergias ? 'Alergias: '.$this->proforma->paciente->antecedentes_alergias : 'Sin antecedentes patológicos de relevancia reportados.');
            $this->epi_antecedentes_relevantes = $antecedentes;

            $this->epi_historia_medica_actual = $this->proforma->historiaClinica?->enfermedad_actual ?: ($this->proforma->motivo_consulta ? 'Ingresa con cuadro de: '.$this->proforma->motivo_consulta : 'Paciente cumple esquema hospitalario satisfactorio.');

            $servicios = $this->proforma->servicios->pluck('servicio.nombre')->filter()->unique()->implode(', ');
            $this->epi_procedimientos = $servicios ?: 'Atención médica y cuidados continuos de enfermería.';

            $fármacos = $this->proforma->recetaActiva?->detalles?->map(function ($det) {
                return $det->producto->nombre.($det->indicaciones ? ' ('.$det->indicaciones.')' : '');
            })->filter()->implode('; ');
            $this->epi_tratamiento_realizado = $fármacos ?: 'Terapia médica hospitalaria de soporte.';

            $this->epi_indicaciones_alta = 'Reposo relativo en domicilio, cumplimiento estricto del tratamiento farmacológico prescrito y acudir de inmediato a emergencias ante cualquier signo de alarma.';
            $this->epi_proxima_cita = 'Control por consulta externa en 7 días.';
            $this->epi_observaciones = 'Se entrega alta médica formal con recomendaciones de cuidado.';
        }

        $this->modalEpicrisisOpen = true;
    }

    public function cerrarModalEpicrisis(): void
    {
        $this->modalEpicrisisOpen = false;
    }

    public function guardarEpicrisis(): void
    {
        if (! $this->autorizarAccion('proformas.editar', 'No cuenta con permiso para guardar la epicrisis de la proforma.')) {
            return;
        }

        $this->validate([
            'epi_cama' => ['nullable', 'string', 'max:100'],
            'epi_servicio' => ['nullable', 'string', 'max:150'],
            'epi_fecha_egreso' => ['nullable', 'date'],
            'epi_condiciones_ingreso' => ['nullable', 'string', 'max:2000'],
            'epi_diagnostico_ingreso' => ['nullable', 'string', 'max:2000'],
            'epi_condiciones_egreso' => ['nullable', 'string', 'max:2000'],
            'epi_diagnostico_egreso' => ['nullable', 'string', 'max:2000'],
            'epi_causa_egreso' => ['nullable', 'string', 'max:255'],
            'epi_estudios_complementarios' => ['nullable', 'string', 'max:5000'],
            'epi_antecedentes_relevantes' => ['nullable', 'string', 'max:5000'],
            'epi_historia_medica_actual' => ['nullable', 'string', 'max:5000'],
            'epi_procedimientos' => ['nullable', 'string', 'max:5000'],
            'epi_tratamiento_realizado' => ['nullable', 'string', 'max:5000'],
            'epi_indicaciones_alta' => ['nullable', 'string', 'max:5000'],
            'epi_proxima_cita' => ['nullable', 'string', 'max:255'],
            'epi_observaciones' => ['nullable', 'string', 'max:5000'],
        ]);

        $epi = Epicrisis::updateOrCreate(
            ['proforma_id' => $this->proforma->id],
            [
                'cama' => $this->epi_cama ?: null,
                'servicio' => $this->epi_servicio ?: null,
                'fecha_egreso' => $this->epi_fecha_egreso ?: null,
                'condiciones_ingreso' => $this->epi_condiciones_ingreso ?: null,
                'diagnostico_ingreso' => $this->epi_diagnostico_ingreso ?: null,
                'condiciones_egreso' => $this->epi_condiciones_egreso ?: null,
                'diagnostico_egreso' => $this->epi_diagnostico_egreso ?: null,
                'causa_egreso' => $this->epi_causa_egreso ?: null,
                'estudios_complementarios' => $this->epi_estudios_complementarios ?: null,
                'antecedentes_relevantes' => $this->epi_antecedentes_relevantes ?: null,
                'historia_medica_actual' => $this->epi_historia_medica_actual ?: null,
                'procedimientos' => $this->epi_procedimientos ?: null,
                'tratamiento_realizado' => $this->epi_tratamiento_realizado ?: null,
                'indicaciones_alta' => $this->epi_indicaciones_alta ?: null,
                'proxima_cita' => $this->epi_proxima_cita ?: null,
                'observaciones' => $this->epi_observaciones ?: null,
                'usuario_modificador_id' => Auth::id(),
            ]
        );

        if (! $epi->usuario_creador_id) {
            $epi->update(['usuario_creador_id' => Auth::id()]);
        }

        // Si la proforma no tenía fecha de salida registrada y se indicó fecha de egreso, sincronizar
        if ($this->epi_fecha_egreso && ! $this->proforma->fecha_salida) {
            $this->proforma->update(['fecha_salida' => $this->epi_fecha_egreso]);
        }

        $this->proforma->load('epicrisis.usuarioCreador');

        $this->cerrarModalEpicrisis();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Epicrisis Registrada',
            'text' => 'El documento de epicrisis médica de alta ha sido guardado exitosamente.',
        ]);
    }

    // =========================================================================
    // CIRUGÍAS / DESCARGO DE QUIRÓFANO
    // =========================================================================

    public function abrirModalNuevaCirugia(): void
    {
        if (! $this->autorizarAccion('proformas.cirugia.crear', 'No cuenta con permiso para registrar descargos de quirófano.')) {
            return;
        }

        $this->cirugia_id_en_edicion = null;
        $count = $this->proforma->cirugias()->count() + 1;
        $this->cir_numero = 'CQ-'.str_pad($this->proforma->id, 4, '0', STR_PAD_LEFT).'-'.str_pad($count, 2, '0', STR_PAD_LEFT);

        // Autocompletado del régimen de convenio si el paciente cuenta con institución
        $institucionPaciente = $this->proforma->paciente->institucion
            ?? ($this->proforma->paciente->institucion_id ? Institucion::find($this->proforma->paciente->institucion_id) : null);

        if ($institucionPaciente) {
            $this->cir_es_convenio = true;
            $this->cir_es_institucional = false;
            $this->cir_convenio_nombre = $institucionPaciente->nombre;
        } else {
            $this->cir_es_convenio = false;
            $this->cir_es_institucional = true;
            $this->cir_convenio_nombre = '';
        }

        $this->cir_fecha = now()->format('Y-m-d');
        $this->cir_especialidad = $this->proforma->medicos->first()?->especialidad?->nombre ?? 'Cirugía General';
        $this->cir_sala = 'Quirófano Central';
        $this->cir_cama = $this->proforma->pieza ?? 'Recuperación';

        $this->cir_hora_ingreso_quirofano = now()->format('H:i');
        $this->cir_hora_salida_quirofano = '';
        $this->cir_hora_inicio_anestesia = now()->format('H:i');
        $this->cir_hora_fin_anestesia = '';
        $this->cir_hora_inicio_cirugia = now()->format('H:i');
        $this->cir_hora_fin_cirugia = '';

        $this->cir_diagnostico_operacion = $this->proforma->diagnostico ?? '';
        $this->cir_cirugia_efectuada = '';
        $this->cir_tipo_cirugia = 'Media';
        $this->cir_tipo_anestesia = 'General Balanceada';
        $this->cir_analgesia = '';

        $primerMedicoId = $this->proforma->medicos->first()?->id ?? Auth::id();
        $this->cir_cirujano_id = $primerMedicoId;
        $this->cir_primer_ayudante_id = null;
        $this->cir_segundo_ayudante_id = null;
        $this->cir_anestesiologo_id = null;
        $this->cir_instrumentador_id = null;
        $this->cir_circulante_id = null;

        $this->cir_torre_laparoscopia = false;
        $this->cir_bomba_infusion = false;
        $this->cir_maquina_aspiracion = false;
        $this->cir_material_importadora = '';
        $this->cir_intensificador_imagenes = false;
        $this->cir_maquina_anestesiologia = false;
        $this->cir_monitor_multiparametrico = false;
        $this->cir_ecografo = false;
        $this->cir_rayos_x = false;
        $this->cir_oxigeno_central = false;
        $this->cir_electrobisturi = false;
        $this->cir_patologia = '';
        $this->cir_otros_equipos = '';
        $this->cir_observaciones = '';

        // Sección por defecto para insumos de quirófano
        $quirofanoSec = Seccion::where('nombre', 'like', '%Quirofano%')->first();
        $this->cir_medicamento_seccion_id = $quirofanoSec?->id ?? Seccion::first()?->id;
        $this->cir_medicamento_producto_id = null;
        $this->buscarMedicamentoCirugia = '';
        $this->cir_medicamento_area = 'Anestesiología';
        $this->cir_medicamento_cantidad = 1;
        $this->cir_medicamento_costo_unitario = '0.00';
        $this->cir_medicamento_indicaciones = '';
        $this->cola_medicamentos_cirugia = [];

        $this->modalCirugiaOpen = true;
    }

    public function abrirModalEditarCirugia(int $cirugiaId): void
    {
        if (! $this->autorizarAccion('proformas.cirugia.editar', 'No cuenta con permiso para editar descargos de quirófano.')) {
            return;
        }

        $cirugia = Cirugia::with(['receta.detalles.producto', 'receta.detalles.seccion'])->findOrFail($cirugiaId);

        $this->cirugia_id_en_edicion = $cirugia->id;
        $this->cir_numero = $cirugia->numero ?? '';
        $this->cir_es_institucional = (bool) $cirugia->es_institucional;
        $this->cir_es_convenio = (bool) $cirugia->es_convenio;

        $institucionPaciente = $this->proforma->paciente->institucion
            ?? ($this->proforma->paciente->institucion_id ? Institucion::find($this->proforma->paciente->institucion_id) : null);

        $this->cir_convenio_nombre = $cirugia->convenio_nombre
            ?: ($this->cir_es_convenio && $institucionPaciente ? $institucionPaciente->nombre : '');
        $this->cir_fecha = $cirugia->fecha?->format('Y-m-d') ?? now()->format('Y-m-d');
        $this->cir_especialidad = $cirugia->especialidad ?? '';
        $this->cir_sala = $cirugia->sala ?? '';
        $this->cir_cama = $cirugia->cama ?? '';
        $this->cir_hora_ingreso_quirofano = $cirugia->hora_ingreso_quirofano ?? '';
        $this->cir_hora_salida_quirofano = $cirugia->hora_salida_quirofano ?? '';
        $this->cir_hora_inicio_anestesia = $cirugia->hora_inicio_anestesia ?? '';
        $this->cir_hora_fin_anestesia = $cirugia->hora_fin_anestesia ?? '';
        $this->cir_hora_inicio_cirugia = $cirugia->hora_inicio_cirugia ?? '';
        $this->cir_hora_fin_cirugia = $cirugia->hora_fin_cirugia ?? '';
        $this->cir_diagnostico_operacion = $cirugia->diagnostico_operacion ?? '';
        $this->cir_cirugia_efectuada = $cirugia->cirugia_efectuada ?? '';
        $this->cir_tipo_cirugia = $cirugia->tipo_cirugia ?? 'Media';
        $this->cir_tipo_anestesia = $cirugia->tipo_anestesia ?? '';
        $this->cir_analgesia = $cirugia->analgesia ?? '';

        $this->cir_cirujano_id = $cirugia->cirujano_id;
        $this->cir_primer_ayudante_id = $cirugia->primer_ayudante_id;
        $this->cir_segundo_ayudante_id = $cirugia->segundo_ayudante_id;
        $this->cir_anestesiologo_id = $cirugia->anestesiologo_id;
        $this->cir_instrumentador_id = $cirugia->instrumentador_id;
        $this->cir_circulante_id = $cirugia->circulante_id;

        $this->cir_torre_laparoscopia = (bool) $cirugia->torre_laparoscopia;
        $this->cir_bomba_infusion = (bool) $cirugia->bomba_infusion;
        $this->cir_maquina_aspiracion = (bool) $cirugia->maquina_aspiracion;
        $this->cir_material_importadora = $cirugia->material_importadora ?? '';
        $this->cir_intensificador_imagenes = (bool) $cirugia->intensificador_imagenes;
        $this->cir_maquina_anestesiologia = (bool) $cirugia->maquina_anestesiologia;
        $this->cir_monitor_multiparametrico = (bool) $cirugia->monitor_multiparametrico;
        $this->cir_ecografo = (bool) $cirugia->ecografo;
        $this->cir_rayos_x = (bool) $cirugia->rayos_x;
        $this->cir_oxigeno_central = (bool) $cirugia->oxigeno_central;
        $this->cir_electrobisturi = (bool) $cirugia->electrobisturi;
        $this->cir_patologia = $cirugia->patologia ?? '';
        $this->cir_otros_equipos = $cirugia->otros_equipos ?? '';
        $this->cir_observaciones = $cirugia->observaciones ?? '';

        // Cargar medicamentos de la receta de quirófano
        $this->cola_medicamentos_cirugia = [];
        if ($cirugia->receta) {
            foreach ($cirugia->receta->detalles as $det) {
                $this->cola_medicamentos_cirugia[] = [
                    'producto_id' => $det->producto_id,
                    'producto_nombre' => $det->producto->nombre ?? 'Medicamento/Insumo',
                    'seccion_id' => $det->seccion_id,
                    'seccion_nombre' => $det->seccion?->nombre ?? 'Sin sección',
                    'area' => $det->area_quirurgica ?? 'Anestesiología',
                    'cantidad' => $det->cantidad,
                    'costo_unitario' => (float) ($det->costo_unitario ?? $det->producto?->ultimo_precio_venta ?? 0),
                    'indicaciones' => $det->indicaciones ?? '',
                ];
            }
        }

        $quirofanoSec = Seccion::where('nombre', 'like', '%Quirofano%')->first();
        $this->cir_medicamento_seccion_id = $quirofanoSec?->id ?? Seccion::first()?->id;
        $this->cir_medicamento_producto_id = null;
        $this->buscarMedicamentoCirugia = '';
        $this->cir_medicamento_area = 'Anestesiología';
        $this->cir_medicamento_cantidad = 1;
        $this->cir_medicamento_costo_unitario = '0.00';
        $this->cir_medicamento_indicaciones = '';

        $this->modalCirugiaOpen = true;
    }

    public function updatedCirEsConvenio(bool $value): void
    {
        if ($value) {
            $this->cir_es_institucional = false;
            if (empty($this->cir_convenio_nombre)) {
                $institucionPaciente = $this->proforma->paciente->institucion
                    ?? ($this->proforma->paciente->institucion_id ? Institucion::find($this->proforma->paciente->institucion_id) : null);
                $this->cir_convenio_nombre = $institucionPaciente?->nombre ?? '';
            }
        } else {
            $this->cir_es_institucional = true;
            $this->cir_convenio_nombre = '';
        }
    }

    public function updatedCirEsInstitucional(bool $value): void
    {
        if ($value) {
            $this->cir_es_convenio = false;
            $this->cir_convenio_nombre = '';
        } else {
            $this->cir_es_convenio = true;
            if (empty($this->cir_convenio_nombre)) {
                $institucionPaciente = $this->proforma->paciente->institucion
                    ?? ($this->proforma->paciente->institucion_id ? Institucion::find($this->proforma->paciente->institucion_id) : null);
                $this->cir_convenio_nombre = $institucionPaciente?->nombre ?? '';
            }
        }
    }

    public function cerrarModalCirugia(): void
    {
        $this->modalCirugiaOpen = false;
        $this->cirugia_id_en_edicion = null;
        $this->cola_medicamentos_cirugia = [];
    }

    public function seleccionarMedicamentoCirugia(int $productoId): void
    {
        $producto = Producto::with('lotes')->find($productoId);
        if (! $producto) {
            return;
        }

        $this->cir_medicamento_producto_id = $producto->id;
        $this->buscarMedicamentoCirugia = $producto->nombre;

        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()?->sucursal_id;
        $loteConStock = $producto->lotes
            ->where('cantidad_actual', '>', 0)
            ->when($sucursalId, fn ($lotes) => $lotes->where('sucursal_id', $sucursalId))
            ->sortBy('fecha_vencimiento')
            ->first();

        $precio = (float) ($loteConStock?->precio_venta ?? $producto->ultimo_precio_venta ?? 0);
        $this->cir_medicamento_costo_unitario = number_format($precio, 2, '.', '');
    }

    public function agregarMedicamentoACirugia(): void
    {
        if (! $this->cir_medicamento_producto_id) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Seleccione un Medicamento o Insumo',
                'text' => 'Debe buscar y seleccionar un producto del catálogo para añadirlo al descargo.',
            ]);

            return;
        }

        if ($this->cir_medicamento_cantidad <= 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Cantidad Inválida',
                'text' => 'La cantidad debe ser mayor a 0.',
            ]);

            return;
        }

        $producto = Producto::with('lotes')->find($this->cir_medicamento_producto_id);
        if (! $producto) {
            return;
        }

        // Control de stock referencial
        $stockTotal = $producto->lotes->where('cantidad_actual', '>', 0)->sum('cantidad_actual');
        if ($stockTotal <= 0) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Sin Stock Disponible',
                'text' => "El producto '{$producto->nombre}' no cuenta con stock disponible en farmacia (>0). Seleccione una alternativa con existencias.",
            ]);

            return;
        }

        $seccion = Seccion::find($this->cir_medicamento_seccion_id);

        $precioUnitario = (float) $this->cir_medicamento_costo_unitario;
        if ($precioUnitario <= 0) {
            $sucursalId = $this->proforma->sucursal_id ?? Auth::user()?->sucursal_id;
            $loteConStock = $producto->lotes
                ->where('cantidad_actual', '>', 0)
                ->when($sucursalId, fn ($lotes) => $lotes->where('sucursal_id', $sucursalId))
                ->sortBy('fecha_vencimiento')
                ->first();
            $precioUnitario = (float) ($loteConStock?->precio_venta ?? $producto->ultimo_precio_venta ?? 0);
        }

        $this->cola_medicamentos_cirugia[] = [
            'producto_id' => $producto->id,
            'producto_nombre' => $producto->nombre,
            'seccion_id' => $seccion?->id,
            'seccion_nombre' => $seccion?->nombre ?? 'Sin sección',
            'area' => $this->cir_medicamento_area,
            'cantidad' => (int) $this->cir_medicamento_cantidad,
            'costo_unitario' => $precioUnitario,
            'indicaciones' => trim($this->cir_medicamento_indicaciones),
        ];

        // Limpiar formulario de adición
        $this->cir_medicamento_producto_id = null;
        $this->buscarMedicamentoCirugia = '';
        $this->cir_medicamento_cantidad = 1;
        $this->cir_medicamento_costo_unitario = '0.00';
        $this->cir_medicamento_indicaciones = '';
    }

    public function quitarMedicamentoDeCirugia(int $index): void
    {
        if (isset($this->cola_medicamentos_cirugia[$index])) {
            unset($this->cola_medicamentos_cirugia[$index]);
            $this->cola_medicamentos_cirugia = array_values($this->cola_medicamentos_cirugia);
        }
    }

    public function guardarCirugia(): void
    {
        if (! $this->asegurarProformaModificable('No se pueden registrar o modificar cirugías en una proforma liquidada.')) {
            return;
        }

        $this->validate([
            'cir_numero' => ['nullable', 'string', 'max:50'],
            'cir_fecha' => ['required', 'date'],
            'cir_especialidad' => ['nullable', 'string', 'max:100'],
            'cir_sala' => ['nullable', 'string', 'max:50'],
            'cir_cama' => ['nullable', 'string', 'max:50'],
            'cir_cirugia_efectuada' => ['nullable', 'string', 'max:1000'],
            'cir_diagnostico_operacion' => ['nullable', 'string', 'max:1000'],
            'cir_tipo_cirugia' => ['nullable', 'string', 'max:50'],
            'cir_tipo_anestesia' => ['nullable', 'string', 'max:100'],
            'cir_analgesia' => ['nullable', 'string', 'max:150'],
            'cir_cirujano_id' => ['nullable', 'exists:users,id'],
            'cir_primer_ayudante_id' => ['nullable', 'exists:users,id'],
            'cir_segundo_ayudante_id' => ['nullable', 'exists:users,id'],
            'cir_anestesiologo_id' => ['nullable', 'exists:users,id'],
            'cir_instrumentador_id' => ['nullable', 'exists:users,id'],
            'cir_circulante_id' => ['nullable', 'exists:users,id'],
        ]);

        DB::transaction(function () {
            $cirugia = Cirugia::updateOrCreate(
                ['id' => $this->cirugia_id_en_edicion],
                [
                    'proforma_id' => $this->proforma->id,
                    'numero' => $this->cir_numero ?: 'CQ-'.str_pad($this->proforma->id, 4, '0', STR_PAD_LEFT).'-'.now()->format('His'),
                    'es_institucional' => $this->cir_es_institucional,
                    'es_convenio' => $this->cir_es_convenio,
                    'convenio_nombre' => $this->cir_es_convenio ? $this->cir_convenio_nombre : null,
                    'fecha' => $this->cir_fecha,
                    'especialidad' => $this->cir_especialidad ?: null,
                    'sala' => $this->cir_sala ?: null,
                    'cama' => $this->cir_cama ?: null,
                    'hora_ingreso_quirofano' => $this->cir_hora_ingreso_quirofano ?: null,
                    'hora_salida_quirofano' => $this->cir_hora_salida_quirofano ?: null,
                    'hora_inicio_anestesia' => $this->cir_hora_inicio_anestesia ?: null,
                    'hora_fin_anestesia' => $this->cir_hora_fin_anestesia ?: null,
                    'hora_inicio_cirugia' => $this->cir_hora_inicio_cirugia ?: null,
                    'hora_fin_cirugia' => $this->cir_hora_fin_cirugia ?: null,
                    'diagnostico_operacion' => $this->cir_diagnostico_operacion ?: null,
                    'cirugia_efectuada' => $this->cir_cirugia_efectuada ?: null,
                    'tipo_cirugia' => $this->cir_tipo_cirugia ?: 'Media',
                    'tipo_anestesia' => $this->cir_tipo_anestesia ?: null,
                    'analgesia' => $this->cir_analgesia ?: null,
                    'cirujano_id' => $this->cir_cirujano_id ?: null,
                    'primer_ayudante_id' => $this->cir_primer_ayudante_id ?: null,
                    'segundo_ayudante_id' => $this->cir_segundo_ayudante_id ?: null,
                    'anestesiologo_id' => $this->cir_anestesiologo_id ?: null,
                    'instrumentador_id' => $this->cir_instrumentador_id ?: null,
                    'circulante_id' => $this->cir_circulante_id ?: null,
                    'torre_laparoscopia' => $this->cir_torre_laparoscopia,
                    'bomba_infusion' => $this->cir_bomba_infusion,
                    'maquina_aspiracion' => $this->cir_maquina_aspiracion,
                    'material_importadora' => $this->cir_material_importadora ?: null,
                    'intensificador_imagenes' => $this->cir_intensificador_imagenes,
                    'maquina_anestesiologia' => $this->cir_maquina_anestesiologia,
                    'monitor_multiparametrico' => $this->cir_monitor_multiparametrico,
                    'ecografo' => $this->cir_ecografo,
                    'rayos_x' => $this->cir_rayos_x,
                    'oxigeno_central' => $this->cir_oxigeno_central,
                    'electrobisturi' => $this->cir_electrobisturi,
                    'patologia' => $this->cir_patologia ?: null,
                    'otros_equipos' => $this->cir_otros_equipos ?: null,
                    'observaciones' => $this->cir_observaciones ?: null,
                    'usuario_modificador_id' => Auth::id(),
                ]
            );

            if (! $cirugia->usuario_creador_id) {
                $cirugia->update(['usuario_creador_id' => Auth::id()]);
            }

            // Gestionar Receta de Descargo de Quirófano vinculada a la cirugía
            $recetaCirugia = Receta::updateOrCreate(
                [
                    'proforma_id' => $this->proforma->id,
                    'cirugia_id' => $cirugia->id,
                ],
                [
                    'user_id' => $this->cir_cirujano_id ?? Auth::id(),
                    'tipo' => 'Quirófano',
                    'activo' => true,
                    'observaciones' => "Descargo de Quirófano: {$cirugia->cirugia_efectuada} (N° {$cirugia->numero})",
                    'usuario_creador_id' => Auth::id(),
                ]
            );

            // Reemplazar los detalles de medicamentos e insumos de la cirugía
            $recetaCirugia->detalles()->where('despachado', false)->delete();

            foreach ($this->cola_medicamentos_cirugia as $item) {
                RecetaDetalle::create([
                    'receta_id' => $recetaCirugia->id,
                    'producto_id' => $item['producto_id'],
                    'seccion_id' => $item['seccion_id'] ?: null,
                    'area_quirurgica' => $item['area'] ?? 'Anestesiología',
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'] ?? 0.00,
                    'indicaciones' => $item['indicaciones'] ?: null,
                    'despachado' => false,
                    'usuario_creador_id' => Auth::id(),
                ]);
            }
        });

        $this->proforma->load([
            'cirugias.cirujano',
            'cirugias.primerAyudante',
            'cirugias.segundoAyudante',
            'cirugias.anestesiologo',
            'cirugias.instrumentador',
            'cirugias.circulante',
            'cirugias.receta.detalles.producto',
            'cirugias.receta.detalles.seccion',
        ]);

        $this->cerrarModalCirugia();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Descargo de Quirófano Guardado',
            'text' => 'La cirugía y los medicamentos e insumos de quirófano han sido registrados correctamente.',
        ]);
    }

    public function eliminarCirugia(int $cirugiaId): void
    {
        if (! $this->autorizarAccion('proformas.cirugia.eliminar', 'No cuenta con autorización para eliminar cirugías de la proforma.')) {
            return;
        }

        if (! $this->asegurarProformaModificable('No se pueden eliminar cirugías en una proforma liquidada.')) {
            return;
        }

        $cirugia = Cirugia::with('receta.detalles')->findOrFail($cirugiaId);

        DB::transaction(function () use ($cirugia) {
            if ($cirugia->receta) {
                $cirugia->receta->detalles()->delete();
                $cirugia->receta->delete();
            }
            $cirugia->delete();
        });

        $this->proforma->load('cirugias');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Cirugía Eliminada',
            'text' => 'El registro del descargo de quirófano ha sido eliminado del expediente.',
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
    // 3.A. HOJA DE EVOLUCIÓN CLÍNICA
    // =========================================================================
    public function abrirModalEvolucion(?int $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            if (! $this->autorizarAccion('proformas.evolucion.editar', 'No cuenta con autorización para editar notas de evolución médica.')) {
                return;
            }
            $item = Evolucion::where('proforma_id', $this->proforma->id)->findOrFail($id);
            $this->evolucion_id = $item->id;
            $this->evolucion_fecha_hora = $item->fecha_hora ? $item->fecha_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->evolucion_descripcion = $item->descripcion ?? '';
        } else {
            if (! $this->autorizarAccion('proformas.evolucion.crear', 'No cuenta con autorización para registrar notas de evolución médica.')) {
                return;
            }
            if (! $this->asegurarProformaModificable()) {
                return;
            }
            $this->evolucion_id = null;
            $this->evolucion_fecha_hora = now()->format('Y-m-d\TH:i');
            $this->evolucion_descripcion = '';
        }
        $this->modalEvolucionOpen = true;
    }

    public function cerrarModalEvolucion(): void
    {
        $this->modalEvolucionOpen = false;
        $this->evolucion_id = null;
        $this->evolucion_fecha_hora = '';
        $this->evolucion_descripcion = '';
        $this->resetValidation();
    }

    public function guardarEvolucion(): void
    {
        $permisoRequerido = $this->evolucion_id ? 'proformas.evolucion.editar' : 'proformas.evolucion.crear';
        if (! $this->autorizarAccion($permisoRequerido, 'No cuenta con autorización para guardar esta nota de evolución médica.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'evolucion_fecha_hora' => 'required|date',
            'evolucion_descripcion' => 'required|string',
        ], [
            'evolucion_fecha_hora.required' => 'La fecha y hora de la evolución es obligatoria.',
            'evolucion_descripcion.required' => 'La descripción clínica no puede estar vacía.',
        ]);

        if ($this->evolucion_id) {
            $item = Evolucion::where('proforma_id', $this->proforma->id)->findOrFail($this->evolucion_id);
            $item->update([
                'fecha_hora' => $this->evolucion_fecha_hora,
                'descripcion' => $this->evolucion_descripcion,
                'usuario_modificador_id' => Auth::id(),
            ]);
            $mensaje = 'Nota de evolución actualizada con éxito.';
        } else {
            $this->proforma->evoluciones()->create([
                'fecha_hora' => $this->evolucion_fecha_hora,
                'descripcion' => $this->evolucion_descripcion,
                'usuario_creador_id' => Auth::id(),
            ]);
            $mensaje = 'Nota de evolución registrada con éxito.';
        }

        $this->cerrarModalEvolucion();
        $this->proforma->load('evoluciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Evolución Guardada!',
            'text' => $mensaje,
        ]);
    }

    public function eliminarEvolucion(int $id): void
    {
        if (! $this->autorizarAccion('proformas.evolucion.eliminar', 'No cuenta con autorización para eliminar notas de evolución médica.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $item = Evolucion::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $item->update(['usuario_eliminador_id' => Auth::id()]);
        $item->delete();

        $this->proforma->load('evoluciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro Eliminado',
            'text' => 'La nota de evolución fue eliminada correctamente.',
        ]);
    }

    // =========================================================================
    // 3.B. HOJA DE INDICACIONES MÉDICAS
    // =========================================================================
    public function abrirModalIndicacion(?int $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            if (! $this->autorizarAccion('proformas.indicacion.editar', 'No cuenta con autorización para editar indicaciones médicas.')) {
                return;
            }
            $item = Indicacion::where('proforma_id', $this->proforma->id)->findOrFail($id);
            $this->indicacion_id = $item->id;
            $this->indicacion_fecha_hora = $item->fecha_hora ? $item->fecha_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->indicacion_descripcion = $item->descripcion ?? '';
        } else {
            if (! $this->autorizarAccion('proformas.indicacion.crear', 'No cuenta con autorización para registrar indicaciones médicas.')) {
                return;
            }
            if (! $this->asegurarProformaModificable()) {
                return;
            }
            $this->indicacion_id = null;
            $this->indicacion_fecha_hora = now()->format('Y-m-d\TH:i');
            $this->indicacion_descripcion = '';
        }
        $this->modalIndicacionOpen = true;
    }

    public function cerrarModalIndicacion(): void
    {
        $this->modalIndicacionOpen = false;
        $this->indicacion_id = null;
        $this->indicacion_fecha_hora = '';
        $this->indicacion_descripcion = '';
        $this->resetValidation();
    }

    public function guardarIndicacion(): void
    {
        $permisoRequerido = $this->indicacion_id ? 'proformas.indicacion.editar' : 'proformas.indicacion.crear';
        if (! $this->autorizarAccion($permisoRequerido, 'No cuenta con autorización para guardar esta indicación médica.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'indicacion_fecha_hora' => 'required|date',
            'indicacion_descripcion' => 'required|string',
        ], [
            'indicacion_fecha_hora.required' => 'La fecha y hora de la indicación es obligatoria.',
            'indicacion_descripcion.required' => 'El contenido de la indicación no puede estar vacío.',
        ]);

        if ($this->indicacion_id) {
            $item = Indicacion::where('proforma_id', $this->proforma->id)->findOrFail($this->indicacion_id);
            $item->update([
                'fecha_hora' => $this->indicacion_fecha_hora,
                'descripcion' => $this->indicacion_descripcion,
                'usuario_modificador_id' => Auth::id(),
            ]);
            $mensaje = 'Indicación médica actualizada con éxito.';
        } else {
            $this->proforma->indicaciones()->create([
                'fecha_hora' => $this->indicacion_fecha_hora,
                'descripcion' => $this->indicacion_descripcion,
                'usuario_creador_id' => Auth::id(),
            ]);
            $mensaje = 'Indicación médica registrada con éxito.';
        }

        $this->cerrarModalIndicacion();
        $this->proforma->load('indicaciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Indicación Guardada!',
            'text' => $mensaje,
        ]);
    }

    public function eliminarIndicacion(int $id): void
    {
        if (! $this->autorizarAccion('proformas.indicacion.eliminar', 'No cuenta con autorización para eliminar indicaciones médicas.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $item = Indicacion::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $item->update(['usuario_eliminador_id' => Auth::id()]);
        $item->delete();

        $this->proforma->load('indicaciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro Eliminado',
            'text' => 'La indicación médica fue eliminada correctamente.',
        ]);
    }

    // =========================================================================
    // 3.C. HOJA DE INTERNACIÓN
    // =========================================================================
    public function abrirModalInternacion(?int $id = null): void
    {
        $this->resetValidation();
        if ($id) {
            if (! $this->autorizarAccion('proformas.internacion.editar', 'No cuenta con autorización para editar registros de internación.')) {
                return;
            }
            $item = Internacion::where('proforma_id', $this->proforma->id)->findOrFail($id);
            $this->internacion_id = $item->id;
            $this->internacion_fecha_hora = $item->fecha_hora ? $item->fecha_hora->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i');
            $this->internacion_descripcion = $item->descripcion ?? '';
        } else {
            if (! $this->autorizarAccion('proformas.internacion.crear', 'No cuenta con autorización para registrar notas de internación.')) {
                return;
            }
            if (! $this->asegurarProformaModificable()) {
                return;
            }
            $this->internacion_id = null;
            $this->internacion_fecha_hora = now()->format('Y-m-d\TH:i');
            $this->internacion_descripcion = '';
        }
        $this->modalInternacionOpen = true;
    }

    public function cerrarModalInternacion(): void
    {
        $this->modalInternacionOpen = false;
        $this->internacion_id = null;
        $this->internacion_fecha_hora = '';
        $this->internacion_descripcion = '';
        $this->resetValidation();
    }

    public function guardarInternacion(): void
    {
        $permisoRequerido = $this->internacion_id ? 'proformas.internacion.editar' : 'proformas.internacion.crear';
        if (! $this->autorizarAccion($permisoRequerido, 'No cuenta con autorización para guardar este registro de internación.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $this->validate([
            'internacion_fecha_hora' => 'required|date',
            'internacion_descripcion' => 'required|string',
        ], [
            'internacion_fecha_hora.required' => 'La fecha y hora del registro es obligatoria.',
            'internacion_descripcion.required' => 'El detalle de internación no puede estar vacío.',
        ]);

        if ($this->internacion_id) {
            $item = Internacion::where('proforma_id', $this->proforma->id)->findOrFail($this->internacion_id);
            $item->update([
                'fecha_hora' => $this->internacion_fecha_hora,
                'descripcion' => $this->internacion_descripcion,
                'usuario_modificador_id' => Auth::id(),
            ]);
            $mensaje = 'Registro de internación actualizado con éxito.';
        } else {
            $this->proforma->internaciones()->create([
                'fecha_hora' => $this->internacion_fecha_hora,
                'descripcion' => $this->internacion_descripcion,
                'usuario_creador_id' => Auth::id(),
            ]);
            $mensaje = 'Registro de internación guardado con éxito.';
        }

        $this->cerrarModalInternacion();
        $this->proforma->load('internaciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Internación Guardada!',
            'text' => $mensaje,
        ]);
    }

    public function eliminarInternacion(int $id): void
    {
        if (! $this->autorizarAccion('proformas.internacion.eliminar', 'No cuenta con autorización para eliminar registros de internación.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        $item = Internacion::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $item->update(['usuario_eliminador_id' => Auth::id()]);
        $item->delete();

        $this->proforma->load('internaciones.usuarioCreador');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Registro Eliminado',
            'text' => 'El registro de internación fue eliminado correctamente.',
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
        $this->receta_medicamentos = [];
        $this->limpiarMedicamentoSeleccionadoReceta();
        $this->modalRecetaOpen = true;
    }

    public function cerrarModalReceta(): void
    {
        $this->modalRecetaOpen = false;
        $this->limpiarMedicamentoSeleccionadoReceta();
        $this->receta_medicamentos = [];
        $this->resetValidation();
    }

    /**
     * Calcula las existencias disponibles de un producto en la sucursal de la proforma.
     */
    public function obtenerStockDisponibleProducto(int $productoId): int
    {
        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()?->sucursal_id;

        $query = Lote::where('producto_id', $productoId)
            ->where('cantidad_actual', '>', 0);

        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        return (int) $query->sum('cantidad_actual');
    }

    /**
     * Limpia los campos de entrada del medicamento para la receta.
     */
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

    /**
     * Selecciona un medicamento tras validar estrictamente que cuente con stock disponible (> 0).
     */
    public function seleccionarMedicamentoReceta(int $arg1, ?int $arg2 = null): void
    {
        $productoId = $arg2 ?? $arg1;
        $producto = Producto::with('marca')->find($productoId);

        if (! $producto) {
            return;
        }

        $stock = $this->obtenerStockDisponibleProducto($producto->id);

        if ($stock <= 0) {
            $this->limpiarMedicamentoSeleccionadoReceta();
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Producto Sin Stock',
                'text' => "El medicamento '{$producto->nombre}' no cuenta con existencias en farmacia (Stock: 0). Por favor, seleccione otro fármaco disponible.",
            ]);

            return;
        }

        $this->receta_nuevo_producto_id = $producto->id;
        $this->receta_nuevo_producto_nombre = $producto->nombre;
        $this->receta_nuevo_unidad_medida = $producto->unidad_medida ?? 'Unidad';
        $this->receta_nuevo_marca_nombre = $producto->marca?->nombre ?? '';
        $this->receta_nuevo_stock_disponible = $stock;
        $this->buscarMedicamentoReceta = '';

        // Compatibilidad si se invoca con formato posicional index
        if ($arg2 !== null && isset($this->receta_medicamentos[$arg1])) {
            $this->receta_medicamentos[$arg1]['producto_id'] = $producto->id;
            $this->receta_medicamentos[$arg1]['producto_nombre'] = $producto->nombre;
            $this->receta_medicamentos[$arg1]['unidad_medida'] = $producto->unidad_medida ?? 'Unidad';
        }

        $this->resetValidation([
            'receta_nuevo_producto_id',
            'receta_nuevo_cantidad',
            'receta_nuevo_indicaciones',
        ]);
    }

    public function limpiarMedicamentoReceta(?int $index = null): void
    {
        $this->limpiarMedicamentoSeleccionadoReceta();

        if ($index !== null && isset($this->receta_medicamentos[$index])) {
            $this->receta_medicamentos[$index]['producto_id'] = '';
            $this->receta_medicamentos[$index]['producto_nombre'] = '';
            $this->receta_medicamentos[$index]['unidad_medida'] = '';
        }
    }

    /**
     * Agrega el medicamento seleccionado a la tabla de la receta tras revalidar stock.
     */
    public function agregarMedicamentoAReceta(): void
    {
        if (! $this->receta_nuevo_producto_id) {
            $this->dispatch('swal', [
                'icon' => 'warning',
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
            'receta_nuevo_indicaciones.required' => 'Indique la posología y horarios (ej. 1 comp cada 8 hrs por 5 días).',
        ]);

        $stock = $this->obtenerStockDisponibleProducto((int) $this->receta_nuevo_producto_id);

        if ($stock <= 0) {
            $prodNombre = $this->receta_nuevo_producto_nombre;
            $this->limpiarMedicamentoSeleccionadoReceta();
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Producto Sin Stock',
                'text' => "El medicamento '{$prodNombre}' no cuenta con stock disponible en farmacia. Por favor, seleccione otro producto.",
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

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Medicamento Agregado',
            'text' => 'El medicamento se agregó a la lista de la receta.',
            'toast' => true,
        ]);
    }

    public function removerMedicamentoReceta(int $index): void
    {
        if (isset($this->receta_medicamentos[$index])) {
            unset($this->receta_medicamentos[$index]);
            $this->receta_medicamentos = array_values($this->receta_medicamentos);
        }
    }

    public function agregarFilaMedicamento(): void
    {
        $this->agregarMedicamentoAReceta();
    }

    public function eliminarFilaMedicamento(int $index): void
    {
        $this->removerMedicamentoReceta($index);
    }

    public function prescribirReceta(): void
    {
        if (! $this->autorizarAccion('emitir-receta', 'No cuenta con autorización para emitir recetas médicas.')) {
            return;
        }

        if (! $this->asegurarProformaModificable()) {
            return;
        }

        // Si el usuario seleccionó un medicamento y rellenó los campos pero no pulsó "Agregar", lo incorporamos automáticamente
        if ($this->receta_nuevo_producto_id && (int) $this->receta_nuevo_cantidad > 0 && ! empty(trim($this->receta_nuevo_indicaciones))) {
            $stock = $this->obtenerStockDisponibleProducto((int) $this->receta_nuevo_producto_id);
            if ($stock > 0) {
                $this->agregarMedicamentoAReceta();
            }
        }

        $this->validate([
            'receta_medico_id' => ['required', 'exists:users,id'],
            'receta_observaciones' => ['nullable', 'string', 'max:1000'],
            'receta_medicamentos' => ['required', 'array', 'min:1'],
            'receta_medicamentos.*.producto_id' => ['required', 'exists:productos,id'],
            'receta_medicamentos.*.cantidad' => ['required', 'integer', 'min:1'],
            'receta_medicamentos.*.indicaciones' => ['required', 'string', 'min:2', 'max:255'],
        ], [
            'receta_medico_id.required' => 'Debe seleccionar un médico prescriptor.',
            'receta_medicamentos.required' => 'Debe agregar al menos un medicamento a la receta.',
            'receta_medicamentos.min' => 'Debe agregar al menos un medicamento a la receta.',
            'receta_medicamentos.*.producto_id.required' => 'Seleccione un fármaco válido en cada línea.',
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
        $this->proforma->load([
            'recetaActiva.detalles.producto',
            'recetaActiva.doctor',
            'recetas.detalles.producto',
            'recetas.doctor',
        ]);
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
                // Unidades despachadas netas (salidas menos devoluciones)
                $salidas = (int) MovimientoInventario::where('receta_id', $this->proforma->recetaActiva->id)
                    ->where('producto_id', $det->producto_id)
                    ->where('tipo_movimiento', 'Salida Receta')
                    ->sum('cantidad');

                $devoluciones = (int) MovimientoInventario::where('receta_id', $this->proforma->recetaActiva->id)
                    ->where('producto_id', $det->producto_id)
                    ->whereIn('tipo_movimiento', ['Devolución', 'Devolucion'])
                    ->sum('cantidad');

                $despachadasPrevias = max(0, $salidas - $devoluciones);
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
    // 7. HONORARIOS Y PAGOS MÉDICOS CON CONTROL DE CAJA Y SALDO
    // =========================================================================

    /**
     * Obtiene el listado de cajas abiertas activas en la sucursal de la proforma.
     *
     * @return Collection<int, Caja>
     */
    public function getCajasAbiertasDisponibles()
    {
        return Caja::with('user')
            ->where('sucursal_id', $this->proforma->sucursal_id)
            ->where('estado', 'Abierta')
            ->latest('fecha_apertura')
            ->get();
    }

    public function getCajasAbiertasProperty()
    {
        return $this->getCajasAbiertasDisponibles();
    }

    /**
     * Calcula en tiempo real el saldo disponible en la caja seleccionada según el tipo de pago.
     */
    public function getSaldoCajaSeleccionadaProperty(): float
    {
        if (! $this->pago_medico_caja_id) {
            return 0.00;
        }

        $caja = Caja::find($this->pago_medico_caja_id);
        if (! $caja || ! $caja->isAbierta()) {
            return 0.00;
        }

        $saldo = match ($this->pago_medico_tipo_pago) {
            'Efectivo' => $caja->saldoEsperadoEfectivo(),
            'QR' => $caja->saldoEsperadoQr(),
            'Transferencia' => $caja->saldoEsperadoTransferencia(),
            default => $caja->saldoEsperadoEfectivo(),
        };

        if ($this->editando_pago_medico_id) {
            $pagoPrevio = ProformaPagoMedico::find($this->editando_pago_medico_id);
            if ($pagoPrevio && $pagoPrevio->caja_id === $caja->id && $pagoPrevio->tipo_pago === $this->pago_medico_tipo_pago) {
                $saldo += (float) $pagoPrevio->monto;
            }
        }

        return max(0.00, (float) $saldo);
    }

    public function abrirModalPagoMedico(?int $medicoId = null): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.gestionar', 'No cuenta con autorización para gestionar honorarios médicos.')) {
            return;
        }

        $cajasAbiertas = $this->getCajasAbiertasDisponibles();
        if ($cajasAbiertas->isEmpty()) {
            $this->dispatch('swal', [
                'icon' => 'warning',
                'title' => 'Sin Caja Abierta',
                'text' => 'No existe ninguna caja abierta activa en esta sucursal para realizar el desembolso del honorario médico. Debe aperturar una caja previamente en el módulo de Caja.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->editando_pago_medico_id = null;
        $this->pago_medico_id = $medicoId;
        $this->pago_medico_monto = '';
        $this->pago_medico_observaciones = '';
        $this->pago_medico_fecha = now()->toDateString();
        $this->pago_medico_tipo_pago = 'Efectivo';
        $this->pago_medico_numero_referencia = '';

        // Preseleccionar la caja del usuario logueado si tiene una abierta; si no, la primera disponible
        $cajaUsuario = $cajasAbiertas->firstWhere('user_id', Auth::id());
        $this->pago_medico_caja_id = $cajaUsuario?->id ?? $cajasAbiertas->first()?->id;

        $this->modalPagoMedicoOpen = true;
    }

    public function cerrarModalPagoMedico(): void
    {
        $this->modalPagoMedicoOpen = false;
        $this->resetValidation();
        $this->reset([
            'pago_medico_id',
            'pago_medico_caja_id',
            'pago_medico_tipo_pago',
            'pago_medico_numero_referencia',
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
        $pago = ProformaPagoMedico::with('caja')->where('proforma_id', $this->proforma->id)->findOrFail($pagoId);

        $this->editando_pago_medico_id = $pago->id;
        $this->pago_medico_id = $pago->medico_id;
        $this->pago_medico_caja_id = $pago->caja_id ?? $this->getCajasAbiertasDisponibles()->first()?->id;
        $this->pago_medico_tipo_pago = $pago->tipo_pago ?? 'Efectivo';
        $this->pago_medico_numero_referencia = $pago->numero_referencia ?? '';
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
            'pago_medico_caja_id' => ['required', 'exists:cajas,id'],
            'pago_medico_tipo_pago' => ['required', 'in:Efectivo,QR,Transferencia'],
            'pago_medico_monto' => ['required', 'numeric', 'min:0.01'],
            'pago_medico_fecha' => ['required', 'date'],
            'pago_medico_numero_referencia' => ['nullable', 'string', 'max:100'],
            'pago_medico_observaciones' => ['nullable', 'string', 'max:1000'],
        ], [
            'pago_medico_id.required' => 'Debe seleccionar el médico asignado.',
            'pago_medico_caja_id.required' => 'Debe seleccionar la caja abierta de donde se desembolsará el honorario.',
            'pago_medico_tipo_pago.required' => 'Debe seleccionar el canal de pago / desembolso.',
            'pago_medico_monto.required' => 'Debe indicar el monto de honorarios a pagar.',
            'pago_medico_monto.min' => 'El monto debe ser mayor a 0.',
            'pago_medico_fecha.required' => 'La fecha de liquidación es requerida.',
        ]);

        if ($this->pago_medico_tipo_pago === 'Transferencia' && empty(trim($this->pago_medico_numero_referencia))) {
            $this->addError('pago_medico_numero_referencia', 'El número de referencia o comprobante bancario es obligatorio para transferencias.');

            return;
        }

        $caja = Caja::where('id', $this->pago_medico_caja_id)
            ->where('estado', 'Abierta')
            ->first();

        if (! $caja) {
            $this->addError('pago_medico_caja_id', 'La caja seleccionada no existe o ya no se encuentra abierta.');
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Caja No Disponible',
                'text' => 'La caja seleccionada ya no se encuentra en estado Abierta.',
            ]);

            return;
        }

        $monto = (float) $this->pago_medico_monto;

        // Calcular saldo disponible en la caja según el método de pago
        $saldoDisponible = match ($this->pago_medico_tipo_pago) {
            'Efectivo' => $caja->saldoEsperadoEfectivo(),
            'QR' => $caja->saldoEsperadoQr(),
            'Transferencia' => $caja->saldoEsperadoTransferencia(),
            default => $caja->saldoEsperadoEfectivo(),
        };

        // Si estamos editando el mismo registro en la misma caja y canal, sumamos su monto previo al saldo disponible
        if ($this->editando_pago_medico_id) {
            $pagoPrevio = ProformaPagoMedico::find($this->editando_pago_medico_id);
            if ($pagoPrevio && $pagoPrevio->caja_id === $caja->id && $pagoPrevio->tipo_pago === $this->pago_medico_tipo_pago) {
                $saldoDisponible += (float) $pagoPrevio->monto;
            }
        }

        // Control estricto: el monto debe ser menor o igual al saldo disponible en caja
        if ($monto > $saldoDisponible) {
            $this->addError('pago_medico_monto', 'Saldo insuficiente en caja: La caja seleccionada solo dispone de Bs. '.number_format($saldoDisponible, 2)." en {$this->pago_medico_tipo_pago} para desembolsar este honorario.");
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Saldo Insuficiente en Caja',
                'text' => 'No es posible otorgar el honorario de Bs. '.number_format($monto, 2).' porque la caja seleccionada solo dispone de Bs. '.number_format($saldoDisponible, 2)." en {$this->pago_medico_tipo_pago}.",
            ]);

            return;
        }

        $medico = User::find($this->pago_medico_id);
        $nombreMedico = $medico ? $medico->nombre_completo : 'Médico';
        $conceptoEgreso = "Honorario Médico: Dr(a). {$nombreMedico} - Proforma #{$this->proforma->id}";
        $mensajeExito = '';

        DB::transaction(function () use ($caja, $monto, $conceptoEgreso, &$mensajeExito) {
            if ($this->editando_pago_medico_id) {
                $pagoMedico = ProformaPagoMedico::where('proforma_id', $this->proforma->id)->findOrFail($this->editando_pago_medico_id);

                // Actualizar o crear el registro en Pagos (Egreso Caja)
                if ($pagoMedico->pago_id && $pago = Pago::find($pagoMedico->pago_id)) {
                    $pago->update([
                        'caja_id' => $caja->id,
                        'tipo_pago' => $this->pago_medico_tipo_pago,
                        'concepto' => $conceptoEgreso,
                        'monto' => $monto,
                        'numero_referencia' => ! empty($this->pago_medico_numero_referencia) ? trim($this->pago_medico_numero_referencia) : null,
                    ]);
                } else {
                    $pago = Pago::create([
                        'caja_id' => $caja->id,
                        'proforma_id' => $this->proforma->id,
                        'tipo_movimiento' => 'Egreso Caja',
                        'categoria' => 'Honorario',
                        'tipo_pago' => $this->pago_medico_tipo_pago,
                        'concepto' => $conceptoEgreso,
                        'monto' => $monto,
                        'numero_referencia' => ! empty($this->pago_medico_numero_referencia) ? trim($this->pago_medico_numero_referencia) : null,
                        'user_id' => Auth::id(),
                    ]);
                    $pagoMedico->pago_id = $pago->id;
                }

                $pagoMedico->update([
                    'medico_id' => $this->pago_medico_id,
                    'caja_id' => $caja->id,
                    'pago_id' => $pagoMedico->pago_id,
                    'tipo_pago' => $this->pago_medico_tipo_pago,
                    'numero_referencia' => ! empty($this->pago_medico_numero_referencia) ? trim($this->pago_medico_numero_referencia) : null,
                    'monto' => $monto,
                    'observaciones' => $this->pago_medico_observaciones ? trim($this->pago_medico_observaciones) : null,
                    'fecha_pago' => $this->pago_medico_fecha,
                ]);

                $mensajeExito = 'El honorario médico y su egreso de caja fueron actualizados correctamente.';
            } else {
                // Crear egreso en caja
                $pago = Pago::create([
                    'caja_id' => $caja->id,
                    'proforma_id' => $this->proforma->id,
                    'tipo_movimiento' => 'Egreso Caja',
                    'categoria' => 'Honorario',
                    'tipo_pago' => $this->pago_medico_tipo_pago,
                    'concepto' => $conceptoEgreso,
                    'monto' => $monto,
                    'numero_referencia' => ! empty($this->pago_medico_numero_referencia) ? trim($this->pago_medico_numero_referencia) : null,
                    'user_id' => Auth::id(),
                ]);

                ProformaPagoMedico::create([
                    'proforma_id' => $this->proforma->id,
                    'caja_id' => $caja->id,
                    'pago_id' => $pago->id,
                    'medico_id' => $this->pago_medico_id,
                    'tipo_pago' => $this->pago_medico_tipo_pago,
                    'numero_referencia' => ! empty($this->pago_medico_numero_referencia) ? trim($this->pago_medico_numero_referencia) : null,
                    'monto' => $monto,
                    'observaciones' => $this->pago_medico_observaciones ? trim($this->pago_medico_observaciones) : null,
                    'fecha_pago' => $this->pago_medico_fecha,
                    'user_id' => Auth::id(),
                ]);

                $mensajeExito = 'El honorario médico fue registrado y asentado como salida de caja por Bs. '.number_format($monto, 2).' con éxito.';
            }
        });

        $this->proforma->load('pagosMedicos.medico.especialidad', 'pagosMedicos.user', 'pagosMedicos.caja.user', 'pagosMedicos.pago');
        $this->cerrarModalPagoMedico();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Honorario Registrado en Caja',
            'text' => $mensajeExito,
        ]);
    }

    public function eliminarPagoMedico(int $pagoId): void
    {
        if (! $this->autorizarAccion('proformas.honorarios.eliminar', 'No cuenta con autorización para eliminar pagos de honorarios médicos.')) {
            return;
        }

        DB::transaction(function () use ($pagoId) {
            $pagoMedico = ProformaPagoMedico::where('proforma_id', $this->proforma->id)->findOrFail($pagoId);

            if ($pagoMedico->pago_id) {
                Pago::where('id', $pagoMedico->pago_id)->delete();
            }

            $pagoMedico->delete();
        });

        $this->proforma->load('pagosMedicos.medico.especialidad', 'pagosMedicos.user', 'pagosMedicos.caja.user', 'pagosMedicos.pago');

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Honorario Revertido',
            'text' => 'El honorario médico y su egreso de caja asociado fueron revertidos con éxito.',
        ]);
    }

    public function render(): View
    {
        // Refrescar relaciones clave
        $this->proforma->load([
            'servicios.servicio.categoria',
            'historiaClinica.usuarioCreador',
            'epicrisis.usuarioCreador',
            'solicitudes.tipoSolicitud',
            'evoluciones.usuarioCreador',
            'indicaciones.usuarioCreador',
            'internaciones.usuarioCreador',
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

        // 2. Catálogo reactivo para Recetas (Búsqueda de medicamentos con existencias en sucursal)
        $sucursalId = $this->proforma->sucursal_id ?? Auth::user()?->sucursal_id;
        $sugerenciasMedicamentosReceta = collect();
        if ($this->modalRecetaOpen && ! $this->receta_nuevo_producto_id) {
            $term = trim($this->buscarMedicamentoReceta);
            if (! empty($term)) {
                $sugerenciasMedicamentosReceta = Producto::with([
                    'marca',
                    'lotes' => function ($q) use ($sucursalId) {
                        $q->when($sucursalId, fn ($sq) => $sq->where('sucursal_id', $sucursalId))
                            ->where('cantidad_actual', '>', 0);
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

        // 3. Catálogo reactivo para Consumos Extras (Filtro estricto por stock disponible en sucursal y sección)
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

        $personalClinica = User::where('activo', true)->orderBy('nombres')->get();

        $sugerenciasMedicamentosCirugia = collect();
        if ($this->modalCirugiaOpen && ! $this->cir_medicamento_producto_id) {
            $termCir = trim($this->buscarMedicamentoCirugia);
            if (! empty($termCir)) {
                $sugerenciasMedicamentosCirugia = Producto::with([
                    'marca',
                    'lotes' => function ($q) use ($sucursalId) {
                        $q->when($sucursalId, fn ($sq) => $sq->where('sucursal_id', $sucursalId))
                            ->where('cantidad_actual', '>', 0);
                    },
                ])
                    ->where(function ($q) use ($termCir) {
                        $q->where('nombre', 'like', "%{$termCir}%")
                            ->orWhere('descripcion', 'like', "%{$termCir}%");
                    })
                    ->orderBy('nombre')
                    ->take(10)
                    ->get();
            }
        }

        return view('livewire.proformas.proforma-detalle', [
            'tiposSolicitudes' => $tiposSolicitudes,
            'medicos' => $medicos,
            'personalClinica' => $personalClinica,
            'sugerenciasMedicamentosCirugia' => $sugerenciasMedicamentosCirugia,
            'cirugias' => $this->proforma->cirugias()->with(['cirujano', 'anestesiologo', 'primerAyudante', 'segundoAyudante', 'instrumentador', 'circulante', 'receta.detalles.producto', 'receta.detalles.seccion'])->latest('fecha')->get(),
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
            'cajasAbiertas' => $this->getCajasAbiertasDisponibles(),
            'evoluciones' => $this->proforma->evoluciones()->with('usuarioCreador')->orderBy('fecha_hora', 'desc')->get(),
            'indicaciones' => $this->proforma->indicaciones()->with('usuarioCreador')->orderBy('fecha_hora', 'desc')->get(),
            'internaciones' => $this->proforma->internaciones()->with('usuarioCreador')->orderBy('fecha_hora', 'desc')->get(),
        ]);
    }
}
