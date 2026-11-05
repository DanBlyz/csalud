<?php

use App\Livewire\Pacientes\PacientesIndex;
use App\Livewire\Proformas\ProformaCrear;
use App\Livewire\Proformas\ProformaDetalle;
use App\Livewire\Proformas\ProformasIndex;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cirugia;
use App\Models\ConsumoExtra;
use App\Models\Epicrisis;
use App\Models\Evolucion;
use App\Models\HistoriaClinica;
use App\Models\Indicacion;
use App\Models\Institucion;
use App\Models\Internacion;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaCalendario;
use App\Models\ProformaPagoMedico;
use App\Models\ProformaServicio;
use App\Models\ProformaSolicitud;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Services\ReportePacientesService;
use Database\Seeders\PermisoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(['nombre' => 'Admin'], ['descripcion' => 'Admin']);
    $this->sucursal = Sucursal::factory()->create();
    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);
    $this->actingAs($this->admin);
});

test('puede acceder al listado de pacientes y renderizar correctamente', function () {
    $this->actingAs($this->admin);

    Livewire::test(PacientesIndex::class)
        ->assertStatus(200)
        ->assertSee('Directorio y Registro de Pacientes');
});

test('puede registrar y editar un paciente en pacientes index', function () {
    $this->actingAs($this->admin);

    $cedula = 'CI-TEST-'.rand(1000, 9999);

    // Crear paciente
    Livewire::test(PacientesIndex::class)
        ->call('abrirModalCrear')
        ->set('nombres', 'Juan Pablo')
        ->set('apellido_paterno', 'Mamani')
        ->set('apellido_materno', 'Choque')
        ->set('cedula', $cedula)
        ->set('fecha_nacimiento', '1995-05-15')
        ->set('genero', 'Masculino')
        ->set('celular', '71234567')
        ->set('estado_civil', 'Soltero(a)')
        ->set('ocupacion', 'Contador')
        ->set('antecedentes_alergias', 'Alérgico a sulfas')
        ->call('guardar')
        ->assertDispatched('swal');

    $paciente = Paciente::where('cedula', $cedula)->first();
    expect($paciente)->not->toBeNull();
    expect($paciente->nombres)->toBe('Juan Pablo');
    expect($paciente->estado_civil)->toBe('Soltero(a)');
    expect($paciente->ocupacion)->toBe('Contador');
    expect($paciente->edad)->not->toBeNull();

    // Editar paciente
    Livewire::test(PacientesIndex::class)
        ->call('abrirModalEditar', $paciente->id)
        ->assertSet('estado_civil', 'Soltero(a)')
        ->assertSet('ocupacion', 'Contador')
        ->set('nombres', 'Juan Pablo Modificado')
        ->set('estado_civil', 'Casado(a)')
        ->set('ocupacion', 'Docente')
        ->call('guardar')
        ->assertDispatched('swal');

    expect($paciente->fresh()->nombres)->toBe('Juan Pablo Modificado');
    expect($paciente->fresh()->estado_civil)->toBe('Casado(a)');
    expect($paciente->fresh()->ocupacion)->toBe('Docente');
});

test('puede registrar un nuevo paciente rapido con estado civil y ocupacion en proforma crear', function () {
    $this->actingAs($this->admin);

    $cedulaQuick = 'CI-QUICK-'.rand(1000, 9999);

    Livewire::test(ProformaCrear::class)
        ->call('abrirModalNuevoPaciente')
        ->set('nuevo_nombres', 'Patricia')
        ->set('nuevo_apellido_paterno', 'Flores')
        ->set('nuevo_apellido_materno', 'Rios')
        ->set('nuevo_cedula', $cedulaQuick)
        ->set('nuevo_fecha_nacimiento', '1990-11-20')
        ->set('nuevo_genero', 'Femenino')
        ->set('nuevo_celular', '78945612')
        ->set('nuevo_estado_civil', 'Casado(a)')
        ->set('nuevo_ocupacion', 'Abogada')
        ->call('guardarNuevoPaciente')
        ->assertDispatched('swal');

    $paciente = Paciente::where('cedula', $cedulaQuick)->first();
    expect($paciente)->not->toBeNull();
    expect($paciente->nombres)->toBe('Patricia');
    expect($paciente->estado_civil)->toBe('Casado(a)');
    expect($paciente->ocupacion)->toBe('Abogada');
});

test('puede consultar el historial clinico de un paciente', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Mario',
        'apellido_paterno' => 'Bros',
        'cedula' => 'CI-HIST-'.rand(100, 999),
        'genero' => 'Masculino',
    ]);

    Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'motivo_consulta' => 'Control de rutina',
    ]);

    Livewire::test(PacientesIndex::class)
        ->call('verHistorial', $paciente->id)
        ->assertSet('modalHistorialOpen', true)
        ->assertSee('Historial Clínico de Mario Bros')
        ->assertSee('Control de rutina');
});

test('puede aperturar una proforma en proforma crear (fase 1)', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Luigi',
        'apellido_paterno' => 'Bros',
        'cedula' => 'CI-PRO-'.rand(100, 999),
        'genero' => 'Masculino',
    ]);

    Livewire::test(ProformaCrear::class)
        ->call('seleccionarPaciente', $paciente->id)
        ->set('tipo_atencion', 'Internacion')
        ->set('pieza', 'Sala 4 - Cama 2')
        ->set('medicos_seleccionados', [$this->admin->id])
        ->set('motivo_consulta', 'Fiebre persistente y tos')
        ->set('diagnostico', 'Sospecha bronconeumonía')
        ->call('abrirProforma')
        ->assertDispatched('swal');

    $proforma = Proforma::where('paciente_id', $paciente->id)->latest('id')->first();
    expect($proforma)->not->toBeNull();
    expect($proforma->pieza)->toBe('Sala 4 - Cama 2');
    expect($proforma->tipo_atencion)->toBe('Internacion');
    expect($proforma->estado)->toBe('En Curso');
    expect($proforma->sucursal_id)->toBe($this->sucursal->id);
    expect($proforma->medicos)->toHaveCount(1);
    expect($proforma->medicos->first()->id)->toBe($this->admin->id);
});

