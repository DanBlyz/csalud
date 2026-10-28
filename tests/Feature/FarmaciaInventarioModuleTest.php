<?php

use App\Livewire\Farmacia\DespachosIndex;
use App\Livewire\Farmacia\LotesIndex;
use App\Livewire\Farmacia\MovimientosIndex;
use App\Livewire\Farmacia\ProductosIndex;
use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Proveedor;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use App\Services\ReporteMovimientosService;
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

test('los campos del modal de lote se resetean completamente despues de guardar o cerrar', function () {
    $this->actingAs($this->admin);

    $producto = Producto::create([
        'nombre' => 'Paracetamol 500mg Test',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'stock_minimo' => 10,
    ]);

    $component = Livewire::test(LotesIndex::class)
        ->call('abrirModalLote')
        ->set('sucursal_id', $this->sucursal->id)
        ->set('producto_id', $producto->id)
        ->set('codigo_lote', 'PAR-RESET-01')
        ->set('cantidad_ingresada', 25)
        ->set('fecha_vencimiento', '2028-06-30')
        ->set('precio_compra', '0.80')
        ->set('precio_venta', '1.50')
        ->call('guardarLote')
        ->assertDispatched('swal');

    // Al haberse guardado, el modal debe estar cerrado y los campos limpios
    $component->assertSet('modalLoteOpen', false)
        ->assertSet('producto_id', null)
        ->assertSet('cantidad_ingresada', null)
        ->assertSet('fecha_vencimiento', null)
        ->assertSet('precio_compra', '')
        ->assertSet('precio_venta', '');

    // Al abrir el modal de nuevo, no deben reflejarse los datos anteriores
    $component->call('abrirModalLote')
        ->assertSet('modalLoteOpen', true)
        ->assertSet('producto_id', null)
        ->assertSet('cantidad_ingresada', null)
        ->assertSet('fecha_vencimiento', null)
        ->assertSet('precio_compra', '')
        ->assertSet('precio_venta', '');
});

test('calcula correctamente las tarjetas de valorizacion stock compra y venta usando el ultimo precio de cada producto', function () {
    $this->actingAs($this->admin);

    $prod1 = Producto::create([
        'nombre' => 'Producto Val 1',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Frasco',
        'ultimo_precio_venta' => 20.00,
        'stock_minimo' => 5,
    ]);

    $prod2 = Producto::create([
        'nombre' => 'Producto Val 2',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Caja',
        'ultimo_precio_venta' => 50.00,
        'stock_minimo' => 5,
    ]);

    // Lotes Prod 1: Lote antiguo a compra 8 / venta 15; lote nuevo a compra 10 / venta 20
    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prod1->id,
        'codigo_lote' => 'VAL1-OLD',
        'cantidad_ingresada' => 10,
        'cantidad_actual' => 10,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 8.00,
        'precio_venta' => 15.00,
    ]);

    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prod1->id,
        'codigo_lote' => 'VAL1-NEW',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 20,
        'fecha_vencimiento' => now()->addYears(2),
        'precio_compra' => 10.00, // Último precio de compra de Prod 1
        'precio_venta' => 20.00,  // Último precio de venta de Prod 1
    ]);

    // Stock total Prod 1 = 10 + 20 = 30 unidades
    // Valor compra Prod 1 = 30 * 10.00 = 300.00
    // Valor venta Prod 1 = 30 * 20.00 = 600.00

    // Lote Prod 2: compra 35 / venta 50
    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prod2->id,
        'codigo_lote' => 'VAL2-LOTE',
        'cantidad_ingresada' => 5,
        'cantidad_actual' => 5,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 35.00, // Último precio de compra de Prod 2
        'precio_venta' => 50.00,  // Último precio de venta de Prod 2
    ]);

    // Stock total Prod 2 = 5 unidades
    // Valor compra Prod 2 = 5 * 35.00 = 175.00
    // Valor venta Prod 2 = 5 * 50.00 = 250.00

    // Total general esperado:
    // Compra = 300 + 175 = 475.00
    // Venta = 600 + 250 = 850.00

    Livewire::test(LotesIndex::class)
        ->assertViewHas('valorStockCompra', 475.00)
        ->assertViewHas('valorStockVenta', 850.00)
        ->assertSee('Valorización Stock - Precio Compra')
        ->assertSee('Valorización Stock - Precio Venta')
        ->assertSee('475.00')
        ->assertSee('850.00');
});

