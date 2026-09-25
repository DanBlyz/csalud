<?php

namespace App\Livewire\Proformas;

use App\Models\ConsumoExtra;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaCalendario;
use App\Models\ProformaServicio;
use App\Models\ProformaSolicitud;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Servicio;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
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

    public string $tab = 'servicios'; // servicios, solicitudes, calendario, recetas, consumos

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

    public ?int $consumo_producto_id = null;

    public int $consumo_cantidad = 1;

    public string $consumo_precio_unitario = '0.00';

    public string $consumo_observaciones = '';

    public array $cola_consumos = [];

    public function mount(Proforma $proforma): void
    {
        $this->proforma = $proforma->load([
            'paciente',
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
        ]);

        $this->proforma->recalcularTotal();
    }

    public function cambiarTab(string $tab): void
    {
        $this->tab = $tab;
    }

    // =========================================================================
    // 1. EDICIÓN DE CABECERA CLÍNICA
    // =========================================================================
    public function abrirModalEditarCabecera(): void
    {
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
        $this->resetValidation();
        $this->reset([
            'nuevo_servicio_id',
            'nuevo_servicio_costo',
            'nuevo_servicio_observaciones',
            'cola_servicios',
        ]);
        $this->nuevo_servicio_costo = '0.00';
        $this->modalServicioOpen = true;
    }

    public function cerrarModalServicio(): void
    {
        $this->modalServicioOpen = false;
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

        $this->reset(['nuevo_servicio_id', 'nuevo_servicio_observaciones']);
        $this->nuevo_servicio_costo = '0.00';
    }

    public function eliminarServicioDeCola(int $index): void
    {
        unset($this->cola_servicios[$index]);
        $this->cola_servicios = array_values($this->cola_servicios);
    }

    public function agregarServicio(): void
    {
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
        $this->resetValidation();
        $this->receta_medico_id = Auth::id();
        $this->receta_observaciones = '';
        $this->receta_medicamentos = [
            ['producto_id' => '', 'cantidad' => 1, 'indicaciones' => ''],
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

    public function prescribirReceta(): void
    {
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
        $this->resetValidation();
        $this->reset([
            'consumo_producto_id',
            'consumo_observaciones',
            'cola_consumos',
        ]);
        $this->consumo_cantidad = 1;
        $this->consumo_precio_unitario = '0.00';
        $this->modalConsumoOpen = true;
    }

    public function cerrarModalConsumo(): void
    {
        $this->modalConsumoOpen = false;
    }

    public function updatedConsumoProductoId($value): void
    {
        if ($value) {
            $producto = Producto::find($value);
            if ($producto) {
                $this->consumo_precio_unitario = number_format($producto->ultimo_precio_venta, 2, '.', '');
            }
        }
    }

    public function agregarConsumoACola(): void
    {
        $this->validate([
            'consumo_producto_id' => ['required', 'exists:productos,id'],
            'consumo_cantidad' => ['required', 'integer', 'min:1'],
            'consumo_precio_unitario' => ['required', 'numeric', 'min:0'],
            'consumo_observaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'consumo_producto_id.required' => 'Seleccione el insumo o material utilizado.',
            'consumo_cantidad.min' => 'La cantidad debe ser mayor a 0.',
        ]);

        $prod = Producto::find($this->consumo_producto_id);
        $this->cola_consumos[] = [
            'producto_id' => $prod->id,
            'nombre' => $prod->nombre,
            'cantidad' => $this->consumo_cantidad,
            'precio_unitario' => (float) $this->consumo_precio_unitario,
            'observaciones' => $this->consumo_observaciones,
        ];

        $this->reset(['consumo_producto_id', 'consumo_observaciones']);
        $this->consumo_cantidad = 1;
        $this->consumo_precio_unitario = '0.00';
    }

    public function eliminarConsumoDeCola(int $index): void
    {
        unset($this->cola_consumos[$index]);
        $this->cola_consumos = array_values($this->cola_consumos);
    }

    public function registrarConsumoExtra(): void
    {
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

        foreach ($this->cola_consumos as $c) {
            ConsumoExtra::create([
                'proforma_id' => $this->proforma->id,
                'producto_id' => $c['producto_id'],
                'cantidad' => $c['cantidad'],
                'precio_unitario' => $c['precio_unitario'],
                'user_id' => Auth::id(),
                'observaciones' => $c['observaciones'],
            ]);
        }

        $cant = count($this->cola_consumos);
        $this->cola_consumos = [];
        $this->proforma->recalcularTotal();
        $this->cerrarModalConsumo();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Insumos Registrados',
            'text' => "Se han cargado {$cant} insumo(s) hospitalarios a la proforma.",
        ]);
    }

    #[On('eliminarConsumoExtra')]
    public function eliminarConsumoExtra(int $id): void
    {
        $consumo = ConsumoExtra::where('proforma_id', $this->proforma->id)->findOrFail($id);
        $consumo->delete();

        $this->proforma->recalcularTotal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Consumo Eliminado',
            'text' => 'El insumo fue removido y el costo total de la proforma actualizado.',
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
        ]);

        $serviciosDisponibles = Servicio::where('estado', true)->with('categoria')->orderBy('nombre')->get();
        $tiposSolicitudes = TipoSolicitud::orderBy('nombre')->get();
        $productosDisponibles = Producto::orderBy('nombre')->get();
        $medicos = User::where('activo', true)->whereHas('rol', fn ($r) => $r->whereIn('nombre', ['Médico', 'Admin']))->get();

        // Totales por rubro para el consolidado financiero
        $subtotalServicios = (float) $this->proforma->servicios->sum('costo_final');
        $subtotalConsumos = (float) $this->proforma->consumosExtras->sum(fn ($c) => $c->cantidad * $c->precio_unitario);
        $subtotalFarmacia = $this->proforma->totalDespachosFarmacia();
        $totalPrescripcionReferencial = $this->proforma->totalPrescripcionReferencial();

        return view('livewire.proformas.proforma-detalle', [
            'serviciosDisponibles' => $serviciosDisponibles,
            'tiposSolicitudes' => $tiposSolicitudes,
            'productosDisponibles' => $productosDisponibles,
            'medicos' => $medicos,
            'subtotalServicios' => $subtotalServicios,
            'subtotalConsumos' => $subtotalConsumos,
            'subtotalFarmacia' => $subtotalFarmacia,
            'totalPrescripcionReferencial' => $totalPrescripcionReferencial,
        ]);
    }
}
