<?php

use App\Livewire\Farmacia\DespachosIndex;
use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador']
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

    $this->seccion = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Farmacia Central',
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $this->marca = Marca::create([
        'nombre' => 'Laboratorios Bagó',
    ]);

    $this->paciente = Paciente::create([
        'nombres' => 'Juan',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'Gómez',
        'cedula' => '12345678',
        'fecha_nacimiento' => '1990-05-15',
        'genero' => 'M',
        'activo' => true,
    ]);

    $this->proforma = Proforma::create([
        'paciente_id' => $this->paciente->id,
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->admin->id,
        'medico_id' => $this->admin->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0.00,
    ]);

    $this->producto = Producto::create([
        'nombre' => 'Paracetamol 500mg',
        'marca_id' => $this->marca->id,
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 2.50,
        'stock_minimo' => 10,
        'activo' => true,
    ]);

    $this->lote = Lote::create([
        'producto_id' => $this->producto->id,
        'sucursal_id' => $this->sucursal->id,
        'codigo_lote' => 'LOTE-PARAC-01',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'precio_compra' => 1.00,
        'precio_venta' => 2.50,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'estado' => 'Disponible',
    ]);
});

test('los botones de Crear Receta y Despachar Insumos Extras son visibles en DespachosIndex', function () {
    $this->actingAs($this->admin);

    Livewire::test(DespachosIndex::class)
        ->assertOk()
        ->assertSee('Crear Receta')
        ->assertSee('Despachar Insumos Extras');
});

test('puede buscar proforma en curso y prescribir una nueva receta medica desde farmacia', function () {
    $this->actingAs($this->admin);

    Livewire::test(DespachosIndex::class)
        ->assertOk()
        ->call('abrirModalCrearReceta')
        ->assertSet('modalCrearRecetaOpen', true)
        ->set('buscarProformaReceta', 'Pérez')
        ->call('seleccionarProformaReceta', $this->proforma->id)
        ->assertSet('receta_proforma_id', $this->proforma->id)
        ->set('buscarMedicamentoReceta', 'Paracetamol')
        ->call('seleccionarMedicamentoReceta', $this->producto->id)
        ->assertSet('receta_nuevo_producto_id', $this->producto->id)
        ->set('receta_nuevo_cantidad', 5)
        ->set('receta_nuevo_indicaciones', '1 tableta cada 8 horas')
        ->call('agregarMedicamentoAReceta')
        ->assertCount('receta_medicamentos', 1)
        ->call('guardarNuevaReceta')
        ->assertDispatched('swal')
        ->assertSet('modalCrearRecetaOpen', false);

    $receta = Receta::where('proforma_id', $this->proforma->id)->first();
    expect($receta)->not->toBeNull();
    expect($receta->activo)->toBeTrue();
    expect($receta->detalles)->toHaveCount(1);
    expect($receta->detalles->first()->producto_id)->toBe($this->producto->id);
    expect($receta->detalles->first()->cantidad)->toBe(5);
    expect($receta->detalles->first()->indicaciones)->toBe('1 tableta cada 8 horas');
});

test('no permite crear receta si la proforma esta finalizada o cancelada', function () {
    $this->actingAs($this->admin);

    $this->proforma->update(['estado' => 'Pagada']);

    Livewire::test(DespachosIndex::class)
        ->call('abrirModalCrearReceta')
        ->call('seleccionarProformaReceta', $this->proforma->id)
        ->assertDispatched('swal')
        ->assertSet('receta_proforma_id', null);
});