test('muestra metricas del catalogo de productos y permite filtrar interactivamente por condicion de stock', function () {
    $this->actingAs($this->admin);

    // Producto 1: Stock Normal (Stock 50 > Min 10)
    $prodNormal = Producto::create([
        'nombre' => 'Paracetamol 500mg Normal',
        'marca_id' => $this->marca->id,
        'stock_minimo' => 10,
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 2.00,
        'activo' => true,
    ]);
    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prodNormal->id,
        'codigo_lote' => 'LOTE-NORM',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 1.00,
        'precio_venta' => 2.00,
    ]);

    // Producto 2: Stock Crítico (Stock 5 <= Min 15)
    $prodCritico = Producto::create([
        'nombre' => 'Omeprazol 20mg Critico',
        'marca_id' => $this->marca->id,
        'stock_minimo' => 15,
        'unidad_medida' => 'Capsula',
        'ultimo_precio_venta' => 4.00,
        'activo' => true,
    ]);
    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $prodCritico->id,
        'codigo_lote' => 'LOTE-CRIT',
        'cantidad_ingresada' => 5,
        'cantidad_actual' => 5,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 2.00,
        'precio_venta' => 4.00,
    ]);

    // Producto 3: Sin existencias (Stock 0)
    $prodAgotado = Producto::create([
        'nombre' => 'Loratadina 10mg Agotado',
        'marca_id' => $this->marca->id,
        'stock_minimo' => 10,
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'activo' => true,
    ]);

    Livewire::test(ProductosIndex::class)
        ->assertViewHas('totalStockNormal', 1)
        ->assertViewHas('totalStockCritico', 1)
        ->assertViewHas('totalStockAgotado', 1)
        ->assertSee('Paracetamol 500mg Normal')
        ->assertSee('Omeprazol 20mg Critico')
        ->assertSee('Loratadina 10mg Agotado')
        // Filtrar por Crítico
        ->call('setFiltroStock', 'critico')
        ->assertSet('filtroStock', 'critico')
        ->assertSee('Omeprazol 20mg Critico')
        ->assertDontSee('Paracetamol 500mg Normal')
        ->assertDontSee('Loratadina 10mg Agotado')
        // Toggle Crítico apaga el filtro
        ->call('setFiltroStock', 'critico')
        ->assertSet('filtroStock', '')
        ->assertSee('Paracetamol 500mg Normal')
        ->assertSee('Omeprazol 20mg Critico')
        ->assertSee('Loratadina 10mg Agotado')
        // Filtrar por Agotado
        ->call('setFiltroStock', 'agotado')
        ->assertSet('filtroStock', 'agotado')
        ->assertSee('Loratadina 10mg Agotado')
        ->assertDontSee('Paracetamol 500mg Normal')
        ->assertDontSee('Omeprazol 20mg Critico')
        // Filtrar por Normal
        ->call('setFiltroStock', 'normal')
        ->assertSet('filtroStock', 'normal')
        ->assertSee('Paracetamol 500mg Normal')
        ->assertDontSee('Omeprazol 20mg Critico')
        ->assertDontSee('Loratadina 10mg Agotado');
});

