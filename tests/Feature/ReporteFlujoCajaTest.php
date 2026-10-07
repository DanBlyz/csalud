<?php

use App\Livewire\Administracion\ReportesIndex;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\ConsumoExtra;
use App\Models\Marca;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaServicio;
use App\Models\Rol;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\ReporteFlujoCajaService;
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

    $this->categoria = Categoria::create([
        'nombre' => 'Servicios Médicos',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->servicioConsulta = Servicio::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Consultas Clínicas',
        'descripcion' => 'Evaluación ambulatoria',
        'precio_tentativo' => 100.00,
        'estado' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Bago',
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);

    $this->producto = Producto::create([
        'codigo' => 'MED-010',
        'nombre' => 'Paracetamol 500mg',
        'unidad_medida' => 'Tableta',
        'marca_id' => $this->marca->id,
        'ultimo_precio_compra' => 2.00,
        'ultimo_precio_venta' => 5.00,
        'stock_minimo' => 10,
        'activo' => true,
        'usuario_creador_id' => $this->admin->id,
    ]);
});

test('servicio de flujo de caja genera la matriz de ingresos, egresos y saldos diarios', function () {
    // 1. Crear caja del 4 de septiembre 2026
    $caja1 = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 100.00,
        'fecha_apertura' => Carbon::create(2026, 9, 4, 8, 30),
        'estado' => 'Cerrada',
        'usuario_creador_id' => $this->admin->id,
    ]);

    $paciente = Paciente::create([
        'nombres' => 'Juan',
        'apellido_paterno' => 'Perez',
        'cedula' => '1234567',
        'genero' => 'M',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Proforma con servicio (100) y consumos/medicamentos (50) = 150 total
    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => Carbon::create(2026, 9, 4),
        'costo_total' => 150.00,
        'estado' => 'Pagada',
        'usuario_creador_id' => $this->admin->id,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicioConsulta->id,
        'costo_final' => 100.00,
        'usuario_creador_id' => $this->admin->id,
    ]);

    ConsumoExtra::create([
        'proforma_id' => $proforma->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 10,
        'precio_unitario' => 5.00,
        'user_id' => $this->admin->id,
        'observaciones' => '[Farmacia Central] Despacho medicamentos',
        'usuario_creador_id' => $this->admin->id,
    ]);

    // Pago de proforma en caja 1: 150.00
    Pago::create([
        'caja_id' => $caja1->id,
        'proforma_id' => $proforma->id,
        'tipo_movimiento' => 'Ingreso Proforma',
        'categoria' => 'Proforma',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Cobro Proforma #1',
        'monto' => 150.00,
        'user_id' => $this->admin->id,
    ]);

    // Ingreso extra en caja 1: Alquileres 80.00
    Pago::create([
        'caja_id' => $caja1->id,
        'proforma_id' => null,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Alquiler',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Alquiler de auditorio',
        'monto' => 80.00,
        'user_id' => $this->admin->id,
    ]);

    // Egreso en caja 1: Servicios Básicos 40.00
    Pago::create([
        'caja_id' => $caja1->id,
        'proforma_id' => null,
        'tipo_movimiento' => 'Egreso Caja',
        'categoria' => 'Servicios Básicos',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Pago factura luz',
        'monto' => 40.00,
        'user_id' => $this->admin->id,
    ]);

    $service = new ReporteFlujoCajaService;
    $datos = $service->generar(mes: 9, anio: 2026, sucursalId: $this->sucursal->id);

    expect($datos['mes'])->toBe(9)
        ->and($datos['mes_nombre'])->toBe('SEPTIEMBRE')
        ->and($datos['anio'])->toBe(2026)
        ->and(count($datos['columnas_cajas']))->toBe(1);

    $col = $datos['columnas_cajas'][0];
    expect($col['fecha'])->toBe('04/09/2026')
        ->and($col['nro_reporte'])->toBe((string) $caja1->id)
        ->and($col['valores_ingresos']['ing_proformas_meds'])->toBe(50.00)
        ->and($col['valores_ingresos']['ing_serv_'.$this->servicioConsulta->id])->toBe(100.00)
        ->and($col['valores_ingresos']['ing_alquileres'])->toBe(80.00)
        ->and($col['total_ingresos'])->toBe(230.00)
        ->and($col['valores_egresos']['egr_servicios_basicos'])->toBe(40.00)
        ->and($col['total_egresos'])->toBe(40.00)
        ->and($col['saldo_neto'])->toBe(190.00);

    expect($datos['gran_total_ingresos'])->toBe(230.00)
        ->and($datos['gran_total_egresos'])->toBe(40.00)
        ->and($datos['gran_saldo_neto'])->toBe(190.00);

    // Exportación a Excel
    $spreadsheet = $service->exportarExcel($datos);
    $sheet = $spreadsheet->getActiveSheet();

    expect($sheet->getTitle())->toContain('Flujo_SEPTIEMBRE')
        ->and($sheet->getCell('A4')->getValue())->toBe('INGRESOS')
        ->and($sheet->getCell('A5')->getValue())->toBe('TIPO DE INGRESOS')
        ->and($sheet->getCell('A6')->getValue())->toBe('Nro.Reporte Caja')
        ->and((string) $sheet->getCell('B6')->getValue())->toBe((string) $caja1->id);
});

