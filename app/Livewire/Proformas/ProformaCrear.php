<?php

namespace App\Livewire\Proformas;

use App\Models\HistoriaClinica;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaCalendario;
use App\Models\ProformaServicio;
use App\Models\ProformaSolicitud;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\TipoSolicitud;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class ProformaCrear extends Component
{
    use WithFileUploads;

    // Datos de la Proforma (sucursal_id se obtiene internamente del usuario en sesión)
    public ?int $sucursal_id = null;

    public ?int $paciente_id = null;

    // 1. Médicos Tratantes con Buscador
    public array $medicos_seleccionados = [];

    public string $medicoSearch = '';

    // Parámetros de Internación / Admisión
    public string $tipo_atencion = 'Ambulatoria'; // Ambulatoria, Internacion

    public string $fecha_ingreso = '';

    public ?string $fecha_salida = null;

    public string $pieza = '';

    public string $motivo_consulta = '';

    public string $diagnostico = '';

    // Historia Clínica (Sección 2)
    public string $procedencia = '';

    public string $residencia = '';

    public string $fuente_informacion = 'Paciente';

    public string $enfermedad_actual = '';

    public string $antecedentes_patologicos = '';

    public string $antecedentes_no_patologicos = '';

    public string $menarca = '';

    public string $fum = '';

    public string $gestas = '';

    public string $partos = '';

    public string $cesareas = '';

    public string $abortos = '';

    public string $pap = '';

    public string $gineco_otros = '';

    public string $pa = '';

    public string $fc = '';

    public string $fr = '';

    public string $temperatura = '';

    public string $sato2 = '';

    public string $peso = '';

    public string $talla = '';

    public string $imc = '';

    public string $examen_fisico = '';

    public string $conducta = '';

    // Búsqueda de Pacientes
    public string $pacienteSearch = '';

    public ?Paciente $pacienteSeleccionado = null;

    // 2. Servicios / Procedimientos Clínicos en Lote
    public string $servicioSearch = '';

    public array $servicios_agregados = [];

    // 3. Solicitudes / Exámenes Clínicos en Lote
    public array $solicitudes_agregadas = [];

    public ?int $temp_solicitud_tipo_id = null;

    public string $temp_solicitud_observaciones = '';

    public $temp_solicitud_archivo = null;

    // 4. Cronograma / Calendario en Lote
    public array $calendario_agregados = [];

    public string $temp_evento_fecha = '';

    public string $temp_evento_hora = '';

    public string $temp_evento_descripcion = '';

    // 5. Prescripción de Receta Médica en Lote
    public bool $incluir_receta = false;

    public ?int $receta_medico_id = null;

    public string $receta_observaciones = '';

    public string $medicamentoSearch = '';

    public array $receta_medicamentos = [];

    // Modal Crear Paciente Rápido
    public bool $modalNuevoPacienteOpen = false;

    public string $nuevo_nombres = '';

    public string $nuevo_apellido_paterno = '';

    public string $nuevo_apellido_materno = '';

    public string $nuevo_cedula = '';

    public ?string $nuevo_fecha_nacimiento = null;

    public string $nuevo_genero = 'Masculino';

    public string $nuevo_celular = '';

    public string $nuevo_direccion = '';

    public string $nuevo_estado_civil = '';

    public string $nuevo_ocupacion = '';

    public string $nuevo_contacto_nombre = '';

    public string $nuevo_contacto_telefono = '';

    public ?int $nuevo_institucion_id = null;

    public string $nuevo_antecedentes_alergias = '';

    public function mount(?int $paciente_id = null): void
    {
        $user = Auth::user();
        // Obtener internamente la sucursal del usuario
        $this->sucursal_id = $user?->sucursal_id ?? Sucursal::first()?->id;
        $this->fecha_ingreso = now()->format('Y-m-d\TH:i');
        $this->temp_evento_fecha = now()->format('Y-m-d');
        $this->temp_evento_hora = now()->addHour()->format('H:00');

        // Por defecto: el usuario que realiza el registro debe estar en la lista de médicos
        if ($user) {
            $this->medicos_seleccionados = [$user->id];
            $this->receta_medico_id = $user->id;
        }

        if ($paciente_id) {
            $this->seleccionarPaciente($paciente_id);
        }
    }

    public function updatedPacienteSearch(): void
    {
        // Actualiza la lista de coincidencias
    }

    public function getResultadosPacientesProperty(): Collection
    {
        if (strlen(trim($this->pacienteSearch)) < 2) {
            return collect();
        }

        $term = '%'.trim($this->pacienteSearch).'%';

        return Paciente::query()
            ->with('institucion')
            ->where(function ($q) use ($term) {
                $q->where('nombres', 'like', $term)
                    ->orWhere('apellido_paterno', 'like', $term)
                    ->orWhere('apellido_materno', 'like', $term)
                    ->orWhere('cedula', 'like', $term)
                    ->orWhere('celular', 'like', $term);
            })
            ->take(6)
            ->get();
    }

    public function seleccionarPaciente(int $id): void
    {
        $this->pacienteSeleccionado = Paciente::with('institucion')->findOrFail($id);
        $this->paciente_id = $this->pacienteSeleccionado->id;
        $this->pacienteSearch = '';

        if (empty($this->residencia) && $this->pacienteSeleccionado->direccion) {
            $this->residencia = $this->pacienteSeleccionado->direccion;
        }
        if (empty($this->antecedentes_patologicos) && $this->pacienteSeleccionado->antecedentes_alergias) {
            $this->antecedentes_patologicos = 'Alergias / Antecedentes: '.$this->pacienteSeleccionado->antecedentes_alergias;
        }
    }

    public function updatedPeso(): void
    {
        $this->calcularImc();
    }

    public function updatedTalla(): void
    {
        $this->calcularImc();
    }

    protected function calcularImc(): void
    {
        $peso = (float) str_replace(',', '.', $this->peso);
        $talla = (float) str_replace(',', '.', $this->talla);

        if ($peso > 0 && $talla > 0) {
            $tallaM = $talla > 3 ? $talla / 100 : $talla;
            $imc = $peso / ($tallaM * $tallaM);
            $this->imc = number_format($imc, 1, '.', '');
        }
    }

    public function deseleccionarPaciente(): void
    {
        $this->pacienteSeleccionado = null;
        $this->paciente_id = null;
        $this->pacienteSearch = '';
    }

    public function abrirModalNuevoPaciente(): void
    {
        if (! Auth::user()?->tienePermiso('pacientes.crear')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para registrar nuevos pacientes.',
            ]);

            return;
        }

        $this->resetValidation();
        $this->reset([
            'nuevo_nombres',
            'nuevo_apellido_paterno',
            'nuevo_apellido_materno',
            'nuevo_cedula',
            'nuevo_institucion_id',
            'nuevo_fecha_nacimiento',
            'nuevo_celular',
            'nuevo_direccion',
            'nuevo_estado_civil',
            'nuevo_ocupacion',
            'nuevo_contacto_nombre',
            'nuevo_contacto_telefono',
            'nuevo_antecedentes_alergias',
        ]);
        $this->nuevo_genero = 'Masculino';
        $this->modalNuevoPacienteOpen = true;
    }

    public function cerrarModalNuevoPaciente(): void
    {
        $this->modalNuevoPacienteOpen = false;
        $this->resetValidation();
    }

    public function guardarNuevoPaciente(): void
    {
        if (! Auth::user()?->tienePermiso('pacientes.crear')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para registrar nuevos pacientes.',
            ]);

            return;
        }

        $this->validate([
            'nuevo_nombres' => ['required', 'string', 'min:2', 'max:100'],
            'nuevo_apellido_paterno' => ['required', 'string', 'min:2', 'max:100'],
            'nuevo_apellido_materno' => ['nullable', 'string', 'max:100'],
            'nuevo_cedula' => [
                'required',
                'string',
                'max:30',
                Rule::unique('pacientes', 'cedula')->whereNull('deleted_at'),
            ],
            'nuevo_institucion_id' => ['nullable', 'integer', 'exists:instituciones,id'],
            'nuevo_fecha_nacimiento' => ['nullable', 'date', 'before_or_equal:today'],
            'nuevo_genero' => ['required', 'in:Masculino,Femenino,Otro'],
            'nuevo_celular' => ['nullable', 'string', 'max:20'],
            'nuevo_direccion' => ['nullable', 'string', 'max:255'],
            'nuevo_estado_civil' => ['nullable', 'string', 'max:50'],
            'nuevo_ocupacion' => ['nullable', 'string', 'max:150'],
            'nuevo_contacto_nombre' => ['nullable', 'string', 'max:150'],
            'nuevo_contacto_telefono' => ['nullable', 'string', 'max:20'],
            'nuevo_antecedentes_alergias' => ['nullable', 'string', 'max:1000'],
        ]);

        $paciente = Paciente::create([
            'institucion_id' => $this->nuevo_institucion_id,
            'nombres' => $this->nuevo_nombres,
            'apellido_paterno' => $this->nuevo_apellido_paterno,
            'apellido_materno' => $this->nuevo_apellido_materno,
            'cedula' => $this->nuevo_cedula,
            'fecha_nacimiento' => $this->nuevo_fecha_nacimiento,
            'genero' => $this->nuevo_genero,
            'celular' => $this->nuevo_celular,
            'direccion' => $this->nuevo_direccion,
            'estado_civil' => $this->nuevo_estado_civil ?: null,
            'ocupacion' => $this->nuevo_ocupacion ?: null,
            'contacto_emergencia_nombre' => $this->nuevo_contacto_nombre,
            'contacto_emergencia_telefono' => $this->nuevo_contacto_telefono,
            'antecedentes_alergias' => $this->nuevo_antecedentes_alergias,
        ]);

        $this->seleccionarPaciente($paciente->id);
        $this->cerrarModalNuevoPaciente();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => 'Paciente Creado',
            'text' => "Se registró y seleccionó a {$paciente->nombre_completo}.",
        ]);
    }

    // =========================================================================
    // GESTIÓN DE MÉDICOS TRATANTES CON BUSCADOR
    // =========================================================================
    public function getResultadosMedicosProperty(): Collection
    {
        if (strlen(trim($this->medicoSearch)) < 1) {
            return collect();
        }

        $term = '%'.trim($this->medicoSearch).'%';

        return User::where('activo', true)
            ->whereNotIn('id', $this->medicos_seleccionados)
            ->whereHas('rol', fn ($r) => $r->whereIn('nombre', ['Médico', 'Admin']))
            ->where(function ($q) use ($term) {
                $q->where('nombres', 'like', $term)
                    ->orWhere('apellido_paterno', 'like', $term)
                    ->orWhere('apellido_materno', 'like', $term)
                    ->orWhereHas('especialidad', fn ($e) => $e->where('nombre', 'like', $term));
            })
            ->with(['especialidad', 'rol'])
            ->take(6)
            ->get();
    }

    public function getMedicosSeleccionadosModelosProperty(): Collection
    {
        if (empty($this->medicos_seleccionados)) {
            return collect();
        }

        return User::whereIn('id', $this->medicos_seleccionados)->with('especialidad')->get();
    }

    public function agregarMedico(int $id): void
    {
        if (! in_array($id, $this->medicos_seleccionados)) {
            $this->medicos_seleccionados[] = $id;
        }
        $this->medicoSearch = '';
    }

    public function eliminarMedico(int $id): void
    {
        $this->medicos_seleccionados = array_values(array_filter($this->medicos_seleccionados, fn ($mId) => $mId !== $id));
    }

    // =========================================================================
    // GESTIÓN EN LOTE: SERVICIOS Y PROCEDIMIENTOS
    // =========================================================================
    public function getResultadosServiciosProperty(): Collection
    {
        if (strlen(trim($this->servicioSearch)) < 1) {
            return collect();
        }

        $term = '%'.trim($this->servicioSearch).'%';

        return Servicio::where('estado', true)
            ->where(function ($q) use ($term) {
                $q->where('nombre', 'like', $term)
                    ->orWhereHas('categoria', fn ($c) => $c->where('nombre', 'like', $term));
            })
            ->with('categoria')
            ->take(6)
            ->get();
    }

    public function agregarServicioALista(int $servicioId): void
    {
        $servicio = Servicio::with('categoria')->find($servicioId);
        if ($servicio) {
            $this->servicios_agregados[] = [
                'servicio_id' => $servicio->id,
                'nombre' => $servicio->nombre,
                'categoria' => $servicio->categoria->nombre ?? 'General',
                'costo_final' => (string) number_format((float) $servicio->precio_tentativo, 2, '.', ''),
                'observaciones' => '',
            ];
        }
        $this->servicioSearch = '';
    }

    public function eliminarServicioDeLista(int $index): void
    {
        unset($this->servicios_agregados[$index]);
        $this->servicios_agregados = array_values($this->servicios_agregados);
    }

    // =========================================================================
    // GESTIÓN EN LOTE: SOLICITUDES Y EXÁMENES
    // =========================================================================
    public function agregarSolicitudALista(): void
    {
        $this->validate([
            'temp_solicitud_tipo_id' => ['required', 'exists:tipos_solicitudes,id'],
            'temp_solicitud_observaciones' => ['nullable', 'string', 'max:1000'],
            'temp_solicitud_archivo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'temp_solicitud_tipo_id.required' => 'Seleccione el tipo de examen.',
            'temp_solicitud_archivo.mimes' => 'El archivo adjunto solo puede ser un PDF o imagen (JPG, PNG, WEBP).',
        ]);

        $tipo = TipoSolicitud::find($this->temp_solicitud_tipo_id);
        $this->solicitudes_agregadas[] = [
            'tipo_solicitud_id' => $this->temp_solicitud_tipo_id,
            'tipo_nombre' => $tipo?->nombre ?? 'Estudio',
            'observaciones' => $this->temp_solicitud_observaciones,
            'archivo' => $this->temp_solicitud_archivo,
        ];

        $this->reset(['temp_solicitud_tipo_id', 'temp_solicitud_observaciones', 'temp_solicitud_archivo']);
    }

    public function eliminarSolicitudDeLista(int $index): void
    {
        unset($this->solicitudes_agregadas[$index]);
        $this->solicitudes_agregadas = array_values($this->solicitudes_agregadas);
    }

    // =========================================================================
    // GESTIÓN EN LOTE: CRONOGRAMA / CALENDARIO
    // =========================================================================
    public function agregarEventoALista(): void
    {
        $this->validate([
            'temp_evento_fecha' => ['required', 'date'],
            'temp_evento_hora' => ['required'],
            'temp_evento_descripcion' => ['required', 'string', 'max:255'],
        ], [
            'temp_evento_descripcion.required' => 'Describa la actividad del cronograma.',
        ]);

        $this->calendario_agregados[] = [
            'fecha' => $this->temp_evento_fecha,
            'hora' => $this->temp_evento_hora,
            'descripcion' => $this->temp_evento_descripcion,
        ];

        $this->temp_evento_descripcion = '';
    }

    public function eliminarEventoDeLista(int $index): void
    {
        unset($this->calendario_agregados[$index]);
        $this->calendario_agregados = array_values($this->calendario_agregados);
    }

    // =========================================================================
    // GESTIÓN EN LOTE: RECETAS MÉDICAS
    // =========================================================================
    public function getResultadosMedicamentosProperty(): Collection
    {
        if (strlen(trim($this->medicamentoSearch)) < 1) {
            return collect();
        }

        $term = '%'.trim($this->medicamentoSearch).'%';

        return Producto::where(function ($q) use ($term) {
            $q->where('nombre', 'like', $term)
                ->orWhere('descripcion', 'like', $term);
        })
            ->take(6)
            ->get();
    }

    public function agregarMedicamentoALista(int $productoId): void
    {
        $producto = Producto::find($productoId);
        if ($producto) {
            $this->receta_medicamentos[] = [
                'producto_id' => $producto->id,
                'nombre' => $producto->nombre,
                'precio' => (float) $producto->ultimo_precio_venta,
                'cantidad' => 1,
                'indicaciones' => 'Tomar cada 8 horas por 5 días',
            ];
            $this->incluir_receta = true;
        }
        $this->medicamentoSearch = '';
    }

    public function eliminarMedicamentoDeLista(int $index): void
    {
        unset($this->receta_medicamentos[$index]);
        $this->receta_medicamentos = array_values($this->receta_medicamentos);
        if (empty($this->receta_medicamentos)) {
            $this->incluir_receta = false;
        }
    }

    public function getTotalEstimadoProperty(): float
    {
        $totalServicios = collect($this->servicios_agregados)->sum(fn ($s) => (float) ($s['costo_final'] ?? 0));
        $totalMedicamentos = collect($this->receta_medicamentos)->sum(fn ($m) => ((int) ($m['cantidad'] ?? 0)) * ((float) ($m['precio'] ?? 0)));

        return $totalServicios + $totalMedicamentos;
    }

    // =========================================================================
    // APERTURA Y REGISTRO COMPLETO EN LOTE
    // =========================================================================
    public function abrirProforma(): void
    {
        if (! Auth::user()?->tienePermiso('crear-proforma')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para abrir o registrar nuevas proformas clínicas.',
            ]);

            return;
        }

        // Asegurar sucursal internamente desde el usuario en sesión
        $this->sucursal_id = Auth::user()->sucursal_id ?? $this->sucursal_id ?? Sucursal::first()?->id;

        $this->validate([
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'paciente_id' => ['required', 'exists:pacientes,id'],
            'medicos_seleccionados' => ['required', 'array', 'min:1'],
            'medicos_seleccionados.*' => ['exists:users,id'],
            'tipo_atencion' => ['required', 'in:Ambulatoria,Internacion'],
            'fecha_ingreso' => ['required', 'date'],
            'fecha_salida' => ['nullable', 'date', 'after_or_equal:fecha_ingreso'],
            'pieza' => ['nullable', 'string', 'max:100'],
            'motivo_consulta' => ['nullable', 'string', 'max:1000'],
            'diagnostico' => ['nullable', 'string', 'max:1000'],
            'procedencia' => ['nullable', 'string', 'max:150'],
            'residencia' => ['nullable', 'string', 'max:255'],
            'fuente_informacion' => ['nullable', 'string', 'max:100'],
            'enfermedad_actual' => ['nullable', 'string', 'max:5000'],
            'antecedentes_patologicos' => ['nullable', 'string', 'max:5000'],
            'antecedentes_no_patologicos' => ['nullable', 'string', 'max:5000'],
            'menarca' => ['nullable', 'string', 'max:50'],
            'fum' => ['nullable', 'string', 'max:50'],
            'gestas' => ['nullable', 'string', 'max:20'],
            'partos' => ['nullable', 'string', 'max:20'],
            'cesareas' => ['nullable', 'string', 'max:20'],
            'abortos' => ['nullable', 'string', 'max:20'],
            'pap' => ['nullable', 'string', 'max:100'],
            'gineco_otros' => ['nullable', 'string', 'max:255'],
            'pa' => ['nullable', 'string', 'max:30'],
            'fc' => ['nullable', 'string', 'max:30'],
            'fr' => ['nullable', 'string', 'max:30'],
            'temperatura' => ['nullable', 'string', 'max:30'],
            'sato2' => ['nullable', 'string', 'max:30'],
            'peso' => ['nullable', 'string', 'max:30'],
            'talla' => ['nullable', 'string', 'max:30'],
            'imc' => ['nullable', 'string', 'max:30'],
            'examen_fisico' => ['nullable', 'string', 'max:5000'],
            'conducta' => ['nullable', 'string', 'max:5000'],
            'servicios_agregados.*.costo_final' => ['nullable', 'numeric', 'min:0'],
            'receta_medicamentos.*.cantidad' => ['nullable', 'integer', 'min:1'],
            'receta_medicamentos.*.indicaciones' => ['nullable', 'string', 'max:255'],
        ], [
            'paciente_id.required' => 'Debe buscar o registrar un paciente para abrir la proforma.',
            'medicos_seleccionados.required' => 'Debe asignar al menos un médico tratante.',
            'medicos_seleccionados.min' => 'Debe asignar al menos un médico tratante.',
            'fecha_ingreso.required' => 'La fecha y hora de ingreso es obligatoria.',
            'fecha_salida.after_or_equal' => 'La fecha estimada de salida no puede ser anterior al ingreso.',
        ]);

        $proforma = Proforma::create([
            'sucursal_id' => $this->sucursal_id,
            'paciente_id' => $this->paciente_id,
            'tipo_atencion' => $this->tipo_atencion,
            'fecha_ingreso' => $this->fecha_ingreso,
            'fecha_salida' => $this->fecha_salida,
            'pieza' => $this->pieza,
            'motivo_consulta' => $this->motivo_consulta,
            'diagnostico' => $this->diagnostico,
            'estado' => 'En Curso',
            'costo_total' => 0.00,
        ]);

        // Registrar Historia Clínica Inicial de la Proforma
        HistoriaClinica::create([
            'proforma_id' => $proforma->id,
            'procedencia' => $this->procedencia ?: null,
            'residencia' => $this->residencia ?: null,
            'fuente_informacion' => $this->fuente_informacion ?: 'Paciente',
            'motivo_consulta' => $this->motivo_consulta ?: null,
            'enfermedad_actual' => $this->enfermedad_actual ?: null,
            'antecedentes_patologicos' => $this->antecedentes_patologicos ?: null,
            'antecedentes_no_patologicos' => $this->antecedentes_no_patologicos ?: null,
            'menarca' => $this->menarca ?: null,
            'fum' => $this->fum ?: null,
            'gestas' => $this->gestas ?: null,
            'partos' => $this->partos ?: null,
            'cesareas' => $this->cesareas ?: null,
            'abortos' => $this->abortos ?: null,
            'pap' => $this->pap ?: null,
            'gineco_otros' => $this->gineco_otros ?: null,
            'pa' => $this->pa ?: null,
            'fc' => $this->fc ?: null,
            'fr' => $this->fr ?: null,
            'temperatura' => $this->temperatura ?: null,
            'sato2' => $this->sato2 ?: null,
            'peso' => $this->peso ?: null,
            'talla' => $this->talla ?: null,
            'imc' => $this->imc ?: null,
            'examen_fisico' => $this->examen_fisico ?: null,
            'diagnostico' => $this->diagnostico ?: null,
            'conducta' => $this->conducta ?: null,
            'usuario_creador_id' => Auth::id(),
        ]);

        // 1. Asociar Médicos Tratantes
        $proforma->medicos()->sync($this->medicos_seleccionados);

        // 2. Asociar Servicios Clínicos en lote
        foreach ($this->servicios_agregados as $serv) {
            ProformaServicio::create([
                'proforma_id' => $proforma->id,
                'servicio_id' => $serv['servicio_id'],
                'costo_final' => $serv['costo_final'],
                'observaciones' => $serv['observaciones'] ?? '',
            ]);
        }

        // 3. Asociar Solicitudes y Exámenes con adjuntos en lote
        foreach ($this->solicitudes_agregadas as $sol) {
            $archivoRuta = null;
            if (isset($sol['archivo']) && $sol['archivo']) {
                $archivoRuta = $sol['archivo']->store('solicitudes_clinicas', 'public');
            }

            ProformaSolicitud::create([
                'proforma_id' => $proforma->id,
                'tipo_solicitud_id' => $sol['tipo_solicitud_id'],
                'observaciones' => $sol['observaciones'] ?? '',
                'archivo' => $archivoRuta,
            ]);
        }

        // 4. Asociar Cronograma / Calendario en lote
        foreach ($this->calendario_agregados as $cal) {
            ProformaCalendario::create([
                'proforma_id' => $proforma->id,
                'fecha' => $cal['fecha'],
                'hora' => $cal['hora'],
                'descripcion' => $cal['descripcion'],
                'estado' => 'Programado',
            ]);
        }

        // 5. Prescribir Receta Médica Inicial en lote (si se agregaron fármacos)
        if ($this->incluir_receta && ! empty($this->receta_medicamentos)) {
            $receta = Receta::create([
                'proforma_id' => $proforma->id,
                'user_id' => $this->receta_medico_id ?? Auth::id(),
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
        }

        // Recalcular consolidado financiero automático
        $proforma->recalcularTotal();

        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => '¡Proforma Creada Exitosamente!',
            'text' => "Se aperturó el expediente #{$proforma->id} con todos sus servicios, exámenes, cronograma y receta.",
        ]);

        $this->redirectRoute('proformas.show', ['proforma' => $proforma->id]);
    }

    public function render(): View
    {
        $tiposSolicitudes = TipoSolicitud::orderBy('nombre')->get();
        $medicosParaReceta = User::where('activo', true)
            ->whereHas('rol', fn ($r) => $r->whereIn('nombre', ['Médico', 'Admin']))
            ->get();
        $instituciones = Institucion::where('estado', 'Activo')->orderBy('nombre')->get();

        return view('livewire.proformas.proforma-crear', [
            'tiposSolicitudes' => $tiposSolicitudes,
            'medicosParaReceta' => $medicosParaReceta,
            'instituciones' => $instituciones,
        ]);
    }
}
