<?php

use App\Livewire\Farmacia\DespachosIndex;
use App\Livewire\Farmacia\LotesIndex;
use App\Livewire\Farmacia\MovimientosIndex;
use App\Livewire\Farmacia\ProductosIndex;
use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Proveedor;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador Total']
    );

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sede Central La Paz',
        'ciudad' => 'La Paz',
        'direccion' => 'Av. 6 de Agosto #1234',
        'telefono' => '22445566',
        'es_matriz' => true,
        'activo' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Laboratorios Bagó',
        'descripcion' => 'Línea farmacéutica internacional',
    ]);

    $this->proveedor = Proveedor::create([
        'razon_social' => 'Droguería INTI S.A.',
        'nit_ruc' => '1020304050',
        'telefono' => '22113344',
    ]);
});

test('puede acceder a las vistas del modulo de farmacia', function () {
    $this->actingAs($this->admin);

    $this->get(route('farmacia.productos'))->assertOk();
    $this->get(route('farmacia.lotes'))->assertOk();
    $this->get(route('farmacia.despachos'))->assertOk();
    $this->get(route('farmacia.movimientos'))->assertOk();
});

test('puede registrar, editar y listar productos en el catalogo', function () {
    $this->actingAs($this->admin);

    Livewire::test(ProductosIndex::class)
        ->assertOk()
        ->call('abrirModalProducto')
        ->set('nombre', 'Ibuprofeno 400mg')
        ->set('marca_id', $this->marca->id)
        ->set('unidad_medida', 'Tableta')
        ->set('ultimo_precio_venta', '1.50')
        ->set('stock_minimo', 20)
        ->set('descripcion', 'Antiinflamatorio no esteroideo')
        ->call('guardarProducto')
        ->assertDispatched('swal');

    $producto = Producto::where('nombre', 'Ibuprofeno 400mg')->first();
    expect($producto)->not->toBeNull();
    expect((float) $producto->ultimo_precio_venta)->toBe(1.50);
    expect($producto->stock_minimo)->toBe(20);

    // Editar
    Livewire::test(ProductosIndex::class)
        ->call('abrirModalProducto', $producto->id)
        ->set('ultimo_precio_venta', '2.00')
        ->call('guardarProducto')
        ->assertDispatched('swal');

    expect((float) $producto->fresh()->ultimo_precio_venta)->toBe(2.00);
});

test('puede registrar un lote generando asiento automatico en kardex y actualizando precio de venta', function () {
    $this->actingAs($this->admin);

    $producto = Producto::create([
        'nombre' => 'Amoxicilina 500mg',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Cápsula',
        'ultimo_precio_venta' => 2.50,
        'stock_minimo' => 10,
    ]);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalLote')
        ->set('sucursal_id', $this->sucursal->id)
        ->set('producto_id', $producto->id)
        ->set('proveedor_id', $this->proveedor->id)
        ->set('codigo_lote', 'AMX-2026-001')
        ->set('cantidad_ingresada', 50)
        ->set('fecha_vencimiento', now()->addMonths(18)->toDateString())
        ->set('precio_compra', '1.80')
        ->set('precio_venta', '3.50')
        ->call('guardarLote')
        ->assertDispatched('swal');

    $lote = Lote::where('codigo_lote', 'AMX-2026-001')->first();
    expect($lote)->not->toBeNull();
    expect($lote->cantidad_actual)->toBe(50);
    expect((float) $lote->precio_venta)->toBe(3.50);

    // Asiento automático en Kardex
    $mov = MovimientoInventario::where('lote_id', $lote->id)->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento)->toBe('Entrada Compra');
    expect($mov->cantidad)->toBe(50);

    // Producto actualizado con nuevo precio
    expect((float) $producto->fresh()->ultimo_precio_venta)->toBe(3.50);
});

test('puede procesar ajuste o merma de un lote con descuento de stock y asiento en kardex', function () {
    $this->actingAs($this->admin);

    $producto = Producto::create([
        'nombre' => 'Omeprazol 20mg',
        'unidad_medida' => 'Cápsula',
        'ultimo_precio_venta' => 1.00,
        'stock_minimo' => 5,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'OME-MERMA-1',
        'cantidad_ingresada' => 10,
        'cantidad_actual' => 10,
        'fecha_vencimiento' => now()->subDay(), // Vencido ayer
        'precio_compra' => 0.60,
        'precio_venta' => 1.00,
    ]);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalAjuste', $lote->id)
        ->set('tipoAjuste', 'Merma por Vencimiento')
        ->set('cantidadAjuste', 4)
        ->set('motivoAjuste', 'Lote expirado retirado de estantería')
        ->call('procesarAjuste')
        ->assertDispatched('swal');

    expect($lote->fresh()->cantidad_actual)->toBe(6);

    $mov = MovimientoInventario::where('lote_id', $lote->id)
        ->where('tipo_movimiento', 'Merma por Vencimiento')
        ->first();

    expect($mov)->not->toBeNull();
    expect($mov->cantidad)->toBe(4);
});