test('puede gestionar detalle de la proforma (fase 2) con servicios, solicitudes, calendario, receta activa y consumos', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Carla',
        'apellido_paterno' => 'Mendoza',
        'cedula' => 'CI-DET-'.rand(100, 999),
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $categoria = Categoria::firstOrCreate(['nombre' => 'Consultas'], ['descripcion' => 'Consultas', 'estado' => true]);
    $servicio = Servicio::firstOrCreate(['nombre' => 'Consulta General'], ['categoria_id' => $categoria->id, 'precio_tentativo' => 70.00, 'estado' => true]);
    $tipoSolicitud = TipoSolicitud::firstOrCreate(['nombre' => 'Hemograma Completo'], ['descripcion' => 'Laboratorio']);
    $producto = Producto::firstOrCreate(['nombre' => 'Paracetamol 500mg'], ['ultimo_precio_venta' => 15.00, 'stock_minimo' => 10]);

    // 1. Agregar Servicio Clínico
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalServicio')
        ->set('nuevo_servicio_id', $servicio->id)
        ->set('nuevo_servicio_costo', '150.00')
        ->set('nuevo_servicio_observaciones', 'Procedimiento con descuento')
        ->call('agregarServicio')
        ->assertDispatched('swal');

    expect($proforma->fresh()->costo_total)->toBe('150.00');
    expect(ProformaServicio::where('proforma_id', $proforma->id)->count())->toBe(1);

    // 2. Agregar Solicitud / Examen
    Storage::fake('public');
    $fakeFile = UploadedFile::fake()->create('laboratorio.pdf', 100, 'application/pdf');

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalSolicitud')
        ->set('nuevo_tipo_solicitud_id', $tipoSolicitud->id)
        ->set('nuevo_solicitud_observaciones', 'Urgente para hoy')
        ->set('nuevo_solicitud_archivo', $fakeFile)
        ->call('agregarSolicitud')
        ->assertDispatched('swal');

    expect(ProformaSolicitud::where('proforma_id', $proforma->id)->count())->toBe(1);

    // 3. Agregar Evento de Calendario
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalCalendario')
        ->set('nuevo_evento_fecha', now()->format('Y-m-d'))
        ->set('nuevo_evento_hora', '14:30')
        ->set('nuevo_evento_descripcion', 'Curación ambulatoria')
        ->call('agregarEventoCalendario')
        ->assertDispatched('swal');

    $evento = ProformaCalendario::where('proforma_id', $proforma->id)->first();
    expect($evento)->not->toBeNull();
    expect($evento->estado)->toBe('Programado');

    // Toggle evento
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('toggleEstadoEvento', $evento->id);
    expect($evento->fresh()->estado)->toBe('Realizado');

    // 4. Prescribir Receta Médica (Verificar Regla de Receta Activa Única)
    // Receta 1
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalReceta')
        ->set('receta_medico_id', $this->admin->id)
        ->set('receta_observaciones', 'Primera indicación')
        ->set('receta_medicamentos', [
            ['producto_id' => $producto->id, 'cantidad' => 2, 'indicaciones' => '1 cada 8 hrs'],
        ])
        ->call('prescribirReceta')
        ->assertDispatched('swal');

    $receta1 = Receta::where('proforma_id', $proforma->id)->latest('id')->first();
    expect($receta1->activo)->toBeTrue();

    // Receta 2 (Debe desactivar la Receta 1 automáticamente)
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalReceta')
        ->set('receta_medico_id', $this->admin->id)
        ->set('receta_observaciones', 'Segunda indicación ajustada')
        ->set('receta_medicamentos', [
            ['producto_id' => $producto->id, 'cantidad' => 3, 'indicaciones' => '1 cada 12 hrs'],
        ])
        ->call('prescribirReceta')
        ->assertDispatched('swal');

    $receta2 = Receta::where('proforma_id', $proforma->id)->latest('id')->first();
    expect($receta2->id)->not->toBe($receta1->id);
    expect($receta2->activo)->toBeTrue();
    expect($receta1->fresh()->activo)->toBeFalse(); // Desactivada automáticamente!

    // 5. Cargar Consumo Extra (Insumo de piso con stock en sucursal)
    Lote::create([
        'sucursal_id' => $proforma->sucursal_id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'LOTE-TEST-001',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 10.00,
        'precio_venta' => 15.00,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_producto_id', $producto->id)
        ->set('consumo_cantidad', 2)
        ->set('consumo_precio_unitario', '20.00')
        ->set('consumo_observaciones', 'Uso directo en consultorio')
        ->call('registrarConsumoExtra')
        ->assertDispatched('swal');

    expect(ConsumoExtra::where('proforma_id', $proforma->id)->count())->toBe(1);

    // Regla de Negocio: La receta prescrita es pauta médica referencial.
    // El costo_total inicial consolidado de la proforma es SOLO: Servicios (150) + Consumos de Piso (2 * 20 = 40) = 190.00
    $proformaFresh = $proforma->fresh();
    expect((float) $proformaFresh->costo_total)->toBe(190.00);
    $precioMed = (float) $producto->ultimo_precio_venta;
    expect((float) $proformaFresh->totalPrescripcionReferencial())->toBe(3 * $precioMed);

    // Si Farmacia realiza un despacho real (ej: entrega 1 unidad por MovimientoInventario 'Salida Receta'):
    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'LOT-TEST-1',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 99,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 5.00,
        'precio_venta' => $precioMed,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'proforma_id' => $proforma->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'user_id' => $this->admin->id,
        'tipo_movimiento' => 'Salida Receta',
        'cantidad' => 1,
        'fecha' => now(),
        'observaciones' => 'Despacho de 1 unidad día 1',
    ]);

    $proformaFresh->recalcularTotal();
    // Ahora el costo total suma: 150 (Servicios) + 40 (Consumos Piso) + (1 * precioMed Farmacia)
    expect((float) $proformaFresh->fresh()->costo_total)->toBe(190.00 + (1 * $precioMed));
    expect((float) $proformaFresh->totalDespachosFarmacia())->toBe(1 * $precioMed);
});

test('puede actualizar cabecera de proforma y sincronizar medicos asignados', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Valeria',
        'apellido_paterno' => 'Perez',
        'cedula' => 'CI-CAB-'.rand(100, 999),
        'genero' => 'Femenino',
    ]);

    $otroMedico = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $proforma->medicos()->attach($this->admin->id);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalEditarCabecera')
        ->assertSet('edit_medicos', [$this->admin->id])
        ->set('edit_pieza', 'Box 5')
        ->set('edit_medicos', [$this->admin->id, $otroMedico->id])
        ->call('guardarCabecera')
        ->assertDispatched('swal');

    expect($proforma->fresh()->pieza)->toBe('Box 5');
    expect($proforma->fresh()->medicos)->toHaveCount(2);
});

test('proforma crear preselecciona al usuario autenticado y permite buscar y gestionar medicos', function () {
    $this->actingAs($this->admin);

    $segundoMedico = User::factory()->create([
        'nombres' => 'Fernando',
        'apellido_paterno' => 'Suarez',
        'apellido_materno' => 'Paz',
        'activo' => true,
    ]);

    Livewire::test(ProformaCrear::class)
        ->assertSet('medicos_seleccionados', [$this->admin->id])
        ->call('agregarMedico', $segundoMedico->id)
        ->assertSet('medicos_seleccionados', [$this->admin->id, $segundoMedico->id])
        ->call('eliminarMedico', $this->admin->id)
        ->assertSet('medicos_seleccionados', [$segundoMedico->id]);
});

test('puede aperturar una proforma con servicios solicitudes calendario y recetas en lote desde la creacion', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Gabriel',
        'apellido_paterno' => 'Mercado',
        'cedula' => 'CI-BATCH-'.rand(100, 999),
        'genero' => 'Masculino',
    ]);

    $categoria = Categoria::firstOrCreate(['nombre' => 'Cirugia'], ['descripcion' => 'Cirugia', 'estado' => true]);
    $servicio = Servicio::firstOrCreate(['nombre' => 'Apendicectomía Laparoscópica'], ['categoria_id' => $categoria->id, 'precio_tentativo' => 2500.00, 'estado' => true]);
    $tipoSolicitud = TipoSolicitud::firstOrCreate(['nombre' => 'Ecografía Abdominal'], ['descripcion' => 'Imagenología']);
    $medicamento = Producto::firstOrCreate(['nombre' => 'Ciprofloxacino 500mg'], ['ultimo_precio_venta' => 25.00, 'stock_minimo' => 10]);

    Livewire::test(ProformaCrear::class)
        ->call('seleccionarPaciente', $paciente->id)
        ->set('tipo_atencion', 'Internacion')
        ->set('pieza', 'Quirófano 1')
        ->set('motivo_consulta', 'Dolor agudo en fosa ilíaca derecha')
        ->set('diagnostico', 'Apendicitis aguda grado II')
        // Adjuntar servicio en lote
        ->call('agregarServicioALista', $servicio->id)
        ->set('servicios_agregados.0.costo_final', '2300.00')
        ->set('servicios_agregados.0.observaciones', 'Honorarios incluidos')
        // Adjuntar solicitud en lote
        ->set('temp_solicitud_tipo_id', $tipoSolicitud->id)
        ->set('temp_solicitud_observaciones', 'Revisar apéndice cecal')
        ->call('agregarSolicitudALista')
        // Adjuntar evento de calendario en lote
        ->set('temp_evento_fecha', now()->format('Y-m-d'))
        ->set('temp_evento_hora', '08:00')
        ->set('temp_evento_descripcion', 'Ingreso a quirófano y preparación prequirúrgica')
        ->call('agregarEventoALista')
        // Adjuntar receta médica en lote
        ->call('agregarMedicamentoALista', $medicamento->id)
        ->set('receta_observaciones', 'Tratamiento postoperatorio inmediato')
        ->set('receta_medicamentos.0.cantidad', 2)
        ->set('receta_medicamentos.0.indicaciones', '1 comp cada 12 horas por 7 días')
        // Guardar proforma con todo el paquete en lote
        ->call('abrirProforma')
        ->assertDispatched('swal');

    $proforma = Proforma::where('paciente_id', $paciente->id)->latest('id')->first();
    expect($proforma)->not->toBeNull();
    expect($proforma->sucursal_id)->toBe($this->sucursal->id);
    expect($proforma->tipo_atencion)->toBe('Internacion');

    // Verificar médico tratante por defecto
    expect($proforma->medicos)->toHaveCount(1);
    expect($proforma->medicos->first()->id)->toBe($this->admin->id);

    // Verificar servicios creados
    expect(ProformaServicio::where('proforma_id', $proforma->id)->count())->toBe(1);
    expect((float) ProformaServicio::where('proforma_id', $proforma->id)->first()->costo_final)->toBe(2300.00);

    // Verificar solicitudes creadas
    expect(ProformaSolicitud::where('proforma_id', $proforma->id)->count())->toBe(1);

    // Verificar calendario creado
    expect(ProformaCalendario::where('proforma_id', $proforma->id)->count())->toBe(1);

    // Verificar receta creada y activa
    $receta = Receta::where('proforma_id', $proforma->id)->first();
    expect($receta)->not->toBeNull();
    expect($receta->activo)->toBeTrue();
    expect($receta->detalles)->toHaveCount(1);

    // Verificar recálculo de costo total: solo servicios (2300.00). La receta es pauta referencial (50.00)
    expect((float) $proforma->fresh()->costo_total)->toBe(2300.00);
    expect((float) $proforma->fresh()->totalPrescripcionReferencial())->toBe(50.00);
});

