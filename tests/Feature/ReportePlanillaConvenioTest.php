<?php

use App\Livewire\Administracion\ReportesIndex;
use App\Models\Categoria;
use App\Models\ConsumoExtra;
use App\Models\Institucion;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaServicio;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\ReportePlanillaConvenioService;
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

    $this->institucion = Institucion::create([
        'nombre' => 'COTEL',
        'descripcion' => 'Cooperativa de Telecomunicaciones',
        'estado' => 'Activo',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->seccionFarmacia = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Farmacia Central',
        'descripcion' => 'Almacén general y farmacia',
        'es_almacen_principal' => true,
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->seccionQuirofano = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Quirófano',
        'descripcion' => 'Piso de cirugía',
        'es_almacen_principal' => false,
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->categoria = Categoria::create([
        'nombre' => 'Servicios Médicos',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->servicioConsulta = Servicio::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Consulta Médica General',
        'descripcion' => 'Evaluación ambulatoria',
        'precio_tentativo' => 100.00,
        'estado' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->servicioCirugia = Servicio::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Uso de Quirófano',
        'descripcion' => 'Derecho de sala quirúrgica',
        'precio_tentativo' => 500.00,
        'estado' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Bago',
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->productoSuero = Producto::create([
        'codigo' => 'MED-001',
        'nombre' => 'Solución Fisiológica 1000ml',
        'unidad_medida' => 'Frasco',
        'marca_id' => $this->marca->id,
        'ultimo_precio_compra' => 10.00,
        'ultimo_precio_venta' => 25.00,
        'stock_minimo' => 10,
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->loteSuero = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->productoSuero->id,
        'codigo_lote' => 'LOT-2026-A',
        'fecha_vencimiento' => Carbon::now()->addYear(),
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'precio_compra' => 10.00,
        'precio_venta' => 25.00,
        'usuario_creador_id' => $this->admin->id,
    ]);
});

test('servicio de planilla genera la estructura matricial con servicios e insumos por sección', function () {
    $paciente1 = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Victor',
        'apellido_paterno' => 'Paucara',
        'apellido_materno' => 'Quispe',
        'cedula' => '1234567',
        'fecha_nacimiento' => '1985-05-10',
        'genero' => 'M',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $paciente2 = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Juan',
        'apellido_paterno' => 'Zamora',
        'apellido_materno' => 'Villca',
        'cedula' => '7654321',
        'fecha_nacimiento' => '1990-08-15',
        'genero' => 'M',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Proforma de Agosto 2026 para Paciente 1
    $proforma1 = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente1->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => Carbon::create(2026, 8, 10, 9, 0),
        'fecha_salida' => Carbon::create(2026, 8, 10, 10, 0),
        'costo_total' => 125.00,
        'estado' => 'Activa',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Servicio en proforma 1: Consulta 100.00
    ProformaServicio::create([
        'proforma_id' => $proforma1->id,
        'servicio_id' => $this->servicioConsulta->id,
        'costo_final' => 100.00,
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Insumo de Farmacia Central en proforma 1: 1 Suero a 25.00
    ConsumoExtra::create([
        'proforma_id' => $proforma1->id,
        'producto_id' => $this->productoSuero->id,
        'cantidad' => 1,
        'precio_unitario' => 25.00,
        'user_id' => $this->admin->id,
        'observaciones' => '[Farmacia Central] Despacho de suero',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Proforma de Agosto 2026 para Paciente 2
    $proforma2 = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente2->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => Carbon::create(2026, 8, 15, 8, 0),
        'fecha_salida' => Carbon::create(2026, 8, 18, 12, 0),
        'costo_total' => 550.00,
        'estado' => 'Activa',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Servicio en proforma 2: Quirófano 500.00
    ProformaServicio::create([
        'proforma_id' => $proforma2->id,
        'servicio_id' => $this->servicioCirugia->id,
        'costo_final' => 500.00,
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Insumo de Quirófano en proforma 2: 2 Sueros a 25.00 = 50.00
    ConsumoExtra::create([
        'proforma_id' => $proforma2->id,
        'producto_id' => $this->productoSuero->id,
        'cantidad' => 2,
        'precio_unitario' => 25.00,
        'user_id' => $this->admin->id,
        'observaciones' => '[Quirófano] Material quirúrgico',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $service = new ReportePlanillaConvenioService;
    $datos = $service->generar(
        institucionId: $this->institucion->id,
        mes: 8,
        anio: 2026,
        sucursalId: $this->sucursal->id,
        soloAtendidos: true
    );

    expect($datos['mes'])->toBe(8)
        ->and($datos['mes_nombre'])->toBe('AGOSTO')
        ->and($datos['anio'])->toBe(2026)
        ->and($datos['institucion']->id)->toBe($this->institucion->id)
        ->and(count($datos['columnas_pacientes']))->toBe(2);

    $col1 = $datos['columnas_pacientes'][0];
    expect($col1['paciente_nombre'])->toContain('VICTOR PAUCARA')
        ->and($col1['proforma_numero'])->toBe((string) $proforma1->id)
        ->and($col1['valores']['serv_'.$this->servicioConsulta->id])->toBe(100.00)
        ->and($col1['valores']['sec_'.$this->seccionFarmacia->id])->toBe(25.00)
        ->and($col1['total'])->toBe(125.00)
        ->and($col1['total_a_cancelar'])->toBe(125.00);

    $col2 = $datos['columnas_pacientes'][1];
    expect($col2['paciente_nombre'])->toContain('JUAN ZAMORA')
        ->and($col2['datos_generales'])->toBe('15/08/2026 A 18/08/2026')
        ->and($col2['valores']['serv_'.$this->servicioCirugia->id])->toBe(500.00)
        ->and($col2['valores']['sec_'.$this->seccionQuirofano->id])->toBe(50.00)
        ->and($col2['total'])->toBe(550.00);

    expect($datos['gran_total'])->toBe(675.00)
        ->and($datos['gran_total_a_cancelar'])->toBe(675.00);

    // Comprobar exportación a PhpSpreadsheet
    $spreadsheet = $service->exportarExcel($datos);
    $sheet = $spreadsheet->getActiveSheet();

    expect($sheet->getTitle())->toContain('Planilla_AGOSTO')
        ->and($sheet->getCell('A2')->getValue())->toContain('PLANILLA PACIENTES ATENDIDOS COTEL MES DE AGOSTO 2026')
        ->and($sheet->getCell('B5')->getValue())->toBe('NO. PROF.')
        ->and($sheet->getCell('B6')->getValue())->toBe('NOMBRE PACIENTE')
        ->and($sheet->getCell('B7')->getValue())->toBe('NUMERO DE FOLEADO')
        ->and($sheet->getCell('B8')->getValue())->toBe('DATOS GENERALES');
});

test('servicio incluye pacientes sin atenciones cuando solo_atendidos es falso', function () {
    $pacienteConAtencion = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Atendido',
        'apellido_paterno' => 'Perez',
        'apellido_materno' => 'Lopez',
        'cedula' => '1111111',
        'genero' => 'M',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $pacienteSinAtencion = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'No Atendido',
        'apellido_paterno' => 'Gomez',
        'apellido_materno' => 'Rios',
        'cedula' => '2222222',
        'genero' => 'F',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $pacienteConAtencion->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => Carbon::create(2026, 8, 5),
        'costo_total' => 50.00,
        'estado' => 'Activa',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $service = new ReportePlanillaConvenioService;

    // Con solo atendidos = true
    $resSoloAtendidos = $service->generar($this->institucion->id, 8, 2026, soloAtendidos: true);
    expect(count($resSoloAtendidos['columnas_pacientes']))->toBe(1);

    // Con solo atendidos = false
    $resTodos = $service->generar($this->institucion->id, 8, 2026, soloAtendidos: false);
    expect(count($resTodos['columnas_pacientes']))->toBe(2);

    $nombres = collect($resTodos['columnas_pacientes'])->pluck('paciente_nombre')->toArray();
    expect($nombres)->toContain('ATENDIDO PEREZ LOPEZ')
        ->and($nombres)->toContain('NO ATENDIDO GOMEZ RIOS');
});

test('componente ReportesIndex permite cambiar a convenios, previsualizar y descargar', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Flores',
        'cedula' => '8888888',
        'genero' => 'M',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => Carbon::create(2026, 8, 12),
        'costo_total' => 100.00,
        'estado' => 'Activa',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Livewire::test(ReportesIndex::class)
        ->assertSet('reporteActivo', 'kardex')
        ->assertSee('Criterios del Reporte de Kardex y Movimientos')
        ->call('cambiarTipoReporte', 'convenios')
        ->assertSet('reporteActivo', 'convenios')
        ->assertSee('Criterios de la Planilla Mensual por Institución / Convenio')
        ->assertDontSee('Criterios del Reporte de Kardex y Movimientos')
        ->assertDontSee('Criterios del Reporte de Ingresos, Salidas y Habitaciones')
        ->set('conv_institucion_id', $this->institucion->id)
        ->set('conv_mes', 8)
        ->set('conv_anio', 2026)
        ->call('previsualizarReporteConvenio')
        ->assertSet('conv_reporteGenerado', true)
        ->assertSee('PLANILLA PACIENTES ATENDIDOS COTEL MES DE AGOSTO 2026')
        ->assertSee('CARLOS MENDOZA FLORES')
        ->call('descargarExcelConvenio')
        ->assertFileDownloaded('Planilla_Pacientes_cotel_AGOSTO_2026.xlsx');
});

test('ruta directa para descargar planilla en excel funciona con permisos requeridos', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('administracion.reportes.convenios.excel', [
        'institucion_id' => $this->institucion->id,
        'mes' => 8,
        'anio' => 2026,
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('openxmlformats-officedocument.spreadsheetml.sheet');
});