test('puede despachar medicamentos de una receta activa y cargar el cobro real a la proforma', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Gabriela',
        'apellido_paterno' => 'Mendoza',
        'cedula' => 'CI-FARM-99',
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

    $producto = Producto::create([
        'nombre' => 'Ciprofloxacina 500mg',
        'unidad_medida' => 'Comprimido',
        'ultimo_precio_venta' => 10.00,
        'stock_minimo' => 5,
    ]);

    // Lote 1: Vence en 30 días (primero en vencer)
    $lote1 = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'CIPRO-L1',
        'cantidad_ingresada' => 10,
        'cantidad_actual' => 10,
        'fecha_vencimiento' => now()->addDays(30),
        'precio_compra' => 5.00,
        'precio_venta' => 10.00,
    ]);

    // Lote 2: Vence en 300 días
    $lote2 = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'CIPRO-L2',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 20,
        'fecha_vencimiento' => now()->addDays(300),
        'precio_compra' => 5.00,
        'precio_venta' => 10.00,
    ]);

    // Receta médica que pide 5 comprimidos para 5 días
    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'observaciones' => 'Tomar 1 comprimido diario',
        'activo' => true,
    ]);

    $detalle = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
        'indicaciones' => '1 comp cada 24 horas',
        'despachado' => false,
    ]);

    // En este momento el costo cobrable de la proforma es 0 (receta referencial)
    $proforma->recalcularTotal();
    expect((float) $proforma->fresh()->costo_total)->toBe(0.00);
    expect((float) $proforma->totalPrescripcionReferencial())->toBe(50.00);

    // Farmacia despacha 2 comprimidos para el día 1 y 2
    Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->assertSet('despachosItems.'.$detalle->id.'.lote_id', $lote1->id) // Sugiere lote1 por FEFO!
        ->set('despachosItems.'.$detalle->id.'.cantidad_despachar', 2)
        ->call('procesarDespacho')
        ->assertDispatched('swal');

    // Comprobar descuento del Lote 1
    expect($lote1->fresh()->cantidad_actual)->toBe(8);
    expect($lote2->fresh()->cantidad_actual)->toBe(20);

    // Comprobar movimiento en Kardex
    $mov = MovimientoInventario::where('receta_id', $receta->id)->first();
    expect($mov)->not->toBeNull();
    expect($mov->tipo_movimiento)->toBe('Salida Receta');
    expect($mov->cantidad)->toBe(2);
    expect($mov->proforma_id)->toBe($proforma->id);

    // Comprobar recálculo de la proforma: ¡ahora suma exactamente 2 * 10.00 = 20.00!
    expect((float) $proforma->fresh()->costo_total)->toBe(20.00);
    expect((float) $proforma->totalDespachosFarmacia())->toBe(20.00);
    // Y la receta sigue teniendo 3 comprimidos pendientes
    expect($detalle->fresh()->despachado)->toBeFalse();

    // Ahora farmacia despacha los 3 restantes
    Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->set('despachosItems.'.$detalle->id.'.cantidad_despachar', 3)
        ->call('procesarDespacho')
        ->assertDispatched('swal');

    expect($lote1->fresh()->cantidad_actual)->toBe(5);
    expect($detalle->fresh()->despachado)->toBeTrue(); // ¡Completado!
    expect((float) $proforma->fresh()->costo_total)->toBe(50.00);
});

test('puede listar y filtrar los movimientos de kardex incluyendo relacion usuario', function () {
    $this->actingAs($this->admin);

    $producto = Producto::create([
        'nombre' => 'Ibuprofeno 400mg',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Caja',
        'ultimo_precio_venta' => 15.00,
        'stock_minimo' => 10,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'proveedor_id' => $this->proveedor->id,
        'codigo_lote' => 'IBU-999',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addMonths(6),
        'precio_compra' => 8.00,
        'precio_venta' => 15.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'cantidad' => 50,
        'tipo_movimiento' => 'Entrada Compra',
        'user_id' => $this->admin->id,
    ]);

    Livewire::test(MovimientosIndex::class)
        ->assertOk()
        ->assertSee('IBU-999')
        ->assertSee('Ibuprofeno 400mg')
        ->assertSee($this->admin->name)
        ->set('search', $this->admin->name)
        ->assertSee('IBU-999');
});