test('puede encolar y registrar multiples insumos en lote en proforma detalle', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Patricia',
        'apellido_paterno' => 'Vaca',
        'cedula' => 'CI-COLA-'.rand(100, 999),
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $prod1 = Producto::firstOrCreate(['nombre' => 'Jeringa 10ml'], ['ultimo_precio_venta' => 2.50, 'stock_minimo' => 20]);
    $prod2 = Producto::firstOrCreate(['nombre' => 'Catéter Nro 20'], ['ultimo_precio_venta' => 8.00, 'stock_minimo' => 20]);

    Lote::create([
        'sucursal_id' => $proforma->sucursal_id,
        'producto_id' => $prod1->id,
        'codigo_lote' => 'LOTE-JER-001',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 1.50,
        'precio_venta' => 2.50,
    ]);

    Lote::create([
        'sucursal_id' => $proforma->sucursal_id,
        'producto_id' => $prod2->id,
        'codigo_lote' => 'LOTE-CAT-001',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 5.00,
        'precio_venta' => 8.00,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_producto_id', $prod1->id)
        ->set('consumo_cantidad', 4)
        ->set('consumo_precio_unitario', '2.50')
        ->set('consumo_observaciones', 'Vía venosa')
        ->call('agregarConsumoACola')
        ->set('consumo_producto_id', $prod2->id)
        ->set('consumo_cantidad', 2)
        ->set('consumo_precio_unitario', '8.00')
        ->set('consumo_observaciones', 'Canalización')
        ->call('agregarConsumoACola')
        ->assertCount('cola_consumos', 2)
        ->call('registrarConsumoExtra')
        ->assertDispatched('swal');

    // Verificar consumos creados y recálculo
    expect(ConsumoExtra::where('proforma_id', $proforma->id)->count())->toBe(2);
    // (4 * 2.50) + (2 * 8.00) = 10.00 + 16.00 = 26.00
    expect((float) $proforma->fresh()->costo_total)->toBe(26.00);
});

test('puede subir y actualizar el archivo digital de una solicitud en proforma detalle', function () {
    $this->actingAs($this->admin);
    Storage::fake('public');

    $paciente = Paciente::create([
        'nombres' => 'Marcelo',
        'apellido_paterno' => 'Claure',
        'cedula' => 'CI-FILE-'.rand(100, 999),
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $tipoSolicitud = TipoSolicitud::firstOrCreate(['nombre' => 'Radiografía de Tórax'], ['descripcion' => 'Rayos X']);

    $solicitud = ProformaSolicitud::create([
        'proforma_id' => $proforma->id,
        'tipo_solicitud_id' => $tipoSolicitud->id,
        'observaciones' => 'Descartar neumotórax',
        'archivo' => null,
    ]);

    $fakeImg = UploadedFile::fake()->image('placa_torax.png');

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalSubirArchivo', $solicitud->id)
        ->assertSet('modalArchivoSolicitudOpen', true)
        ->assertSet('solicitud_id_archivo', $solicitud->id)
        ->set('solicitud_nuevo_archivo', $fakeImg)
        ->call('guardarArchivoSolicitud')
        ->assertDispatched('swal');

    $solicitudActualizada = $solicitud->fresh();
    expect($solicitudActualizada->archivo)->not->toBeNull();
    Storage::disk('public')->assertExists($solicitudActualizada->archivo);

    // Ahora actualizar con un PDF
    $archivoViejo = $solicitudActualizada->archivo;
    $fakePdf = UploadedFile::fake()->create('informe_radiologico.pdf', 150, 'application/pdf');

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalSubirArchivo', $solicitud->id)
        ->set('solicitud_nuevo_archivo', $fakePdf)
        ->call('guardarArchivoSolicitud')
        ->assertDispatched('swal');

    $solicitudFinal = $solicitud->fresh();
    expect($solicitudFinal->archivo)->not->toBe($archivoViejo);
    Storage::disk('public')->assertExists($solicitudFinal->archivo);
    Storage::disk('public')->assertMissing($archivoViejo); // El archivo viejo fue limpiado

    // Validar rechazo de archivos no admitidos (e.g. .exe)
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalSubirArchivo', $solicitud->id)
        ->set('solicitud_nuevo_archivo', UploadedFile::fake()->create('script_malicioso.exe', 100))
        ->call('guardarArchivoSolicitud')
        ->assertHasErrors(['solicitud_nuevo_archivo' => 'mimes']);
});

test('puede buscar y seleccionar reactivamente en modales de servicios, recetas y consumos de proforma detalle', function () {
    $paciente = Paciente::create([
        'nombres' => 'Elena',
        'apellido_paterno' => 'Torres',
        'cedula' => '99887766',
        'fecha_nacimiento' => '1995-04-12',
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $categoria = Categoria::firstOrCreate(['nombre' => 'Cirugía Menor'], ['estado' => true]);
    $servicio = Servicio::firstOrCreate(
        ['nombre' => 'Sutura Simple'],
        ['categoria_id' => $categoria->id, 'precio_tentativo' => 120.00, 'estado' => true]
    );

    $productoMed = Producto::firstOrCreate(
        ['nombre' => 'Amoxicilina 500mg'],
        ['ultimo_precio_venta' => 20.00, 'stock_minimo' => 5, 'unidad_medida' => 'Cápsula']
    );

    $productoInsumo = Producto::firstOrCreate(
        ['nombre' => 'Gasa Estéril 10x10'],
        ['ultimo_precio_venta' => 4.50, 'stock_minimo' => 20, 'unidad_medida' => 'Paquete']
    );

    // 1. Selector reactivo de Servicios
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalServicio')
        ->set('buscarServicio', 'Sutura')
        ->call('seleccionarServicio', $servicio->id)
        ->assertSet('nuevo_servicio_id', $servicio->id)
        ->assertSet('nuevo_servicio_costo', '120.00')
        ->call('limpiarServicioSeleccionado')
        ->assertSet('nuevo_servicio_id', null)
        ->assertSet('nuevo_servicio_costo', '0.00');

    // 2. Selector reactivo de Recetas con validación de stock
    Lote::create([
        'sucursal_id' => $proforma->sucursal_id,
        'producto_id' => $productoMed->id,
        'codigo_lote' => 'LOTE-MED-TEST-'.rand(100, 999),
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'precio_compra' => 10.00,
        'precio_venta' => 20.00,
        'fecha_vencimiento' => now()->addYear(),
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalReceta')
        ->call('seleccionarMedicamentoReceta', $productoMed->id)
        ->assertSet('receta_nuevo_producto_id', $productoMed->id)
        ->assertSet('receta_nuevo_producto_nombre', $productoMed->nombre)
        ->call('limpiarMedicamentoReceta')
        ->assertSet('receta_nuevo_producto_id', null);

    // 3. Selector reactivo de Consumos Extras
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('buscarInsumoConsumo', 'Gasa')
        ->call('seleccionarInsumoConsumo', $productoInsumo->id)
        ->assertSet('consumo_producto_id', $productoInsumo->id)
        ->assertSet('consumo_precio_unitario', '4.50')
        ->call('limpiarInsumoConsumo')
        ->assertSet('consumo_producto_id', null)
        ->assertSet('consumo_precio_unitario', '0.00');
});

test('puede visualizar pestaña de despachos y realizar despacho de farmacia directo desde proforma detalle', function () {
    $paciente = Paciente::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Montes',
        'cedula' => '55443322',
        'fecha_nacimiento' => '1988-11-20',
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $med = Producto::firstOrCreate(
        ['nombre' => 'Ketorolaco 30mg Ampolla'],
        ['ultimo_precio_venta' => 25.00, 'stock_minimo' => 5, 'unidad_medida' => 'Ampolla']
    );

    $insumo = Producto::firstOrCreate(
        ['nombre' => 'Jeringa 5ml con aguja'],
        ['ultimo_precio_venta' => 3.00, 'stock_minimo' => 50, 'unidad_medida' => 'Pieza']
    );

    $loteMed = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $med->id,
        'codigo_lote' => 'LOTE-KETO-001',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 20,
        'precio_compra' => 10.00,
        'precio_venta' => 25.00,
        'fecha_vencimiento' => now()->addMonths(6),
    ]);

    $loteInsumo = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $insumo->id,
        'codigo_lote' => 'LOTE-JER-002',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'precio_compra' => 1.50,
        'precio_venta' => 3.00,
        'fecha_vencimiento' => now()->addMonths(12),
    ]);

    // Crear receta activa
    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'activo' => true,
        'observaciones' => 'Tratamiento del dolor postquirúrgico',
    ]);

    $detalleReceta = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $med->id,
        'cantidad' => 2,
        'indicaciones' => '1 ampolla cada 12 horas IV',
        'despachado' => false,
    ]);

    // Probar componente ProformaDetalle con Tab y Modal de Despacho
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'despachos')
        ->assertSet('tab', 'despachos')
        ->assertSee('Historial de Despachos y Entregas de Farmacia')
        ->call('abrirModalDespacho')
        ->assertSet('modalDespachoProformaOpen', true)
        // Verificar que cargó la receta activa en los ítems
        ->assertSet("despachosItems.{$detalleReceta->id}.cantidad_prescrita", 2)
        ->assertSet("despachosItems.{$detalleReceta->id}.lote_id", $loteMed->id)
        ->set("despachosItems.{$detalleReceta->id}.cantidad_despachar", 2)
        // Adicionar insumo extra (Jeringa)
        ->call('seleccionarDespachoExtraProducto', $insumo->id)
        ->set('despacho_extra_lote_id', $loteInsumo->id)
        ->set('despacho_extra_cantidad', 2)
        ->set('despacho_extra_observaciones', 'Jeringa para aplicación de ketorolaco')
        ->call('agregarDespachoExtraItem')
        ->assertCount('despachosExtrasItems', 1)
        // Ejecutar despacho
        ->call('procesarDespacho')
        ->assertDispatched('swal')
        ->assertSet('modalDespachoProformaOpen', false);

    // Verificar stocks descontados
    expect($loteMed->fresh()->cantidad_actual)->toBe(18);
    expect($loteInsumo->fresh()->cantidad_actual)->toBe(48);

    // Verificar MovimientoInventario registrado
    $movs = MovimientoInventario::where('proforma_id', $proforma->id)->get();
    expect($movs)->toHaveCount(2);

    // Verificar detalle de receta marcado como despachado
    expect($detalleReceta->fresh()->despachado)->toBeTrue();

    // Verificar recalculo del costo consolidado de la proforma
    // 2 x 25.00 (ketorolaco) + 2 x 3.00 (jeringa) = 56.00
    expect($proforma->fresh()->costo_total)->toBe('56.00');
});