test('accion de devolucion reingresa stock al lote y seccion y reduce el costo de la proforma en curso', function () {
    $this->actingAs($this->admin);

    $seccion = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Farmacia Central',
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Gutiérrez',
        'cedula' => '9876543',
        'fecha_nacimiento' => '1990-01-01',
        'genero' => 'Masculino',
        'telefono' => '71112233',
        'direccion' => 'Calle Murillo #10',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'user_id' => $this->admin->id,
        'fecha_ingreso' => now()->toDateString(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $producto = Producto::create([
        'nombre' => 'Amoxicilina 500mg',
        'unidad_medida' => 'Cápsula',
        'ultimo_precio_venta' => 20.00,
        'stock_minimo' => 5,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'AMX-001',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 10,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 10.00,
        'precio_venta' => 20.00,
    ]);

    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'observaciones' => 'Tratamiento por 10 días',
        'activo' => true,
    ]);

    $detalle = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $producto->id,
        'cantidad' => 10,
        'indicaciones' => '1 cap cada 8 horas',
        'despachado' => true,
    ]);

    // Simular despacho previo de 10 unidades
    $movDespacho = MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'cantidad' => 10,
        'tipo_movimiento' => 'Salida Receta',
        'receta_id' => $receta->id,
        'proforma_id' => $proforma->id,
        'seccion_origen_id' => $seccion->id,
        'user_id' => $this->admin->id,
    ]);

    // Costo de proforma = 10 * 20 = 200.00 Bs.
    $proforma->recalcularTotal();
    expect((float) $proforma->fresh()->costo_total)->toBe(200.00);

    // 1. Abrir modal y procesar devolución parcial de 4 unidades (alta médica anticipada)
    Livewire::test(MovimientosIndex::class)
        ->assertSee('AMX-001')
        ->assertSee('Devolver')
        ->call('abrirModalDevolucion', $movDespacho->id)
        ->assertSet('modalDevolucionOpen', true)
        ->assertSet('dev_cantidad_disponible', 10)
        ->set('devolucion_cantidad', 4)
        ->set('devolucion_seccion_id', $seccion->id)
        ->set('devolucion_motivo', 'Alta médica antes de completar dosis')
        ->call('procesarDevolucion')
        ->assertDispatched('swal')
        ->assertSet('modalDevolucionOpen', false);

    // 2. Verificar reingreso al Lote y a LoteSeccion (+4)
    expect($lote->fresh()->cantidad_actual)->toBe(14);
    expect($lote->stockEnSeccion($seccion->id))->toBe(14);

    // 3. Verificar recálculo de la proforma: 6 unidades netas * 20.00 = 120.00 Bs.
    expect((float) $proforma->fresh()->costo_total)->toBe(120.00);
    expect((float) $proforma->totalDespachosFarmacia())->toBe(120.00);

    // 4. Detalle de receta ya no está completado al 100%
    expect($detalle->fresh()->despachado)->toBeFalse();

    // 5. El movimiento original ahora tiene 6 disponibles para devolver
    expect($movDespacho->fresh()->cantidadDisponibleParaDevolucion())->toBe(6);
    expect($movDespacho->fresh()->totalDevuelto())->toBe(4);

    // 6. Asiento de devolución en Kardex
    $movDev = MovimientoInventario::where('movimiento_origen_id', $movDespacho->id)->first();
    expect($movDev)->not->toBeNull();
    expect($movDev->tipo_movimiento)->toBe('Devolución');
    expect($movDev->cantidad)->toBe(4);
    expect($movDev->seccion_destino_id)->toBe($seccion->id);
    expect($movDev->observaciones)->toContain('Alta médica antes de completar dosis');
});