test('puede despachar medicamentos de receta junto con insumos extras y reflejarse en proforma y kardex', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Mario',
        'apellido_paterno' => 'Mendoza',
        'cedula' => '6543210',
        'fecha_nacimiento' => '1985-05-15',
        'genero' => 'Masculino',
        'telefono' => '71234567',
        'direccion' => 'Av. Saavedra #500',
    ]);

    $proforma = Proforma::create([
        'paciente_id' => $paciente->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    // Medicamento prescrito
    $med = Producto::create([
        'nombre' => 'Omeprazol 20mg',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Cápsula',
        'ultimo_precio_venta' => 5.00,
        'stock_minimo' => 10,
    ]);

    $loteMed = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $med->id,
        'proveedor_id' => $this->proveedor->id,
        'codigo_lote' => 'OME-101',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 20,
        'fecha_vencimiento' => now()->addDays(90),
        'precio_compra' => 2.00,
        'precio_venta' => 5.00,
    ]);

    // Insumo extra (ej: Jeringa descartable)
    $insumoExtra = Producto::create([
        'nombre' => 'Jeringa descartable 10ml',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Unidad',
        'ultimo_precio_venta' => 3.00,
        'stock_minimo' => 20,
    ]);

    $loteExtra = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $insumoExtra->id,
        'proveedor_id' => $this->proveedor->id,
        'codigo_lote' => 'JER-500',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 1.00,
        'precio_venta' => 3.00,
    ]);

    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'activo' => true,
        'observaciones' => 'Tomar antes del desayuno',
    ]);

    $detalle = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $med->id,
        'cantidad' => 4,
        'indicaciones' => '1 cápsula cada mañana',
        'despachado' => false,
    ]);

    // Verificar renderizado de tabla en DespachosIndex y despacho combinado
    Livewire::test(DespachosIndex::class)
        ->assertOk()
        ->assertSee('Mario Mendoza')
        ->assertSee('Receta #'.$receta->id)
        ->assertSee('Proforma #'.$proforma->id)
        ->call('abrirModalDespacho', $receta->id)
        ->assertSet('modalDespachoOpen', true)
        ->assertSet('despachosItems.'.$detalle->id.'.lote_id', $loteMed->id)
        ->set('despachosItems.'.$detalle->id.'.cantidad_despachar', 2)
        // Añadir insumo extra en el modal usando el buscador interactivo
        ->set('buscarExtraProducto', 'Jeringa')
        ->assertSee('Jeringa descartable 10ml')
        ->call('seleccionarProductoExtra', $insumoExtra->id)
        ->assertSet('extra_producto_id', $insumoExtra->id)
        ->assertSet('extra_lote_id', $loteExtra->id)
        ->set('extra_cantidad', 3)
        ->set('extra_observaciones', 'Jeringas para administración')
        ->call('agregarItemExtra')
        ->assertCount('extrasItems', 1)
        ->assertSet('extra_producto_id', null)
        ->assertSet('buscarExtraProducto', '')
        ->call('procesarDespacho')
        ->assertDispatched('swal');

    // Validar descuentos físicos de stock
    expect($loteMed->fresh()->cantidad_actual)->toBe(18); // 20 - 2
    expect($loteExtra->fresh()->cantidad_actual)->toBe(97); // 100 - 3

    // Validar movimientos en Kardex
    $movReceta = MovimientoInventario::where('receta_id', $receta->id)
        ->where('tipo_movimiento', 'Salida Receta')
        ->first();
    expect($movReceta)->not->toBeNull();
    expect($movReceta->cantidad)->toBe(2);

    $movExtra = MovimientoInventario::where('receta_id', $receta->id)
        ->where('tipo_movimiento', 'Consumo Extra')
        ->first();
    expect($movExtra)->not->toBeNull();
    expect($movExtra->cantidad)->toBe(3);
    expect($movExtra->producto_id)->toBe($insumoExtra->id);

    // Validar ConsumoExtra creado en Proforma
    $consumo = ConsumoExtra::where('proforma_id', $proforma->id)->first();
    expect($consumo)->not->toBeNull();
    expect($consumo->producto_id)->toBe($insumoExtra->id);
    expect($consumo->cantidad)->toBe(3);
    expect((float) $consumo->precio_unitario)->toBe(3.00);

    // Validar recálculo de Proforma:
    // 2 cápsulas de Omeprazol a Bs. 5.00 = 10.00
    // 3 jeringas a Bs. 3.00 = 9.00
    // Total = 19.00
    expect((float) $proforma->fresh()->costo_total)->toBe(19.00);
});