test('valida stock disponible antes de registrar consumo extra y descuenta inventario fefo con reintegro al eliminar', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'sucursal_id' => $this->sucursal->id,
        'nombres' => 'Paciente Stock',
        'apellido_paterno' => 'Test',
        'cedula' => 'CI-STK-'.rand(1000, 9999),
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $insumo = Producto::create([
        'nombre' => 'Gasa Quirúrgica Estéril',
        'unidad_medida' => 'Sobre',
        'ultimo_precio_venta' => 12.00,
        'stock_minimo' => 5,
    ]);

    // 1. Sin lote: Intentar agregar 1 unidad debe fallar por stock insuficiente
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_producto_id', $insumo->id)
        ->set('consumo_cantidad', 1)
        ->set('consumo_precio_unitario', '12.00')
        ->call('agregarConsumoACola')
        ->assertDispatched('swal')
        ->assertCount('cola_consumos', 0);

    // 2. Crear lote con 5 unidades
    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $insumo->id,
        'codigo_lote' => 'LOTE-GASA-01',
        'cantidad_ingresada' => 5,
        'cantidad_actual' => 5,
        'fecha_vencimiento' => now()->addMonths(6),
        'precio_compra' => 6.00,
        'precio_venta' => 12.00,
    ]);

    // Intentar agregar 10 unidades (más de las 5 disponibles) debe fallar
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_producto_id', $insumo->id)
        ->set('consumo_cantidad', 10)
        ->set('consumo_precio_unitario', '12.00')
        ->call('agregarConsumoACola')
        ->assertDispatched('swal')
        ->assertCount('cola_consumos', 0);

    // Agregar 3 unidades (disponibles) y registrar
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_producto_id', $insumo->id)
        ->set('consumo_cantidad', 3)
        ->set('consumo_precio_unitario', '12.00')
        ->call('agregarConsumoACola')
        ->assertCount('cola_consumos', 1)
        ->call('registrarConsumoExtra')
        ->assertDispatched('swal');

    // Verificar que el lote se redujo de 5 a 2
    expect($lote->fresh()->cantidad_actual)->toBe(2);

    // Verificar que se creó MovimientoInventario de Consumo Extra
    $mov = MovimientoInventario::where('proforma_id', $proforma->id)
        ->where('tipo_movimiento', 'Consumo Extra')
        ->first();
    expect($mov)->not->toBeNull();
    expect($mov->cantidad)->toBe(3);

    // Verificar que se creó el ConsumoExtra y recalculó costo (3 * 12 = 36.00)
    $consumo = ConsumoExtra::where('proforma_id', $proforma->id)->first();
    expect($consumo)->not->toBeNull();
    expect($consumo->cantidad)->toBe(3);
    expect((float) $proforma->fresh()->costo_total)->toBe(36.00);

    // 3. Eliminar ConsumoExtra (simular acción de confirmación)
    $component->call('eliminarConsumoExtra', $consumo->id)
        ->assertDispatched('swal');

    // Verificar que el stock fue reintegrado al lote (2 + 3 = 5)
    expect($lote->fresh()->cantidad_actual)->toBe(5);

    // Verificar que el costo se recalculó a 0.00
    expect((float) $proforma->fresh()->costo_total)->toBe(0.00);
});

test('puede gestionar honorarios y pagos a medicos segun ponderacion de servicios en la proforma', function () {
    $this->actingAs($this->admin);

    $rolMedico = Rol::firstOrCreate(['nombre' => 'Médico'], ['descripcion' => 'Médico Tratante']);
    $medico = User::factory()->create([
        'rol_id' => $rolMedico->id,
        'sucursal_id' => $this->sucursal->id,
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'sucursal_id' => $this->sucursal->id,
        'nombres' => 'Paciente Honorarios',
        'apellido_paterno' => 'Cirugía',
        'cedula' => 'CI-HON-'.rand(1000, 9999),
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'Pagada',
        'costo_total' => 2500.00,
    ]);

    $proforma->medicos()->attach($medico->id);

    $categoria = Categoria::create(['nombre' => 'Cirugía']);
    $servicio = Servicio::create([
        'categoria_id' => $categoria->id,
        'nombre' => 'Apendicectomía Laparoscópica',
        'precio_tentativo' => 2500.00,
        'estado' => true,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $servicio->id,
        'costo_final' => 2500.00,
        'observaciones' => 'Intervención laparoscópica realizada sin complicaciones',
        'user_id' => $this->admin->id,
    ]);

    $caja = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 5000.00,
        'fecha_apertura' => now(),
        'estado' => 'Abierta',
    ]);

    // 1. Probar que el tab y la ponderación de servicios se visualizan
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'pagos_medicos')
        ->assertSet('tab', 'pagos_medicos')
        ->assertSee('Liquidación y Honorarios de Médicos Tratantes')
        ->assertSee('Apendicectomía Laparoscópica')
        ->assertSee('Carlos Mendoza')
        // 2. Abrir modal preseleccionando al médico
        ->call('abrirModalPagoMedico', $medico->id)
        ->assertSet('modalPagoMedicoOpen', true)
        ->assertSet('pago_medico_id', $medico->id)
        ->set('pago_medico_caja_id', $caja->id)
        ->set('pago_medico_tipo_pago', 'Efectivo')
        ->set('pago_medico_monto', '1250.00')
        ->set('pago_medico_observaciones', '50% correspondiente a honorarios de cirujano principal')
        ->set('pago_medico_fecha', now()->toDateString())
        ->call('guardarPagoMedico')
        ->assertDispatched('swal')
        ->assertSet('modalPagoMedicoOpen', false);

    // Verificar en BD
    $pago = ProformaPagoMedico::where('proforma_id', $proforma->id)->where('medico_id', $medico->id)->first();
    expect($pago)->not->toBeNull();
    expect((float) $pago->monto)->toBe(1250.00);
    expect($pago->observaciones)->toBe('50% correspondiente a honorarios de cirujano principal');
    expect($proforma->fresh()->totalPagosMedicos())->toBe(1250.00);

    // 3. Modificar el pago
    $component->call('editarPagoMedico', $pago->id)
        ->assertSet('modalPagoMedicoOpen', true)
        ->assertSet('editando_pago_medico_id', $pago->id)
        ->set('pago_medico_caja_id', $caja->id)
        ->set('pago_medico_monto', '1400.00')
        ->call('guardarPagoMedico')
        ->assertDispatched('swal');

    expect((float) $pago->fresh()->monto)->toBe(1400.00);
    expect($proforma->fresh()->totalPagosMedicos())->toBe(1400.00);

    // 4. Eliminar el pago
    $component->call('eliminarPagoMedico', $pago->id)
        ->assertDispatched('swal');

    expect(ProformaPagoMedico::find($pago->id))->toBeNull();
    expect($proforma->fresh()->totalPagosMedicos())->toBe(0.00);
});

