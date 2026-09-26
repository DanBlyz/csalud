<?php

use App\Livewire\Pacientes\PacientesIndex;
use App\Livewire\Proformas\ProformaCrear;
use App\Livewire\Proformas\ProformaDetalle;
use App\Models\Categoria;
use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaCalendario;
use App\Models\ProformaServicio;
use App\Models\ProformaSolicitud;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\TipoSolicitud;
use App\Models\User;
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
        ->set('antecedentes_alergias', 'Alérgico a sulfas')
        ->call('guardar')
        ->assertDispatched('swal');

    $paciente = Paciente::where('cedula', $cedula)->first();
    expect($paciente)->not->toBeNull();
    expect($paciente->nombres)->toBe('Juan Pablo');
    expect($paciente->edad)->not->toBeNull();

    // Editar paciente
    Livewire::test(PacientesIndex::class)
        ->call('abrirModalEditar', $paciente->id)
        ->set('nombres', 'Juan Pablo Modificado')
        ->call('guardar')
        ->assertDispatched('swal');

    expect($paciente->fresh()->nombres)->toBe('Juan Pablo Modificado');
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

    // 5. Cargar Consumo Extra (Insumo de piso)
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

    // 2. Selector reactivo de Recetas (por fila)
    Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalReceta')
        ->call('seleccionarMedicamentoReceta', 0, $productoMed->id)
        ->assertSet('receta_medicamentos.0.producto_id', $productoMed->id)
        ->assertSet('receta_medicamentos.0.producto_nombre', $productoMed->nombre)
        ->call('limpiarMedicamentoReceta', 0)
        ->assertSet('receta_medicamentos.0.producto_id', '');

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