test('componente ReportesIndex permite interactuar con la pestaña flujo_caja y descargar excel', function () {
    $this->actingAs($this->admin);

    $caja = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 50.00,
        'fecha_apertura' => Carbon::create(2026, 9, 10, 8, 0),
        'estado' => 'Cerrada',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Pago::create([
        'caja_id' => $caja->id,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Extra',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Ingreso extraordinario',
        'monto' => 120.00,
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(ReportesIndex::class)
        ->assertSet('reporteActivo', 'kardex')
        ->call('cambiarTipoReporte', 'flujo_caja')
        ->assertSet('reporteActivo', 'flujo_caja')
        ->assertSee('Criterios del Flujo Mensual de Caja, Ingresos y Egresos')
        ->assertDontSee('Criterios del Reporte de Kardex y Movimientos')
        ->assertDontSee('Criterios de la Planilla Mensual por Institución / Convenio')
        ->set('flujo_mes', 9)
        ->set('flujo_anio', 2026)
        ->call('previsualizarReporteFlujoCaja')
        ->assertSet('flujo_reporteGenerado', true)
        ->assertSee('FLUJO MENSUAL DE CAJA - MES DE SEPTIEMBRE 2026')
        ->assertSee('TOTALES INGRESOS')
        ->assertSee('TOTALES EGRESOS')
        ->assertSee('SALDOS (INGRESOS - EGRESOS)')
        ->call('descargarExcelFlujoCaja')
        ->assertFileDownloaded('Flujo_Caja_Ingresos_Egresos_SEPTIEMBRE_2026.xlsx');
});

test('ruta directa para descargar reporte de flujo de caja en excel funciona correctamente', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('administracion.reportes.flujo_caja.excel', [
        'mes' => 9,
        'anio' => 2026,
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('openxmlformats-officedocument.spreadsheetml.sheet');
});

test('servicio genera matriz anual de 12 meses y exporta excel anual correctamente', function () {
    // Caja en Febrero 2026
    $cajaFeb = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 50.00,
        'fecha_apertura' => Carbon::create(2026, 2, 10, 8, 0),
        'estado' => 'Cerrada',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Pago::create([
        'caja_id' => $cajaFeb->id,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Alquileres',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Alquiler mensual consultorio',
        'monto' => 300.00,
        'user_id' => $this->admin->id,
    ]);

    Pago::create([
        'caja_id' => $cajaFeb->id,
        'tipo_movimiento' => 'Egreso',
        'categoria' => 'Servicios Basicos',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Pago Luz y Agua',
        'monto' => 100.00,
        'user_id' => $this->admin->id,
    ]);

    // Caja en Septiembre 2026
    $cajaSep = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'monto_apertura' => 50.00,
        'fecha_apertura' => Carbon::create(2026, 9, 15, 8, 0),
        'estado' => 'Cerrada',
        'usuario_creador_id' => $this->admin->id,
    ]);

    Pago::create([
        'caja_id' => $cajaSep->id,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Otros',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Otros ingresos extraordinarios',
        'monto' => 500.00,
        'user_id' => $this->admin->id,
    ]);

    $service = app(ReporteFlujoCajaService::class);
    $datos = $service->generarAnual(2026, $this->sucursal->id);

    expect($datos['anio'])->toBe(2026)
        ->and(count($datos['meses']))->toBe(12)
        ->and($datos['meses'][1]['mes_nombre'])->toBe('FEBRERO')
        ->and($datos['meses'][1]['total_ingresos'])->toBe(300.0)
        ->and($datos['meses'][1]['total_egresos'])->toBe(100.0)
        ->and($datos['meses'][1]['saldo_neto'])->toBe(200.0)
        ->and($datos['meses'][8]['mes_nombre'])->toBe('SEPTIEMBRE')
        ->and($datos['meses'][8]['total_ingresos'])->toBe(500.0)
        ->and($datos['gran_total_ingresos'])->toBe(800.0)
        ->and($datos['gran_total_egresos'])->toBe(100.0)
        ->and($datos['gran_saldo_neto'])->toBe(700.0);

    $spreadsheet = $service->exportarExcelAnual($datos);
    $sheet = $spreadsheet->getActiveSheet();

    expect($sheet->getCell('A2')->getValue())->toBe('RESUMEN INGRESOS Y GASTOS GESTION 2026')
        ->and($sheet->getCell('A4')->getValue())->toBe('TIPO DE INGRESOS')
        ->and($sheet->getCell('B4')->getValue())->toBe('ENERO')
        ->and($sheet->getCell('C4')->getValue())->toBe('FEBRERO')
        ->and($sheet->getCell('N4')->getValue())->toBe('TOTALES ANUAL')
        ->and((float) $sheet->getCell('C5')->getValue())->toBe(0.0) // proformas
        ->and($sheet->getTitle())->toBe('Resumen_Anual_2026');
});

test('componente ReportesIndex permite interactuar con la pestaña resumen_anual y descargar excel', function () {
    $this->actingAs($this->admin);

    Livewire::test(ReportesIndex::class)
        ->call('cambiarTipoReporte', 'resumen_anual')
        ->assertSet('reporteActivo', 'resumen_anual')
        ->assertSee('Criterios del Resumen Anual de Ingresos y Gastos')
        ->assertDontSee('Criterios del Reporte de Kardex y Movimientos')
        ->set('anual_anio', 2026)
        ->call('previsualizarReporteAnual')
        ->assertSet('anual_reporteGenerado', true)
        ->assertSee('RESUMEN INGRESOS Y GASTOS GESTIÓN 2026')
        ->assertSee('TOTALES INGRESOS')
        ->assertSee('TOTALES EGRESOS')
        ->assertSee('SALDO EN CAJA (INGRESOS - EGRESOS)')
        ->call('descargarExcelAnual')
        ->assertFileDownloaded('Resumen_Ingresos_y_Gastos_Gestion_2026.xlsx');
});

test('ruta directa para descargar resumen anual en excel funciona correctamente', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('administracion.reportes.resumen_anual.excel', [
        'anio' => 2026,
    ]));

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('openxmlformats-officedocument.spreadsheetml.sheet');
});