test('en estado Pagada la proforma bloquea agregar editar o eliminar items pero permite liquidar honorarios medicos', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::factory()->create();
    $servicio = Servicio::factory()->create(['precio_tentativo' => 250.00]);

    $proforma = Proforma::factory()->create([
        'paciente_id' => $paciente->id,
        'sucursal_id' => $this->sucursal->id,
        'estado' => 'Pagada',
    ]);

    $ps = ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $servicio->id,
        'costo_final' => 250.00,
    ]);

    $medico = User::factory()->create([
        'rol_id' => Rol::firstOrCreate(['nombre' => 'Médico'])->id,
        'activo' => true,
    ]);
    $proforma->medicos()->attach($medico->id);

    $caja = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 2000.00,
        'fecha_apertura' => now(),
        'estado' => 'Abierta',
    ]);

    // 1. Probar que la vista oculta los botones de accion pero muestra el de honorarios
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->assertDontSee('Agregar Procedimiento / Servicio')
        ->assertDontSee('Editar Cabecera')
        ->assertSee('Expediente Cerrado (Proforma Pagada)')
        ->call('cambiarTab', 'pagos_medicos')
        ->assertSee('Asignar Honorario');

    // 2. Probar que abrir o agregar servicio esta bloqueado por el backend
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalServicio')
        ->assertSet('modalServicioOpen', false)
        ->assertDispatched('swal')
        ->set('nuevo_servicio_id', $servicio->id)
        ->set('nuevo_servicio_costo', '250.00')
        ->call('agregarServicio')
        ->assertDispatched('swal');

    expect(ProformaServicio::where('proforma_id', $proforma->id)->count())->toBe(1);

    // 3. Probar que eliminar servicio esta bloqueado por el backend
    $component->call('eliminarServicioProforma', $ps->id)
        ->assertDispatched('swal');

    expect(ProformaServicio::find($ps->id))->not->toBeNull();

    // 4. Probar que la liquidacion de honorarios medicos SI funciona con normalidad
    $component->call('abrirModalPagoMedico', $medico->id)
        ->assertSet('modalPagoMedicoOpen', true)
        ->set('pago_medico_caja_id', $caja->id)
        ->set('pago_medico_tipo_pago', 'Efectivo')
        ->set('pago_medico_monto', '100.00')
        ->set('pago_medico_fecha', now()->toDateString())
        ->call('guardarPagoMedico')
        ->assertDispatched('swal');

    expect(ProformaPagoMedico::where('proforma_id', $proforma->id)->count())->toBe(1);
});

test('puede adjuntar archivo digital a una orden medica en proforma detalle', function () {
    $this->actingAs($this->admin);
    Storage::fake('public');

    $paciente = Paciente::factory()->create();
    $proforma = Proforma::factory()->create([
        'paciente_id' => $paciente->id,
        'sucursal_id' => $this->sucursal->id,
        'estado' => 'En Curso',
    ]);

    $tipoSolicitud = TipoSolicitud::firstOrCreate(
        ['nombre' => 'Hemograma Completo'],
        ['categoria' => 'Laboratorio', 'activo' => true]
    );

    $solicitud = ProformaSolicitud::create([
        'proforma_id' => $proforma->id,
        'tipo_solicitud_id' => $tipoSolicitud->id,
        'observaciones' => 'Urgente para control pre-operatorio',
    ]);

    $archivoFake = UploadedFile::fake()->create('resultado_lab.pdf', 500, 'application/pdf');

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalSubirArchivo', $solicitud->id)
        ->assertSet('modalArchivoSolicitudOpen', true)
        ->assertSet('solicitud_id_archivo', $solicitud->id)
        ->set('solicitud_nuevo_archivo', $archivoFake)
        ->call('guardarArchivoSolicitud')
        ->assertDispatched('swal')
        ->assertSet('modalArchivoSolicitudOpen', false);

    $solicitud->refresh();
    expect($solicitud->archivo)->not->toBeNull();
    Storage::disk('public')->assertExists($solicitud->archivo);
});

test('la edad del paciente en la proforma y reportes corresponde a la fecha de registro/ingreso y no a la fecha actual', function () {
    $this->actingAs($this->admin);

    // Paciente nacido el 15 de marzo de 2000
    $paciente = Paciente::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'cedula' => 'CI-EDAD-'.rand(1000, 9999),
        'fecha_nacimiento' => '2000-03-15',
        'genero' => 'Masculino',
    ]);

    // Proforma registrada el 20 de mayo de 2018 (edad al ingreso: 18 años)
    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => '2018-05-20 10:00:00',
        'tipo_atencion' => 'Ambulatoria',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Verificar cálculos en modelo Paciente
    expect($paciente->edadEnFecha('2018-05-20'))->toBe(18);
    expect($paciente->edadEnFecha('2010-01-01'))->toBe(9);
    expect($paciente->edadEnFecha('1999-01-01'))->toBe(0);

    // Verificar modelo Proforma
    expect($proforma->edadPacienteAlIngreso())->toBe(18);
    expect($proforma->edad_paciente)->toBe(18);

    // ProformaDetalle muestra la edad al ingreso (18 años)
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->assertSee('18 años');

    // ProformasIndex muestra la edad al ingreso (18 años)
    Livewire::test(ProformasIndex::class)
        ->assertSee('18 años');

    // PDF de detalle y resumen reflejan la edad histórica al ingreso
    $responseDetalle = $this->get(route('proformas.pdf.detalle', $proforma));
    $responseDetalle->assertOk();

    $responseResumen = $this->get(route('proformas.pdf.resumen', $proforma));
    $responseResumen->assertOk();

    // Reporte de pacientes también imputa la edad a la fecha de ingreso
    $servicioReporte = app(ReportePacientesService::class);
    $datosReporte = $servicioReporte->generar(
        fechaInicio: '2018-01-01',
        fechaFin: '2018-12-31',
        sucursalId: $this->sucursal->id
    );

    $itemProforma = collect($datosReporte['items'])->firstWhere('proforma_id', $proforma->id);
    expect($itemProforma)->not->toBeNull();
    expect($itemProforma['paciente_edad'])->toBe(18);
});

test('modal de prescripcion de receta valida stock mayor a cero y acumula medicamentos en tabla', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Elena',
        'apellido_paterno' => 'Rios',
        'cedula' => 'CI-REC-'.rand(1000, 9999),
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Producto 1: Con stock en sucursal (50 unidades)
    $prodConStock = Producto::create([
        'nombre' => 'Paracetamol 500mg',
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'stock_minimo' => 10,
    ]);
    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prodConStock->id,
        'codigo_lote' => 'LOTE-PARAC-'.rand(100, 999),
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'precio_compra' => 0.50,
        'precio_venta' => 1.50,
        'fecha_vencimiento' => now()->addMonths(12),
    ]);

    // Producto 2: Sin stock alguno (0 unidades)
    $prodSinStock = Producto::create([
        'nombre' => 'Ibuprofeno 400mg',
        'unidad_medida' => 'Comprimido',
        'ultimo_precio_venta' => 2.00,
        'stock_minimo' => 10,
    ]);

    // Intentar seleccionar producto sin stock debe bloquearse y disparar swal
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalReceta')
        ->call('seleccionarMedicamentoReceta', $prodSinStock->id)
        ->assertDispatched('swal')
        ->assertSet('receta_nuevo_producto_id', null)
        ->assertSet('receta_medicamentos', [])

        // Seleccionar producto con stock sí se permite
        ->call('seleccionarMedicamentoReceta', $prodConStock->id)
        ->assertSet('receta_nuevo_producto_id', $prodConStock->id)
        ->assertSet('receta_nuevo_producto_nombre', 'Paracetamol 500mg')
        ->assertSet('receta_nuevo_stock_disponible', 50)

        // Indicar cantidad y posología y agregar a la tabla
        ->set('receta_nuevo_cantidad', 15)
        ->set('receta_nuevo_indicaciones', '1 comp cada 8 hrs por 5 días vía oral')
        ->call('agregarMedicamentoAReceta')
        ->assertDispatched('swal')
        ->assertSet('receta_nuevo_producto_id', null)
        ->assertCount('receta_medicamentos', 1)

        // Verificar contenido en la tabla acumulada
        ->assertSee('Paracetamol 500mg')
        ->assertSee('1 comp cada 8 hrs por 5 días vía oral')

        // Quitar medicamento de la tabla
        ->call('removerMedicamentoReceta', 0)
        ->assertCount('receta_medicamentos', 0)
        ->assertSee('No se han agregado medicamentos a la receta todavía')

        // Agregar de nuevo y emitir receta activa
        ->call('seleccionarMedicamentoReceta', $prodConStock->id)
        ->set('receta_nuevo_cantidad', 10)
        ->set('receta_nuevo_indicaciones', '1 cada 8 horas')
        ->call('agregarMedicamentoAReceta')
        ->assertCount('receta_medicamentos', 1)
        ->call('prescribirReceta')
        ->assertDispatched('swal')
        ->assertSet('modalRecetaOpen', false);

    $receta = Receta::where('proforma_id', $proforma->id)->where('activo', true)->first();
    expect($receta)->not->toBeNull();
    expect($receta->detalles)->toHaveCount(1);
    expect($receta->detalles->first()->producto_id)->toBe($prodConStock->id);
    expect($receta->detalles->first()->cantidad)->toBe(10);
    expect($receta->detalles->first()->indicaciones)->toBe('1 cada 8 horas');
});