test('puede buscar proforma y despachar insumos extras directamente descargando stock por seccion', function () {
    $this->actingAs($this->admin);

    Livewire::test(DespachosIndex::class)
        ->assertOk()
        ->call('abrirModalDespachoExtraDirecto')
        ->assertSet('modalDespachoExtraDirectoOpen', true)
        ->set('buscarProformaExtraDirecto', '12345678')
        ->call('seleccionarProformaExtraDirecto', $this->proforma->id)
        ->assertSet('extra_directo_proforma_id', $this->proforma->id)
        ->assertSet('extra_directo_seccion_id', $this->seccion->id)
        ->call('seleccionarInsumoExtraDirecto', $this->producto->id)
        ->assertSet('extra_directo_producto_id', $this->producto->id)
        ->set('extra_directo_cantidad', 10)
        ->set('extra_directo_observaciones', 'Urgente quirófano')
        ->call('agregarItemExtraDirecto')
        ->assertCount('cola_despachos_extras_directos', 1)
        ->call('procesarDespachoExtraDirecto')
        ->assertDispatched('swal')
        ->assertSet('modalDespachoExtraDirectoOpen', false);

    // Verificar descuento en Lote y en LoteSeccion
    $this->lote->refresh();
    expect($this->lote->cantidad_actual)->toBe(90);
    expect($this->lote->stockEnSeccion($this->seccion->id))->toBe(90);

    // Verificar registro en ConsumoExtra
    $consumo = ConsumoExtra::where('proforma_id', $this->proforma->id)->first();
    expect($consumo)->not->toBeNull();
    expect($consumo->producto_id)->toBe($this->producto->id);
    expect($consumo->cantidad)->toBe(10);
    expect((float) $consumo->precio_unitario)->toBe(2.50);

    // Verificar Kardex MovimientoInventario
    $mov = MovimientoInventario::where('proforma_id', $this->proforma->id)
        ->where('producto_id', $this->producto->id)
        ->where('tipo_movimiento', 'Consumo Extra')
        ->first();
    expect($mov)->not->toBeNull();
    expect($mov->cantidad)->toBe(10);
    expect($mov->seccion_origen_id)->toBe($this->seccion->id);
});

test('calcula visualmente el costo total en el modal de despacho de receta y en despacho directo de extras', function () {
    $this->actingAs($this->admin);

    // 1. Crear una receta con un medicamento
    $receta = Receta::create([
        'proforma_id' => $this->proforma->id,
        'user_id' => $this->admin->id,
        'fecha_emision' => now(),
        'estado' => 'Pendiente',
        'activo' => true,
    ]);

    $recetaDetalle = $receta->detalles()->create([
        'producto_id' => $this->producto->id,
        'cantidad' => 4,
        'indicaciones' => '1 cada 6h',
        'costo_unitario' => 2.50,
    ]);

    // Asociar stock a la sección
    LoteSeccion::firstOrCreate(
        ['lote_id' => $this->lote->id, 'seccion_id' => $this->seccion->id],
        ['cantidad_actual' => 100]
    );

    // Probar modal de despacho de receta
    $test = Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->assertSet('modalDespachoOpen', true);

    // Al abrir, se sugiere despachar 1 unidad por defecto
    // 1 * 2.50 = 2.50
    expect($test->get('totalRecetaDespachar'))->toBe(2.50);
    expect($test->get('costoTotalDespacho'))->toBe(2.50);

    // Cambiar la cantidad a despachar a 3 unidades -> 3 * 2.50 = 7.50
    $test->set("despachosItems.{$recetaDetalle->id}.cantidad_despachar", 3);
    expect($test->get('totalRecetaDespachar'))->toBe(7.50);
    expect($test->get('costoTotalDespacho'))->toBe(7.50);
    $test->assertSee('Costo Total a Despachar:');
    $test->assertSee('7.50');

    // 2. Probar modal de despacho directo de extras
    $testExtra = Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespachoExtraDirecto')
        ->call('seleccionarProformaExtraDirecto', $this->proforma->id)
        ->call('seleccionarInsumoExtraDirecto', $this->producto->id)
        ->set('extra_directo_cantidad', 6)
        ->set('extra_directo_precio_unitario', 2.50)
        ->call('agregarItemExtraDirecto');

    // 6 * 2.50 = 15.00
    expect($testExtra->get('totalExtraDirectoDespachar'))->toBe(15.00);
    $testExtra->assertSee('Costo Total Extra:');
    $testExtra->assertSee('15.00');
});
