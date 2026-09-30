<?php

use App\Livewire\Administracion\ReportesIndex;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\ReporteMovimientosService;
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

    $this->rolUsuario = Rol::firstOrCreate(
        ['nombre' => 'Personal'],
        ['descripcion' => 'Personal Clínico']
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

    $this->usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolUsuario->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Laboratorio Bagó',
        'descripcion' => 'Farmacéutica',
        'activo' => true,
    ]);

    $this->producto = Producto::create([
        'marca_id' => $this->marca->id,
        'nombre' => 'Paracetamol 500mg',
        'descripcion' => 'Analgésico y antipirético',
        'unidad_medida' => 'Caja x 20',
        'ultimo_precio_venta' => 15.00,
        'stock_minimo' => 10,
    ]);

    $this->lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-TEST-001',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 55,
        'fecha_vencimiento' => Carbon::now()->addYear()->toDateString(),
        'precio_compra' => 10.00,
        'precio_venta' => 15.00,
    ]);
});

test('usuario administrador puede acceder al centro de reportes', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('administracion.reportes'));

    $response->assertOk()
        ->assertSeeLivewire(ReportesIndex::class);
});

test('usuario sin permiso no puede acceder al centro de reportes', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $response = $this->get(route('administracion.reportes'));

    $response->assertForbidden();
});

test('calcula correctamente el saldo inicial, movimientos y valorizaciones en el rango de fechas', function () {
    // 1. Movimiento previo a la fecha de inicio (Saldo Inicial)
    // 50 unidades de Entrada antes del 01 de Septiembre
    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->lote->id,
        'cantidad' => 50,
        'tipo_movimiento' => 'Entrada Compra',
        'user_id' => $this->admin->id,
        'created_at' => Carbon::parse('2026-08-20 10:00:00'),
    ]);

    // 2. Movimientos dentro del período evaluado (01/09/2026 al 30/09/2026)
    // Salida de 15 unidades el 05 de Septiembre
    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->lote->id,
        'cantidad' => 15,
        'tipo_movimiento' => 'Salida Receta',
        'user_id' => $this->admin->id,
        'created_at' => Carbon::parse('2026-09-05 14:00:00'),
    ]);

    // Entrada adicional de 20 unidades el 15 de Septiembre
    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->lote->id,
        'cantidad' => 20,
        'tipo_movimiento' => 'Entrada Compra',
        'user_id' => $this->admin->id,
        'created_at' => Carbon::parse('2026-09-15 09:30:00'),
    ]);

    $service = new ReporteMovimientosService;
    $reporte = $service->generar(
        fechaInicio: '2026-09-01',
        fechaFin: '2026-09-30',
        productoId: $this->producto->id,
        sucursalId: $this->sucursal->id
    );

    expect($reporte['items'])->toHaveCount(1);
    $item = $reporte['items'][0];

    // Saldo Inicial = 50
    expect($item['saldo_inicial'])->toBe(50);
    // Entradas en el período = 20, Salidas en el período = 15
    expect($item['total_entradas'])->toBe(20)
        ->and($item['total_salidas'])->toBe(15);
    // Saldo Final = 50 + 20 - 15 = 55
    expect($item['saldo_final'])->toBe(55);

    // Precios: Compra 10.00, Venta 15.00
    expect($item['ultimo_precio_compra'])->toBe(10.0)
        ->and($item['ultimo_precio_venta'])->toBe(15.0);

    // Valorizaciones:
    // Compra: 55 * 10 = 550.00 Bs
    // Venta: 55 * 15 = 825.00 Bs
    expect($item['valor_total_compra'])->toBe(550.0)
        ->and($item['valor_total_venta'])->toBe(825.0);

    // Movimientos cronológicos
    expect($item['movimientos'])->toHaveCount(2);
    expect($item['movimientos'][0]['cantidad_salida'])->toBe(15)
        ->and($item['movimientos'][0]['saldo_acumulado'])->toBe(35); // 50 - 15 = 35
    expect($item['movimientos'][1]['cantidad_entrada'])->toBe(20)
        ->and($item['movimientos'][1]['saldo_acumulado'])->toBe(55); // 35 + 20 = 55
});

test('componente livewire previsualiza el reporte y responde a los filtros', function () {
    $this->actingAs($this->admin);

    // Crear movimiento
    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->lote->id,
        'cantidad' => 30,
        'tipo_movimiento' => 'Entrada Compra',
        'user_id' => $this->admin->id,
        'created_at' => Carbon::now(),
    ]);

    Livewire::test(ReportesIndex::class)
        ->set('fecha_inicio', Carbon::now()->startOfMonth()->toDateString())
        ->set('fecha_fin', Carbon::now()->toDateString())
        ->set('producto_id', $this->producto->id)
        ->call('previsualizarReporte')
        ->assertSet('reporteGenerado', true)
        ->assertSee('Paracetamol 500mg')
        ->assertSee('LOT-TEST-001')
        ->assertSee('Previsualizar Reporte');
});

test('puede generar y descargar el pdf del kardex de movimientos', function () {
    $this->actingAs($this->admin);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->lote->id,
        'cantidad' => 40,
        'tipo_movimiento' => 'Entrada Compra',
        'user_id' => $this->admin->id,
        'created_at' => Carbon::now(),
    ]);

    $response = $this->get(route('administracion.reportes.movimientos.pdf', [
        'fecha_inicio' => Carbon::now()->startOfMonth()->toDateString(),
        'fecha_fin' => Carbon::now()->toDateString(),
        'producto_id' => $this->producto->id,
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