test('calcula correctamente el numero de historia clinica del paciente con fecha dmY e iniciales', function () {
    $paciente = Paciente::create([
        'nombres' => 'Luis David',
        'apellido_paterno' => 'Loza',
        'apellido_materno' => 'Mamani',
        'fecha_nacimiento' => '2000-10-23',
        'cedula' => 'CI-HC-'.rand(1000, 9999),
        'genero' => 'Masculino',
    ]);

    // 23-10-2000 -> 23102000 + L D L M -> 23102000LDLM
    expect($paciente->numero_historia_clinica)->toBe('23102000LDLM');
});

test('pestañas de evolucion, indicaciones e internacion permiten crear, editar y eliminar registros', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Mario',
        'apellido_paterno' => 'Condori',
        'fecha_nacimiento' => '1995-05-15',
        'cedula' => 'CI-EVO-'.rand(1000, 9999),
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // 1. Probar Hoja de Evolución
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'evoluciones')
        ->assertSet('tab', 'evoluciones')
        ->call('abrirModalEvolucion')
        ->assertSet('modalEvolucionOpen', true)
        ->set('evolucion_fecha_hora', '2026-11-04T10:30')
        ->set('evolucion_descripcion', '<p>Paciente hemodinámicamente estable, afebril.</p>')
        ->call('guardarEvolucion')
        ->assertDispatched('swal')
        ->assertSet('modalEvolucionOpen', false);

    $evolucion = Evolucion::where('proforma_id', $proforma->id)->first();
    expect($evolucion)->not->toBeNull();
    expect($evolucion->descripcion)->toContain('Paciente hemodinámicamente estable');

    // Editar evolución
    $component->call('abrirModalEvolucion', $evolucion->id)
        ->assertSet('evolucion_id', $evolucion->id)
        ->set('evolucion_descripcion', '<p>Paciente estable y tolerando dieta blanda.</p>')
        ->call('guardarEvolucion')
        ->assertDispatched('swal');

    expect($evolucion->fresh()->descripcion)->toContain('tolerando dieta blanda');

    // 2. Probar Hoja de Indicaciones
    $component->call('cambiarTab', 'indicaciones')
        ->assertSet('tab', 'indicaciones')
        ->call('abrirModalIndicacion')
        ->assertSet('modalIndicacionOpen', true)
        ->set('indicacion_fecha_hora', '2026-11-04T11:00')
        ->set('indicacion_descripcion', '<ol><li>Solución fisiológica 1000cc c/8h</li><li>Ceftriaxona 1g EV c/12h</li></ol>')
        ->call('guardarIndicacion')
        ->assertDispatched('swal')
        ->assertSet('modalIndicacionOpen', false);

    $indicacion = Indicacion::where('proforma_id', $proforma->id)->first();
    expect($indicacion)->not->toBeNull();
    expect($indicacion->descripcion)->toContain('Ceftriaxona 1g');

    // 3. Probar Hoja de Internación
    $component->call('cambiarTab', 'internaciones')
        ->assertSet('tab', 'internaciones')
        ->call('abrirModalInternacion')
        ->assertSet('modalInternacionOpen', true)
        ->set('internacion_fecha_hora', '2026-11-04T12:00')
        ->set('internacion_descripcion', '<p>Ingreso a sala de internación cama 304.</p>')
        ->call('guardarInternacion')
        ->assertDispatched('swal')
        ->assertSet('modalInternacionOpen', false);

    $internacion = Internacion::where('proforma_id', $proforma->id)->first();
    expect($internacion)->not->toBeNull();
    expect($internacion->descripcion)->toContain('cama 304');

    // 4. Probar eliminación
    $component->call('eliminarEvolucion', $evolucion->id)
        ->assertDispatched('swal');
    expect(Evolucion::where('proforma_id', $proforma->id)->count())->toBe(0);

    $component->call('eliminarIndicacion', $indicacion->id)
        ->assertDispatched('swal');
    expect(Indicacion::where('proforma_id', $proforma->id)->count())->toBe(0);

    $component->call('eliminarInternacion', $internacion->id)
        ->assertDispatched('swal');
    expect(Internacion::where('proforma_id', $proforma->id)->count())->toBe(0);
});

test('bloquea mutaciones en evolucion, indicaciones e internacion cuando la proforma esta pagada', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Rosa',
        'apellido_paterno' => 'Paredes',
        'cedula' => 'CI-PAG-'.rand(1000, 9999),
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'Pagada',
        'costo_total' => 100.00,
    ]);

    $itemEvo = Evolucion::create([
        'proforma_id' => $proforma->id,
        'fecha_hora' => now(),
        'descripcion' => 'Nota previa',
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalEvolucion')
        ->assertSet('modalEvolucionOpen', false)
        ->call('abrirModalIndicacion')
        ->assertSet('modalIndicacionOpen', false)
        ->call('abrirModalInternacion')
        ->assertSet('modalInternacionOpen', false)
        ->call('eliminarEvolucion', $itemEvo->id)
        ->assertDispatched('swal');

    expect($itemEvo->fresh())->not->toBeNull();
});

test('rutas de impresion pdf para evolucion, indicaciones e internacion generan documentos validos', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Juan Carlos',
        'apellido_paterno' => 'Vargas',
        'apellido_materno' => 'Rojas',
        'fecha_nacimiento' => '1990-03-25',
        'cedula' => 'CI-PDF-'.rand(1000, 9999),
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    Evolucion::create([
        'proforma_id' => $proforma->id,
        'fecha_hora' => now(),
        'descripcion' => '<p>Paciente con buena tolerancia oral.</p>',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Indicacion::create([
        'proforma_id' => $proforma->id,
        'fecha_hora' => now(),
        'descripcion' => '<p>Reposo relativo.</p>',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Internacion::create([
        'proforma_id' => $proforma->id,
        'fecha_hora' => now(),
        'descripcion' => '<p>Ingreso a sala piso 2.</p>',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // 1. PDF Evolución
    $respEvo = $this->get(route('proformas.pdf.evolucion', $proforma->id));
    $respEvo->assertStatus(200);
    $respEvo->assertHeader('content-type', 'application/pdf');

    // 2. PDF Indicación
    $respInd = $this->get(route('proformas.pdf.indicacion', $proforma->id));
    $respInd->assertStatus(200);
    $respInd->assertHeader('content-type', 'application/pdf');

    // 3. PDF Internación
    $respInt = $this->get(route('proformas.pdf.internacion', $proforma->id));
    $respInt->assertStatus(200);
    $respInt->assertHeader('content-type', 'application/pdf');
});

test('tabla de recetas muestra recetas previas y actual y genera pdf de receta con firma del medico', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Alejandro',
        'apellido_paterno' => 'Rios',
        'fecha_nacimiento' => '1985-06-20',
        'cedula' => 'CI-REC-'.rand(1000, 9999),
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $prod1 = Producto::create([
        'nombre' => 'Amoxicilina 500mg',
        'unidad_medida' => 'Cápsula',
        'ultimo_precio_venta' => 2.50,
        'stock_minimo' => 10,
    ]);

    $prod2 = Producto::create([
        'nombre' => 'Ibuprofeno 400mg',
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'stock_minimo' => 10,
    ]);

    // Receta 1 (Histórica)
    $recetaVieja = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'activo' => false,
        'observaciones' => 'Tratamiento inicial de choque',
    ]);
    RecetaDetalle::create([
        'receta_id' => $recetaVieja->id,
        'producto_id' => $prod1->id,
        'cantidad' => 10,
        'indicaciones' => '1 cada 8 horas por 3 días',
        'despachado' => true,
    ]);

    // Receta 2 (Activa)
    $recetaActiva = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'activo' => true,
        'observaciones' => 'Continuar pauta ambulatoria',
    ]);
    RecetaDetalle::create([
        'receta_id' => $recetaActiva->id,
        'producto_id' => $prod2->id,
        'cantidad' => 6,
        'indicaciones' => '1 cada 12 horas si hay dolor',
        'despachado' => false,
    ]);

    // Verificar renderizado en la pestaña de recetas
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'recetas')
        ->assertSet('tab', 'recetas')
        ->assertSee('Historial y Registro de Recetas Médicas')
        ->assertSee('Ibuprofeno 400mg')
        ->assertSee('Vigente / Activa')
        ->assertSee('Archivada / Previa');

    // Probar generación del PDF de la receta activa
    $respPdfActiva = $this->get(route('proformas.pdf.receta', [$proforma->id, $recetaActiva->id]));
    $respPdfActiva->assertStatus(200);
    $respPdfActiva->assertHeader('content-type', 'application/pdf');

    // Probar generación del PDF de la receta histórica
    $respPdfVieja = $this->get(route('proformas.pdf.receta', [$proforma->id, $recetaVieja->id]));
    $respPdfVieja->assertStatus(200);
    $respPdfVieja->assertHeader('content-type', 'application/pdf');
});

