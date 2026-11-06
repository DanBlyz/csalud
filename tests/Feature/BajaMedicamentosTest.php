<?php

use App\Livewire\Farmacia\BajasMedicamentosIndex;
use App\Models\BajaMedicamento;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(PermisoSeeder::class);

    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador Total']
    );

    $this->rolOperador = Rol::firstOrCreate(
        ['nombre' => 'Farmacia'],
        ['descripcion' => 'Personal de Farmacia']
    );

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sede Central',
        'ciudad' => 'La Paz',
        'direccion' => 'Av. Arce #100',
        'telefono' => '22114455',
        'es_matriz' => true,
        'activo' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Laboratorios Bagó',
    ]);

    $this->seccionFarmacia = Seccion::create([
        'nombre' => 'Farmacia Central',
        'sucursal_id' => $this->sucursal->id,
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $this->seccionQuirofano = Seccion::create([
        'nombre' => 'Quirófano',
        'sucursal_id' => $this->sucursal->id,
        'es_almacen_principal' => false,
        'activo' => true,
    ]);
});

test('usuario sin permisos no puede acceder a bajas de medicamentos', function (): void {
    $usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolOperador->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->actingAs($usuarioSinPermiso);

    $this->get(route('farmacia.bajas'))
        ->assertStatus(403);
});

test('usuario autorizado puede visualizar la vista de bajas de medicamentos', function (): void {
    $this->actingAs($this->admin);

    $this->get(route('farmacia.bajas'))
        ->assertStatus(200)
        ->assertSee('Baja de Medicamentos e Insumos');
});

test('puede registrar un formulario de baja en estado pendiente sin descontar inventario', function (): void {
    $this->actingAs($this->admin);

    $producto = Producto::factory()->create([
        'nombre' => 'Amoxicilina 500mg',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Cápsula',
        'descripcion' => '500mg',
    ]);

    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOT-AMX-001',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->subDays(10)->toDateString(), // vencido
        'precio_compra' => 1.50,
        'precio_venta' => 2.50,
    ]);

    Livewire::test(BajasMedicamentosIndex::class)
        ->call('abrirModalCrear')
        ->assertSet('modalBajaOpen', true)
        ->set('fecha_registro', now()->toDateString())
        ->set('area_solicitante', 'Farmacia Central')
        ->set('responsable', 'Lic. Farmacéutico')
        ->set('motivo', 'Vencimiento')
        ->set('justificacion', 'Medicamento caducado hace 10 días, no apto para consumo humano.')
        ->call('seleccionarLoteParaItem', $lote->id)
        ->set('item_seccion_id', $this->seccionFarmacia->id)
        ->set('item_cantidad', 20)
        ->set('item_observaciones', 'Blísteres sellados vencidos')
        ->call('agregarItemACola')
        ->assertCount('cola_detalles', 1)
        ->call('guardarBaja')
        ->assertDispatched('swal')
        ->assertSet('modalBajaOpen', false);

    // Verificar que se creó el registro de baja en estado Pendiente
    $baja = BajaMedicamento::first();
    expect($baja)->not->toBeNull();
    expect($baja->codigo)->toStartWith('BM-');
    expect($baja->estado)->toBe('Pendiente');
    expect($baja->detalles()->count())->toBe(1);

    $detalle = $baja->detalles()->first();
    expect($detalle->producto_id)->toBe($producto->id);
    expect($detalle->lote_id)->toBe($lote->id);
    expect($detalle->seccion_id)->toBe($this->seccionFarmacia->id);
    expect($detalle->cantidad)->toBe(20);
    expect($detalle->movimiento_inventario_id)->toBeNull();

    // Verificación crítica: el stock NO debe haberse alterado aún
    $lote->refresh();
    $loteSeccion = LoteSeccion::where('lote_id', $lote->id)->where('seccion_id', $this->seccionFarmacia->id)->first();
    expect($lote->cantidad_actual)->toBe(50);
    expect($loteSeccion->cantidad_actual)->toBe(50);
    expect(MovimientoInventario::count())->toBe(0);
});