test('devolucion no se permite si la proforma no esta en curso o el usuario no tiene permisos', function () {
    $rolPersonal = Rol::firstOrCreate(['nombre' => 'Personal'], ['descripcion' => 'Personal Sin Permiso']);
    $personal = User::factory()->create([
        'rol_id' => $rolPersonal->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'nombres' => 'Lucía',
        'apellido_paterno' => 'Paredes',
        'cedula' => '11223344',
        'fecha_nacimiento' => '1995-03-20',
        'genero' => 'Femenino',
    ]);

    $proformaCerrada = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'user_id' => $this->admin->id,
        'fecha_ingreso' => now()->toDateString(),
        'estado' => 'Completado',
        'costo_total' => 100.00,
    ]);

    $producto = Producto::create([
        'nombre' => 'Diclofenaco 75mg',
        'unidad_medida' => 'Ampolla',
        'ultimo_precio_venta' => 15.00,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'DICLO-01',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 5,
        'fecha_vencimiento' => now()->addMonths(6),
        'precio_compra' => 8.00,
        'precio_venta' => 15.00,
    ]);

    $mov = MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'cantidad' => 3,
        'tipo_movimiento' => 'Salida Receta',
        'proforma_id' => $proformaCerrada->id,
        'user_id' => $this->admin->id,
    ]);

    // Intento con usuario administrador pero proforma cerrada
    $this->actingAs($this->admin);
    expect($mov->puedeSerDevuelto())->toBeFalse();

    Livewire::test(MovimientosIndex::class)
        ->call('abrirModalDevolucion', $mov->id)
        ->assertDispatched('swal')
        ->assertSet('modalDevolucionOpen', false);

    // Intento con usuario sin permiso de farmacia
    $this->actingAs($personal);
    Livewire::test(MovimientosIndex::class)
        ->call('abrirModalDevolucion', $mov->id)
        ->assertDispatched('swal')
        ->assertSet('modalDevolucionOpen', false);
});

test('puede procesar devolucion de consumos extras reduciendo la cantidad en la proforma', function () {
    $this->actingAs($this->admin);

    $seccion = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Quirófano',
        'es_almacen_principal' => false,
        'activo' => true,
    ]);

    $paciente = Paciente::create([
        'nombres' => 'Raúl',
        'apellido_paterno' => 'Flores',
        'cedula' => '55667788',
        'fecha_nacimiento' => '1988-10-10',
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'user_id' => $this->admin->id,
        'fecha_ingreso' => now()->toDateString(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $producto = Producto::create([
        'nombre' => 'Sutura Catgut 3-0',
        'unidad_medida' => 'Pieza',
        'ultimo_precio_venta' => 25.00,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'SUT-30',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 15,
        'fecha_vencimiento' => now()->addYears(2),
        'precio_compra' => 15.00,
        'precio_venta' => 25.00,
    ]);

    LoteSeccion::firstOrCreate(
        ['lote_id' => $lote->id, 'seccion_id' => $seccion->id],
        ['cantidad_actual' => 5]
    );

    // Consumo extra de 3 piezas en proforma
    $consumo = ConsumoExtra::create([
        'proforma_id' => $proforma->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'precio_unitario' => 25.00,
        'user_id' => $this->admin->id,
        'observaciones' => '[Quirófano] Material utilizado en cirugía',
    ]);

    $movConsumo = MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'cantidad' => 3,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proforma->id,
        'seccion_origen_id' => $seccion->id,
        'user_id' => $this->admin->id,
    ]);

    // Costo proforma inicial: 3 * 25.00 = 75.00 Bs.
    $proforma->recalcularTotal();
    expect((float) $proforma->fresh()->costo_total)->toBe(75.00);

    // Devolver 1 pieza sobrante
    Livewire::test(MovimientosIndex::class)
        ->call('abrirModalDevolucion', $movConsumo->id)
        ->assertSet('modalDevolucionOpen', true)
        ->set('devolucion_cantidad', 1)
        ->set('devolucion_seccion_id', $seccion->id)
        ->set('devolucion_motivo', 'Material no utilizado en procedimiento')
        ->call('procesarDevolucion')
        ->assertDispatched('swal');

    // 1. Stock se restituye (+1)
    expect($lote->fresh()->cantidad_actual)->toBe(16);
    expect($lote->stockEnSeccion($seccion->id))->toBe(16);

    // 2. Registro de consumo en proforma se reduce a 2 unidades
    expect($consumo->fresh()->cantidad)->toBe(2);

    // 3. Proforma se recalcula: 2 * 25.00 = 50.00 Bs.
    expect((float) $proforma->fresh()->costo_total)->toBe(50.00);

    // 4. Saldo disponible en el movimiento = 2
    expect($movConsumo->fresh()->cantidadDisponibleParaDevolucion())->toBe(2);
});