test('control de permisos granulares para evolucion, indicacion e internacion (ver, crear, editar, eliminar)', function () {
    $this->seed(PermisoSeeder::class);

    $rolOperador = Rol::firstOrCreate(['nombre' => 'Enfermería'], ['descripcion' => 'Enfermería']);
    $usuario = User::factory()->create([
        'rol_id' => $rolOperador->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'nombres' => 'Permiso',
        'apellido_paterno' => 'Test',
        'fecha_nacimiento' => '1992-01-01',
        'cedula' => 'CI-PERM-'.rand(1000, 9999),
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $evolucion = Evolucion::create([
        'proforma_id' => $proforma->id,
        'fecha_hora' => now(),
        'descripcion' => 'Nota original',
    ]);

    $this->actingAs($usuario);

    // 1. Sin permisos asignados -> cambiarTab y acciones rechazadas
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'evoluciones')
        ->assertDispatched('swal')
        ->call('abrirModalEvolucion')
        ->assertSet('modalEvolucionOpen', false)
        ->call('abrirModalIndicacion')
        ->assertSet('modalIndicacionOpen', false)
        ->call('abrirModalInternacion')
        ->assertSet('modalInternacionOpen', false)
        ->call('eliminarEvolucion', $evolucion->id)
        ->assertDispatched('swal');

    expect($evolucion->fresh())->not->toBeNull();

    // 2. Asignar permisos de ver y crear evolucion
    $usuario->permisos()->sync([
        Permiso::where('nombre', 'proformas.evolucion.ver')->value('id'),
        Permiso::where('nombre', 'proformas.evolucion.crear')->value('id'),
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'evoluciones')
        ->assertSet('tab', 'evoluciones')
        ->call('abrirModalEvolucion')
        ->assertSet('modalEvolucionOpen', true)
        // Intentar editar sin permiso de editar
        ->call('abrirModalEvolucion', $evolucion->id)
        ->assertDispatched('swal')
        // Intentar eliminar sin permiso de eliminar
        ->call('eliminarEvolucion', $evolucion->id)
        ->assertDispatched('swal');
});

test('puede abrir una proforma con historia clinica completa en proforma crear', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Valeria',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Salazar',
        'cedula' => 'CI-HC-'.rand(1000, 9999),
        'fecha_nacimiento' => '1998-04-12',
        'genero' => 'Femenino',
        'celular' => '71122334',
        'direccion' => 'Av. América #123',
    ]);

    Livewire::test(ProformaCrear::class)
        ->set('paciente_id', $paciente->id)
        ->set('tipo_atencion', 'Internacion')
        ->set('fecha_ingreso', now()->format('Y-m-d\TH:i'))
        ->set('pieza', 'Sala 1 - Cama 4')
        ->set('procedencia', 'Cochabamba')
        ->set('residencia', 'Av. América #123')
        ->set('fuente_informacion', 'Paciente')
        ->set('motivo_consulta', 'Dolor abdominal agudo')
        ->set('enfermedad_actual', 'Cuadro de 24 horas de evolución con dolor en FID')
        ->set('antecedentes_patologicos', 'Alergia a penicilina')
        ->set('antecedentes_no_patologicos', 'No fuma, no bebe')
        ->set('menarca', '12')
        ->set('fum', '10/10/2026')
        ->set('gestas', '1')
        ->set('partos', '1')
        ->set('cesareas', '0')
        ->set('abortos', '0')
        ->set('pap', 'Negativo')
        ->set('pa', '110/70')
        ->set('fc', '78')
        ->set('fr', '16')
        ->set('temperatura', '36.8')
        ->set('sato2', '98')
        ->set('peso', '60')
        ->set('talla', '165')
        ->set('examen_fisico', 'Abdomen doloroso a la palpación en fosa ilíaca derecha')
        ->set('diagnostico', 'Apendicitis aguda presuntiva')
        ->set('conducta', 'Internación y valoración por cirugía general')
        ->call('abrirProforma')
        ->assertRedirect();

    $proforma = Proforma::where('paciente_id', $paciente->id)->latest('id')->first();
    expect($proforma)->not->toBeNull();
    expect($proforma->historiaClinica)->not->toBeNull();
    expect($proforma->historiaClinica->procedencia)->toBe('Cochabamba');
    expect($proforma->historiaClinica->pa)->toBe('110/70');
    expect($proforma->historiaClinica->imc)->not->toBeEmpty();
    expect($proforma->historiaClinica->diagnostico)->toBe('Apendicitis aguda presuntiva');
});

test('puede consultar y editar la historia clinica desde proforma detalle', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Gabriel',
        'apellido_paterno' => 'Perez',
        'cedula' => 'CI-EDITHC-'.rand(1000, 9999),
        'fecha_nacimiento' => '1992-06-18',
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Ambulatoria',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalHistoriaClinica')
        ->assertSet('modalHistoriaClinicaOpen', true)
        ->set('hc_procedencia', 'Santa Cruz')
        ->set('hc_pa', '125/80')
        ->set('hc_fc', '80')
        ->set('hc_peso', '72')
        ->set('hc_talla', '175')
        ->set('hc_motivo_consulta', 'Chequeo rutinario')
        ->set('hc_diagnostico', 'Paciente clínicamente sano')
        ->set('hc_conducta', 'Control preventivo anual')
        ->call('guardarHistoriaClinica')
        ->assertDispatched('swal');

    $hc = HistoriaClinica::where('proforma_id', $proforma->id)->first();
    expect($hc)->not->toBeNull();
    expect($hc->procedencia)->toBe('Santa Cruz');
    expect($hc->pa)->toBe('125/80');
    expect($hc->diagnostico)->toBe('Paciente clínicamente sano');
    expect($proforma->fresh()->diagnostico)->toBe('Paciente clínicamente sano');
});