test('puede modificar una solicitud de baja pendiente', function (): void {
    $this->actingAs($this->admin);

    $producto = Producto::factory()->create(['nombre' => 'Paracetamol 500mg']);
    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOT-PAR-002',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 30,
        'fecha_vencimiento' => now()->subDays(5)->toDateString(),
        'precio_compra' => 0.80,
        'precio_venta' => 1.50,
    ]);

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0099',
        'fecha_registro' => now(),
        'area_solicitante' => 'Almacén',
        'responsable' => 'Auxiliar Farmacia',
        'motivo' => 'Vencimiento',
        'justificacion' => 'Motivo inicial de baja',
        'estado' => 'Pendiente',
    ]);

    $baja->detalles()->create([
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'seccion_id' => $this->seccionFarmacia->id,
        'cantidad' => 10,
    ]);

    Livewire::test(BajasMedicamentosIndex::class)
        ->call('abrirModalEditar', $baja->id)
        ->assertSet('modalBajaOpen', true)
        ->assertSet('baja_id_en_edicion', $baja->id)
        ->set('justificacion', 'Justificación corregida y ampliada')
        ->call('guardarBaja')
        ->assertDispatched('swal');

    $baja->refresh();
    expect($baja->justificacion)->toBe('Justificación corregida y ampliada');
});

test('puede eliminar una solicitud de baja pendiente', function (): void {
    $this->actingAs($this->admin);

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0088',
        'fecha_registro' => now(),
        'area_solicitante' => 'Farmacia',
        'responsable' => 'Encargado',
        'motivo' => 'Deterioro / Rotura',
        'justificacion' => 'Frasco quebrado',
        'estado' => 'Pendiente',
    ]);

    Livewire::test(BajasMedicamentosIndex::class)
        ->call('eliminarBaja', $baja->id)
        ->assertDispatched('swal');

    expect(BajaMedicamento::find($baja->id))->toBeNull();
});

test('al aprobar una baja se descuenta el stock del lote y de la seccion y se genera el movimiento de inventario', function (): void {
    $this->actingAs($this->admin);

    $producto = Producto::factory()->create([
        'nombre' => 'Ciprofloxacina 200mg/100ml',
        'marca_id' => $this->marca->id,
    ]);

    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOT-CIPRO-010',
        'cantidad_ingresada' => 80,
        'cantidad_actual' => 40,
        'fecha_vencimiento' => now()->subMonth()->toDateString(),
        'precio_compra' => 15.00,
        'precio_venta' => 25.00,
    ]);

    $loteSeccion = LoteSeccion::updateOrCreate(
        ['lote_id' => $lote->id, 'seccion_id' => $this->seccionQuirofano->id],
        ['cantidad_actual' => 25]
    );

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0005',
        'fecha_registro' => now(),
        'area_solicitante' => 'Quirófano Central',
        'responsable' => 'Dra. Cirujana',
        'motivo' => 'Vencimiento',
        'justificacion' => 'Lote vencido en sala de operaciones',
        'estado' => 'Pendiente',
    ]);

    $detalle = $baja->detalles()->create([
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'seccion_id' => $this->seccionQuirofano->id,
        'cantidad' => 15,
        'forma_farmaceutica' => 'Frasco Solución',
        'concentracion' => '200mg/100ml',
        'fecha_vencimiento' => $lote->fecha_vencimiento,
    ]);

    // Procesar aprobación mediante Livewire
    Livewire::test(BajasMedicamentosIndex::class)
        ->call('abrirModalAprobar', $baja->id)
        ->assertSet('modalAprobarOpen', true)
        ->set('observaciones_aprobacion', 'Aprobado por Dirección Médica y Jefatura de Farmacia')
        ->call('procesarAprobacion')
        ->assertDispatched('swal')
        ->assertSet('modalAprobarOpen', false);

    // 1. Verificación del estado de la Baja
    $baja->refresh();
    expect($baja->estado)->toBe('Aprobado');
    expect($baja->aprobado_por_id)->toBe($this->admin->id);
    expect($baja->fecha_aprobacion)->not->toBeNull();
    expect($baja->observaciones_aprobacion)->toBe('Aprobado por Dirección Médica y Jefatura de Farmacia');

    // 2. Verificación de descuento físico
    $lote->refresh();
    $loteSeccion->refresh();
    expect($lote->cantidad_actual)->toBe(25); // 40 - 15 = 25
    expect($loteSeccion->cantidad_actual)->toBe(10); // 25 - 15 = 10

    // 3. Verificación del movimiento de inventario generado
    $mov = MovimientoInventario::where('baja_medicamento_id', $baja->id)->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento)->toBe('Baja Medicamento');
    expect($mov->cantidad)->toBe(15);
    expect($mov->producto_id)->toBe($producto->id);
    expect($mov->lote_id)->toBe($lote->id);
    expect($mov->seccion_origen_id)->toBe($this->seccionQuirofano->id);
    expect($mov->seccion_destino_id)->toBeNull();
    expect($mov->user_id)->toBe($this->admin->id);

    // 4. Verificación de asociación en el detalle
    $detalle->refresh();
    expect($detalle->movimiento_inventario_id)->toBe($mov->id);
});