test('devolucion actualiza el saldo pendiente en la bandeja de despachos y en el reporte de kardex', function () {
    $this->actingAs($this->admin);

    $paciente = Paciente::create([
        'nombres' => 'Fernando',
        'apellido_paterno' => 'Quiroga',
        'cedula' => 'CI-DEV-001',
        'genero' => 'Masculino',
    ]);

    $proforma = Proforma::create([
        'paciente_id' => $paciente->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internacion',
        'costo_total' => 0.00,
        'estado' => 'En Curso',
        'fecha_ingreso' => now(),
    ]);

    $seccion = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Farmacia Piso 3',
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $producto = Producto::create([
        'nombre' => 'Omeprazol 40mg IV',
        'unidad_medida' => 'Frasco',
        'ultimo_precio_venta' => 20.00,
        'stock_minimo' => 5,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'codigo_lote' => 'OME-001',
        'cantidad_ingresada' => 10,
        'cantidad_actual' => 10,
        'fecha_vencimiento' => now()->addMonths(12),
        'precio_compra' => 10.00,
        'precio_venta' => 20.00,
    ]);

    LoteSeccion::firstOrCreate(
        ['lote_id' => $lote->id, 'seccion_id' => $seccion->id],
        ['cantidad_actual' => 10]
    );

    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->admin->id,
        'activo' => true,
    ]);

    $detalle = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $producto->id,
        'cantidad' => 5,
        'despachado' => false,
    ]);

    // Despachar 5
    $movDespacho = MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'lote_id' => $lote->id,
        'seccion_origen_id' => $seccion->id,
        'tipo_movimiento' => 'Salida Receta',
        'cantidad' => 5,
        'user_id' => $this->admin->id,
        'proforma_id' => $proforma->id,
        'receta_id' => $receta->id,
    ]);
    $lote->decrement('cantidad_actual', 5);
    LoteSeccion::where('lote_id', $lote->id)->where('seccion_id', $seccion->id)->decrement('cantidad_actual', 5);
    $detalle->update(['despachado' => true]);

    // Verificar que DespachosIndex ve 0 pendientes
    Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->assertSet('despachosItems.'.$detalle->id.'.saldo_pendiente', 0)
        ->assertSet('despachosItems.'.$detalle->id.'.despachadas_previas', 5);

    // Procesar Devolución de 2 unidades en MovimientosIndex
    Livewire::test(MovimientosIndex::class)
        ->call('abrirModalDevolucion', $movDespacho->id)
        ->set('devolucion_cantidad', 2)
        ->set('devolucion_seccion_id', $seccion->id)
        ->set('devolucion_motivo', 'Paciente suspendió tratamiento')
        ->call('procesarDevolucion')
        ->assertDispatched('swal');

    // DespachosIndex ahora debe reflejar saldo_pendiente = 2 y despachadas_previas = 3
    Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->assertSet('despachosItems.'.$detalle->id.'.saldo_pendiente', 2)
        ->assertSet('despachosItems.'.$detalle->id.'.despachadas_previas', 3);

    // Verificar en ReporteMovimientosService
    expect(ReporteMovimientosService::esEntrada('Devolución'))->toBeTrue();

    $reporteService = app(ReporteMovimientosService::class);
    $datosReporte = $reporteService->generar(
        now()->subDay()->format('Y-m-d'),
        now()->addDay()->format('Y-m-d'),
        $producto->id,
        $this->sucursal->id
    );

    expect($datosReporte['items'])->not->toBeEmpty();
    $itemOmeprazol = $datosReporte['items'][0];
    $movs = collect($itemOmeprazol['movimientos']);
    $movDev = $movs->firstWhere('tipo_movimiento', 'Devolución');
    expect($movDev)->not->toBeNull();
    expect($movDev['es_entrada'])->toBeTrue();
    expect($movDev['cantidad_entrada'])->toBe(2);
    expect($movDev['referencia'])->toContain('Paciente suspendió tratamiento');
});