test('puede generar el documento oficial pdf de la historia clinica', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Elena',
        'apellido_paterno' => 'Rojas',
        'cedula' => 'CI-PDFHC-'.rand(1000, 9999),
        'fecha_nacimiento' => '2001-08-25',
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    HistoriaClinica::create([
        'proforma_id' => $proforma->id,
        'procedencia' => 'Oruro',
        'motivo_consulta' => 'Cefalea intensa',
        'enfermedad_actual' => 'Episodio recurrente de migraña',
        'pa' => '120/80',
        'fc' => '72',
        'diagnostico' => 'Migraña sin aura',
        'conducta' => 'Analgésicos y reposo',
    ]);

    $response = $this->get(route('proformas.pdf.historia_clinica', $proforma->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('puede abrir modal, registrar y editar la epicrisis en proforma detalle', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Mario',
        'apellido_paterno' => 'Barrientos',
        'cedula' => 'CI-EPI-'.rand(1000, 9999),
        'fecha_nacimiento' => '1990-05-15',
        'genero' => 'Masculino',
        'telefono' => '70012345',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now()->subDays(3),
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Sala 4 - Cama 2',
        'diagnostico' => 'Apendicitis aguda',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Abrir modal y guardar Epicrisis
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalEpicrisis')
        ->assertSet('modalEpicrisisOpen', true)
        ->assertSet('epi_cama', 'Sala 4 - Cama 2')
        ->assertSet('epi_diagnostico_ingreso', 'Apendicitis aguda')
        ->set('epi_servicio', 'Cirugía General')
        ->set('epi_fecha_egreso', now()->format('Y-m-d\TH:i'))
        ->set('epi_condiciones_ingreso', 'Fiebre, dolor abdominal agudo en FID')
        ->set('epi_condiciones_egreso', 'Herida quirúrgica limpia, afebril, hemodinámicamente estable')
        ->set('epi_diagnostico_egreso', 'Post operado de apendicectomía laparoscópica')
        ->set('epi_causa_egreso', 'Alta médica por mejoría')
        ->set('epi_procedimientos', 'Apendicectomía por laparoscopía')
        ->set('epi_indicaciones_alta', 'Ciprofloxacino 500mg c/12h por 5 días, reposo relativo')
        ->set('epi_proxima_cita', 'En 7 días para retiro de puntos')
        ->call('guardarEpicrisis')
        ->assertDispatched('swal')
        ->assertSet('modalEpicrisisOpen', false);

    $epicrisis = Epicrisis::where('proforma_id', $proforma->id)->first();
    expect($epicrisis)->not->toBeNull();
    expect($epicrisis->servicio)->toBe('Cirugía General');
    expect($epicrisis->causa_egreso)->toBe('Alta médica por mejoría');
    expect($epicrisis->diagnostico_egreso)->toBe('Post operado de apendicectomía laparoscópica');

    // Ahora editar la epicrisis
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalEpicrisis')
        ->assertSet('modalEpicrisisOpen', true)
        ->assertSet('epi_servicio', 'Cirugía General')
        ->set('epi_observaciones', 'Evolución favorable sin complicaciones postoperatorias')
        ->call('guardarEpicrisis')
        ->assertDispatched('swal');

    expect($epicrisis->fresh()->observaciones)->toBe('Evolución favorable sin complicaciones postoperatorias');
});

test('puede generar el documento oficial pdf de la epicrisis', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Rosa',
        'apellido_paterno' => 'Quiroga',
        'cedula' => 'CI-EPIDOC-'.rand(1000, 9999),
        'fecha_nacimiento' => '1988-12-04',
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now()->subDays(2),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    Epicrisis::create([
        'proforma_id' => $proforma->id,
        'cama' => 'Cama 101',
        'servicio' => 'Medicina Interna',
        'fecha_egreso' => now(),
        'condiciones_ingreso' => 'Paciente en regular estado',
        'diagnostico_ingreso' => 'Neumonía adquirida en la comunidad',
        'condiciones_egreso' => 'Buena mecánica ventilatoria, afebril',
        'diagnostico_egreso' => 'Neumonía resuelta',
        'causa_egreso' => 'Alta médica por mejoría',
        'estudios_complementarios' => 'Radiografía de tórax control: campos pulmonares limpios',
        'tratamiento_realizado' => 'Ceftriaxona EV por 48 horas',
        'indicaciones_alta' => 'Amoxicilina + Clavulánico 875/125mg VO c/8h por 5 días',
        'proxima_cita' => 'Control por consulta externa en 10 días',
        'creado_por' => $this->admin->id,
    ]);

    $response = $this->get(route('proformas.pdf.epicrisis', $proforma->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('puede abrir pestaña, registrar, encolar medicamentos y guardar descargo de quirofano en proforma detalle', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'cedula' => 'CI-CQ-'.rand(1000, 9999),
        'fecha_nacimiento' => '1985-03-20',
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Quirófano 1',
        'diagnostico' => 'Colecistitis aguda litiásica',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Crear sección de quirófano
    $seccionQuirofano = Seccion::firstOrCreate(
        ['nombre' => 'Quirofano', 'sucursal_id' => $this->sucursal->id],
        ['activo' => true]
    );

    // Crear producto con stock en lote
    $producto = Producto::factory()->create([
        'nombre' => 'Propofol 1% 20ml',
        'ultimo_precio_venta' => 45.00,
    ]);

    Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'numero_lote' => 'LOT-PROP-'.rand(100, 999),
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 15,
        'precio_compra' => 30.00,
        'precio_venta' => 45.00,
    ]);

    $cirujano = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $anestesiologo = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('cambiarTab', 'cirugias')
        ->assertSet('tab', 'cirugias')
        ->call('abrirModalNuevaCirugia')
        ->assertSet('modalCirugiaOpen', true)
        ->set('cir_especialidad', 'Cirugía Laparoscópica')
        ->set('cir_cirugia_efectuada', 'Colecistectomía laparoscópica')
        ->set('cir_tipo_cirugia', 'Mayor')
        ->set('cir_cirujano_id', $cirujano->id)
        ->set('cir_anestesiologo_id', $anestesiologo->id)
        ->set('cir_torre_laparoscopia', true)
        ->set('cir_electrobisturi', true)
        ->call('seleccionarMedicamentoCirugia', $producto->id)
        ->assertSet('cir_medicamento_costo_unitario', '45.00')
        ->set('cir_medicamento_seccion_id', $seccionQuirofano->id)
        ->set('cir_medicamento_area', 'Anestesiología')
        ->set('cir_medicamento_cantidad', 2)
        ->set('cir_medicamento_indicaciones', 'Inducción anestésica')
        ->call('agregarMedicamentoACirugia')
        ->assertCount('cola_medicamentos_cirugia', 1)
        ->call('guardarCirugia')
        ->assertDispatched('swal')
        ->assertSet('modalCirugiaOpen', false);

    // Verificar en BD
    $cirugia = Cirugia::where('proforma_id', $proforma->id)->first();
    expect($cirugia)->not->toBeNull();
    expect($cirugia->cirugia_efectuada)->toBe('Colecistectomía laparoscópica');
    expect($cirugia->cirujano_id)->toBe($cirujano->id);
    expect($cirugia->anestesiologo_id)->toBe($anestesiologo->id);
    expect($cirugia->torre_laparoscopia)->toBeTrue();

    // Verificar la receta quirúrgica vinculada
    $recetaCirugia = Receta::where('cirugia_id', $cirugia->id)->first();
    expect($recetaCirugia)->not->toBeNull();
    expect($recetaCirugia->tipo)->toBe('Quirófano');
    expect($recetaCirugia->detalles()->count())->toBe(1);

    $detalle = $recetaCirugia->detalles()->first();
    expect($detalle->producto_id)->toBe($producto->id);
    expect($detalle->seccion_id)->toBe($seccionQuirofano->id);
    expect($detalle->area_quirurgica)->toBe('Anestesiología');
    expect($detalle->cantidad)->toBe(2);
    expect((float) $detalle->costo_unitario)->toBe(45.00);
});

test('puede generar los documentos pdf de anestesiologia, enfermeria y descargo completo de quirofano', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Patricia',
        'apellido_paterno' => 'Vargas',
        'cedula' => 'CI-PDFCQ-'.rand(1000, 9999),
        'fecha_nacimiento' => '1992-07-14',
        'genero' => 'Femenino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $cirugia = Cirugia::create([
        'proforma_id' => $proforma->id,
        'numero' => 'CQ-TEST-01',
        'fecha' => now(),
        'especialidad' => 'Cirugía General',
        'cirugia_efectuada' => 'Apendicectomía Laparoscópica',
        'diagnostico_operacion' => 'Apendicitis aguda flegmonosa',
        'tipo_cirugia' => 'Media',
        'tipo_anestesia' => 'General',
        'cirujano_id' => $this->admin->id,
        'torre_laparoscopia' => true,
    ]);

    $seccion = Seccion::firstOrCreate(
        ['nombre' => 'Farmacia Central', 'sucursal_id' => $this->sucursal->id],
        ['activo' => true]
    );

    $prodAnestesia = Producto::factory()->create(['nombre' => 'Fentanilo 0.5mg/10ml', 'ultimo_precio_venta' => 25.50]);
    $prodEnfermeria = Producto::factory()->create(['nombre' => 'Vicryl 3-0 con aguja', 'ultimo_precio_venta' => 18.00]);

    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'cirugia_id' => $cirugia->id,
        'user_id' => $this->admin->id,
        'fecha_prescripcion' => now(),
        'tipo' => 'Quirófano',
        'estado' => 'Activa',
    ]);

    $receta->detalles()->create([
        'producto_id' => $prodAnestesia->id,
        'seccion_id' => $seccion->id,
        'area_quirurgica' => 'Anestesiología',
        'cantidad' => 2,
        'costo_unitario' => 25.50,
    ]);

    $receta->detalles()->create([
        'producto_id' => $prodEnfermeria->id,
        'seccion_id' => $seccion->id,
        'area_quirurgica' => 'Enfermería',
        'cantidad' => 3,
        'costo_unitario' => 18.00,
    ]);

    // Hoja Anestesiología
    $resAnestesia = $this->get(route('proformas.pdf.cirugia_anestesiologia', [$proforma->id, $cirugia->id]));
    $resAnestesia->assertStatus(200);
    $resAnestesia->assertHeader('content-type', 'application/pdf');

    // Hoja Enfermería
    $resEnfermeria = $this->get(route('proformas.pdf.cirugia_enfermeria', [$proforma->id, $cirugia->id]));
    $resEnfermeria->assertStatus(200);
    $resEnfermeria->assertHeader('content-type', 'application/pdf');

    // Descargo Completo (ambas hojas)
    $resCompleto = $this->get(route('proformas.pdf.cirugia', [$proforma->id, $cirugia->id]));
    $resCompleto->assertStatus(200);
    $resCompleto->assertHeader('content-type', 'application/pdf');
});

test('autocompleta convenio institucional en modal de cirugia si el paciente tiene institucion asignada', function () {
    $this->actingAs($this->admin);

    $institucion = Institucion::create([
        'nombre' => 'Seguro Salud BISA',
        'estado' => 'Activo',
    ]);

    $pacienteConConvenio = Paciente::create([
        'institucion_id' => $institucion->id,
        'nombres' => 'Fernando',
        'apellido_paterno' => 'Guzman',
        'cedula' => 'CI-CONV-'.rand(1000, 9999),
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $pacienteConConvenio->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Al abrir el modal de nueva cirugía debe autocompletar automáticamente el nombre del convenio
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalNuevaCirugia')
        ->assertSet('cir_es_convenio', true)
        ->assertSet('cir_es_institucional', false)
        ->assertSet('cir_convenio_nombre', 'Seguro Salud BISA')
        // Si el usuario conmuta a Institucional
        ->set('cir_es_institucional', true)
        ->assertSet('cir_es_convenio', false)
        ->assertSet('cir_convenio_nombre', '')
        // Si vuelve a conmutar a Convenio se debe re-autocompletar con la institución del paciente
        ->set('cir_es_convenio', true)
        ->assertSet('cir_es_institucional', false)
        ->assertSet('cir_convenio_nombre', 'Seguro Salud BISA');
});
