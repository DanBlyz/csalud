<?php

use App\Livewire\Farmacia\MarcasIndex;
use App\Livewire\Farmacia\ProveedoresIndex;
use App\Models\Lote;
use App\Models\Marca;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador Total']
    );

    $this->rolFarmacia = Rol::firstOrCreate(
        ['nombre' => 'Farmacéutico'],
        ['descripcion' => 'Encargado de Farmacia e Inventario']
    );

    DB::table('permisos')->insertOrIgnore([
        'id' => 9,
        'nombre' => 'despachar-farmacia',
        'descripcion' => 'Despacho de medicamentos y control de lotes',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->permisoFarmacia = Permiso::find(9);

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
        'activo' => true,
    ]);

    $this->farmaceutico = User::factory()->create([
        'rol_id' => $this->rolFarmacia->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);
    $this->farmaceutico->permisos()->sync([$this->permisoFarmacia->id]);

    $this->usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolFarmacia->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);
});

test('un usuario con permiso 9 puede acceder a marcas y proveedores', function () {
    $this->actingAs($this->farmaceutico);

    $this->get(route('farmacia.marcas'))
        ->assertOk()
        ->assertSee('Marcas y Laboratorios');

    $this->get(route('farmacia.proveedores'))
        ->assertOk()
        ->assertSee('Proveedores y Droguerías');
});

test('un usuario sin permiso 9 no puede acceder a marcas ni proveedores', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $this->get(route('farmacia.marcas'))
        ->assertForbidden();

    $this->get(route('farmacia.proveedores'))
        ->assertForbidden();
});

test('puede crear, editar y listar marcas en el modulo de farmacia', function () {
    $this->actingAs($this->farmaceutico);

    // 1. Crear marca
    Livewire::test(MarcasIndex::class)
        ->call('abrirModal')
        ->set('nombre', 'Laboratorios Bagó')
        ->set('descripcion', 'Línea farmacéutica internacional')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('modalOpen', false);

    $marca = Marca::where('nombre', 'Laboratorios Bagó')->first();
    expect($marca)->not->toBeNull()
        ->and($marca->descripcion)->toBe('Línea farmacéutica internacional');

    // 2. Editar marca
    Livewire::test(MarcasIndex::class)
        ->call('abrirModal', $marca->id)
        ->assertSet('nombre', 'Laboratorios Bagó')
        ->set('nombre', 'Bagó Bolivia S.A.')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($marca->fresh()->nombre)->toBe('Bagó Bolivia S.A.');
});

test('no permite eliminar una marca si tiene productos vinculados', function () {
    $this->actingAs($this->farmaceutico);

    $marca = Marca::create([
        'nombre' => 'Laboratorios INTI',
        'descripcion' => 'Industria farmacéutica nacional',
    ]);

    Producto::create([
        'nombre' => 'Mentisan Ungüento 15g',
        'marca_id' => $marca->id,
        'unidad_medida' => 'Lata',
        'ultimo_precio_venta' => 8.50,
        'stock_minimo' => 10,
    ]);

    Livewire::test(MarcasIndex::class)
        ->call('eliminar', $marca->id)
        ->assertDispatched('swal');

    expect(Marca::find($marca->id))->not->toBeNull();
});

test('puede eliminar una marca sin productos vinculados', function () {
    $this->actingAs($this->farmaceutico);

    $marca = Marca::create([
        'nombre' => 'Marca Temporal',
        'descripcion' => 'Sin productos',
    ]);

    Livewire::test(MarcasIndex::class)
        ->call('eliminar', $marca->id);

    expect(Marca::find($marca->id))->toBeNull();
});

test('puede crear, editar y listar proveedores en el modulo de farmacia', function () {
    $this->actingAs($this->farmaceutico);

    // 1. Crear proveedor
    Livewire::test(ProveedoresIndex::class)
        ->call('abrirModal')
        ->set('razon_social', 'Droguería INTI S.A.')
        ->set('nit_ruc', '1020304050')
        ->set('contacto_nombre', 'Lic. Juan Pérez')
        ->set('celular', '71234567')
        ->set('correo', 'ventas@inti.com.bo')
        ->set('direccion', 'Calle Lucas Jaimes #1234')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('modalOpen', false);

    $proveedor = Proveedor::where('razon_social', 'Droguería INTI S.A.')->first();
    expect($proveedor)->not->toBeNull()
        ->and($proveedor->nit_ruc)->toBe('1020304050')
        ->and($proveedor->celular)->toBe('71234567');

    // 2. Editar proveedor
    Livewire::test(ProveedoresIndex::class)
        ->call('abrirModal', $proveedor->id)
        ->assertSet('razon_social', 'Droguería INTI S.A.')
        ->set('contacto_nombre', 'Ing. Carlos Mendoza')
        ->call('guardar')
        ->assertHasNoErrors();

    expect($proveedor->fresh()->contacto_nombre)->toBe('Ing. Carlos Mendoza');
});

test('no permite eliminar un proveedor si tiene lotes vinculados', function () {
    $this->actingAs($this->farmaceutico);

    $proveedor = Proveedor::create([
        'razon_social' => 'Distribuidora Farmacéutica del Valle',
        'nit_ruc' => '2030405060',
    ]);

    $producto = Producto::create([
        'nombre' => 'Ibuprofeno 400mg',
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'stock_minimo' => 20,
    ]);

    Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $producto->id,
        'proveedor_id' => $proveedor->id,
        'codigo_lote' => 'LOT-VALLE-001',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'precio_compra' => 0.80,
        'precio_venta' => 1.50,
        'fecha_vencimiento' => now()->addYear(),
    ]);

    Livewire::test(ProveedoresIndex::class)
        ->call('eliminar', $proveedor->id)
        ->assertDispatched('swal');

    expect(Proveedor::find($proveedor->id))->not->toBeNull();
});

test('puede ver el historial de lotes provistos por un proveedor', function () {
    $this->actingAs($this->farmaceutico);

    $proveedor = Proveedor::create([
        'razon_social' => 'Proveedor con Lotes',
        'nit_ruc' => '3040506070',
    ]);

    Livewire::test(ProveedoresIndex::class)
        ->call('verLotes', $proveedor->id)
        ->assertSet('modalLotesOpen', true)
        ->assertSet('proveedorDetalleId', $proveedor->id)
        ->call('cerrarModalLotes')
        ->assertSet('modalLotesOpen', false)
        ->assertSet('proveedorDetalleId', null);
});

test('puede eliminar un proveedor sin lotes vinculados', function () {
    $this->actingAs($this->farmaceutico);

    $proveedor = Proveedor::create([
        'razon_social' => 'Proveedor Sin Lotes',
    ]);

    Livewire::test(ProveedoresIndex::class)
        ->call('eliminar', $proveedor->id);

    expect(Proveedor::find($proveedor->id))->toBeNull();
});
