<?php

use App\Livewire\Administracion\ReportesIndex;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\ReportePacientesService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador Total']
    );

    DB::table('permisos')->insertOrIgnore([
        'id' => 1,
        'nombre' => 'gestion-usuarios',
        'descripcion' => 'Administración general del sistema',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sede Central',
        'ciudad' => 'La Paz',
        'direccion' => 'Av. 6 de Agosto #100',
        'telefono' => '22114455',
        'es_matriz' => true,
        'activo' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    // Crear Instituciones de prueba
    $this->institucionA = Institucion::create([
        'nombre' => 'Seguro Cordes',
        'descripcion' => 'Caja de Salud Cordes',
        'estado' => true,
    ]);

    $this->institucionB = Institucion::create([
        'nombre' => 'Seguro Bancario',
        'descripcion' => 'Caja de Salud de la Banca Privada',
        'estado' => true,
    ]);

    // Crear Pacientes
    $this->pacienteCordes = Paciente::create([
        'institucion_id' => $this->institucionA->id,
        'nombres' => 'Mario',
        'apellido_paterno' => 'Vargas',
        'cedula' => '4455661',
        'celular' => '70112233',
    ]);

    $this->pacienteParticular = Paciente::create([
        'institucion_id' => null, // Sin seguro/convenio
        'nombres' => 'Elena',
        'apellido_paterno' => 'Rios',
        'cedula' => '9988772',
        'celular' => '71223344',
    ]);

    // Proforma 1: Mario en Habitación 201 (Internación, con salida)
    $this->proforma1 = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->pacienteCordes->id,
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Sala 201 - Cama A',
        'fecha_ingreso' => Carbon::now()->subDays(5),
        'fecha_salida' => Carbon::now()->subDays(1),
        'motivo_consulta' => 'Dolor abdominal agudo',
        'diagnostico' => 'Gastroenteritis moderada',
        'estado' => 'Pagada',
        'costo_total' => 800.00,
    ]);

    // Proforma 2: Elena en Habitación 105 (Internación activa / en piso)
    $this->proforma2 = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->pacienteParticular->id,
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Sala 105 - Cama B',
        'fecha_ingreso' => Carbon::now()->subDays(2),
        'fecha_salida' => null, // En curso
        'motivo_consulta' => 'Observación postoperatoria',
        'diagnostico' => 'Apendicectomía laparoscópica',
        'estado' => 'Confirmada',
        'costo_total' => 1500.00,
    ]);

    // Proforma 3: Mario en Ambulatoria (sin pieza asignada)
    $this->proforma3 = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->pacienteCordes->id,
        'tipo_atencion' => 'Ambulatoria',
        'pieza' => null,
        'fecha_ingreso' => Carbon::now()->subDay(),
        'fecha_salida' => Carbon::now()->subDay(),
        'motivo_consulta' => 'Control de rutina',
        'diagnostico' => 'Evaluación post alta',
        'estado' => 'Pagada',
        'costo_total' => 120.00,
    ]);
});

test('servicio de reporte de pacientes genera resumen y detalles correctamente', function () {
    $service = new ReportePacientesService;

    $reporte = $service->generar(
        fechaInicio: Carbon::now()->subDays(7)->toDateString(),
        fechaFin: Carbon::now()->toDateString(),
        institucionId: null,
        sucursalId: $this->sucursal->id,
        tipoFiltro: 'todos'
    );

    expect($reporte['totales']['total_registros'])->toBe(3)
        ->and($reporte['totales']['hospitalarios'])->toBe(2)
        ->and($reporte['totales']['ambulatorios'])->toBe(1)
        ->and($reporte['totales']['internados_activos'])->toBe(1)
        ->and(count($reporte['items']))->toBe(3);

    // Verificar que las piezas / habitaciones se reporten
    $piezas = array_column($reporte['items'], 'pieza');
    expect($piezas)->toContain('Sala 201 - Cama A')
        ->and($piezas)->toContain('Sala 105 - Cama B')
        ->and($piezas)->toContain('Ambulatorio');
});

test('servicio de reporte filtra por institucion especifica', function () {
    $service = new ReportePacientesService;

    // Filtrar solo por Seguro Cordes
    $reporteCordes = $service->generar(
        fechaInicio: Carbon::now()->subDays(7)->toDateString(),
        fechaFin: Carbon::now()->toDateString(),
        institucionId: $this->institucionA->id
    );

    expect($reporteCordes['totales']['total_registros'])->toBe(2)
        ->and($reporteCordes['institucion_nombre'])->toBe('Seguro Cordes');

    foreach ($reporteCordes['items'] as $item) {
        expect($item['institucion_nombre'])->toBe('Seguro Cordes');
    }

    // Filtrar solo por Particulares (-1)
    $reporteParticulares = $service->generar(
        fechaInicio: Carbon::now()->subDays(7)->toDateString(),
        fechaFin: Carbon::now()->toDateString(),
        institucionId: -1
    );

    expect($reporteParticulares['totales']['total_registros'])->toBe(1);
    expect($reporteParticulares['items'][0]['institucion_nombre'])->toBe('Particular')
        ->and($reporteParticulares['items'][0]['pieza'])->toBe('Sala 105 - Cama B');
});

test('componente ReportesIndex permite interactuar con el reporte de pacientes', function () {
    $this->actingAs($this->admin);

    Livewire::test(ReportesIndex::class)
        ->call('cambiarTipoReporte', 'pacientes')
        ->assertSet('reporteActivo', 'pacientes')
        ->set('pac_fecha_inicio', Carbon::now()->subDays(7)->toDateString())
        ->set('pac_fecha_fin', Carbon::now()->toDateString())
        ->set('pac_institucion_id', $this->institucionA->id)
        ->call('previsualizarReportePacientes')
        ->assertSet('pac_reporteGenerado', true)
        ->assertSee('Seguro Cordes')
        ->assertSee('Sala 201 - Cama A');
});

test('controlador genera y descarga el pdf del reporte de pacientes', function () {
    $this->actingAs($this->admin);

    $url = route('administracion.reportes.pacientes.pdf', [
        'fecha_inicio' => Carbon::now()->subDays(7)->toDateString(),
        'fecha_fin' => Carbon::now()->toDateString(),
        'institucion_id' => $this->institucionA->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_filtro' => 'todos',
    ]);

    $response = $this->get($url);

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