test('aprobacion falla si el stock en la seccion es insuficiente', function (): void {
    $this->actingAs($this->admin);

    $producto = Producto::factory()->create(['nombre' => 'Omeprazol 40mg']);
    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOT-OME-001',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 5,
        'fecha_vencimiento' => now()->subDays(2)->toDateString(),
        'precio_compra' => 5.00,
        'precio_venta' => 10.00,
    ]);

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0006',
        'fecha_registro' => now(),
        'area_solicitante' => 'Farmacia',
        'responsable' => 'Lic. Farmacia',
        'motivo' => 'Vencimiento',
        'justificacion' => 'Baja',
        'estado' => 'Pendiente',
    ]);

    $baja->detalles()->create([
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'seccion_id' => $this->seccionFarmacia->id,
        'cantidad' => 10, // requiere 10 pero solo hay 5
    ]);

    Livewire::test(BajasMedicamentosIndex::class)
        ->call('abrirModalAprobar', $baja->id)
        ->call('procesarAprobacion')
        ->assertDispatched('swal');

    $baja->refresh();
    expect($baja->estado)->toBe('Pendiente'); // No debe cambiar a aprobado
    expect(MovimientoInventario::count())->toBe(0);
});

test('puede rechazar una solicitud de baja sin alterar inventarios', function (): void {
    $this->actingAs($this->admin);

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0007',
        'fecha_registro' => now(),
        'area_solicitante' => 'Emergencias',
        'responsable' => 'Dr. Guardia',
        'motivo' => 'Merma',
        'justificacion' => 'Insumo presuntamente dañado',
        'estado' => 'Pendiente',
    ]);

    Livewire::test(BajasMedicamentosIndex::class)
        ->call('abrirModalRechazar', $baja->id)
        ->set('motivo_rechazo', 'No procede baja, el insumo se encuentra apto para canje con el proveedor.')
        ->call('procesarRechazo')
        ->assertDispatched('swal');

    $baja->refresh();
    expect($baja->estado)->toBe('Rechazado');
    expect($baja->observaciones_aprobacion)->toBe('No procede baja, el insumo se encuentra apto para canje con el proveedor.');
    expect(MovimientoInventario::count())->toBe(0);
});

test('puede generar el reporte oficial en pdf de la baja de medicamentos', function (): void {
    $this->actingAs($this->admin);

    $producto = Producto::factory()->create(['nombre' => 'Diclofenaco 75mg/3ml']);
    $lote = Lote::create([
        'producto_id' => $producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOT-DIC-75',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->subDays(30)->toDateString(),
        'precio_compra' => 2.00,
        'precio_venta' => 4.50,
    ]);

    $baja = BajaMedicamento::create([
        'sucursal_id' => $this->sucursal->id,
        'codigo' => 'BM-2026-0008',
        'fecha_registro' => now(),
        'area_solicitante' => 'Farmacia Central',
        'responsable' => 'Lic. Patricia Vargas',
        'motivo' => 'Vencimiento',
        'justificacion' => 'Ampollas caducadas, se da de baja para incineración y disposición final conforme a normativa de salud.',
        'estado' => 'Pendiente',
    ]);

    $baja->detalles()->create([
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'seccion_id' => $this->seccionFarmacia->id,
        'cantidad' => 25,
        'forma_farmaceutica' => 'Ampolla',
        'concentracion' => '75mg/3ml',
        'fecha_vencimiento' => $lote->fecha_vencimiento,
        'observaciones' => 'Caja sellada',
    ]);

    $response = $this->get(route('farmacia.bajas.pdf', $baja->id));
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});
