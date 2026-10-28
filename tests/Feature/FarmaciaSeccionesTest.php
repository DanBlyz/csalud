<?php

use App\Livewire\Farmacia\DespachosIndex;
use App\Livewire\Farmacia\LotesIndex;
use App\Livewire\Farmacia\ProductosIndex;
use App\Livewire\Farmacia\SeccionesIndex;
use App\Livewire\Proformas\ProformaDetalle;
use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    (new PermisoSeeder)->run();
    $this->permisoFarmacia = Permiso::find(9);

    $this->sucursal = Sucursal::create([
        'nombre' => 'Hospital Central',
        'ciudad' => 'La Paz',
        'direccion' => 'Av. 6 de Agosto #100',
        'telefono' => '22000000',
        'es_matriz' => true,
        'activo' => true,
    ]);

    $this->farmaceutico = User::factory()->create([
        'rol_id' => $this->rolFarmacia->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);
    $this->farmaceutico->permisos()->sync(array_merge([9], range(47, 59)));

    $this->usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolFarmacia->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    // Crear secciones hospitalarias estándar para la sucursal
    $this->seccionPrincipal = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Farmacia Central',
        'descripcion' => 'Almacén general y recepción',
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $this->seccionEmergencias = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Emergencias',
        'descripcion' => 'Stock satélite para guardia médica',
        'es_almacen_principal' => false,
        'activo' => true,
    ]);

    $this->seccionQuirofano = Seccion::create([
        'sucursal_id' => $this->sucursal->id,
        'nombre' => 'Quirófano',
        'descripcion' => 'Stock satélite quirófano y anestesia',
        'es_almacen_principal' => false,
        'activo' => true,
    ]);

    $this->producto = Producto::create([
        'nombre' => 'Paracetamol 500mg',
        'unidad_medida' => 'Tableta',
        'ultimo_precio_venta' => 1.50,
        'stock_minimo' => 10,
    ]);
});

test('un usuario con permiso 9 puede acceder al catálogo de secciones y áreas', function () {
    $this->actingAs($this->farmaceutico);

    $this->get(route('farmacia.secciones'))
        ->assertOk()
        ->assertSee('Secciones y Áreas Hospitalarias')
        ->assertSee('Farmacia Central')
        ->assertSee('Emergencias');
});

test('un usuario sin permiso 9 es denegado al acceder a secciones', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $this->get(route('farmacia.secciones'))
        ->assertForbidden();
});

test('puede crear una nueva sección hospitalaria mediante Livewire', function () {
    $this->actingAs($this->farmaceutico);

    Livewire::test(SeccionesIndex::class)
        ->call('abrirModal')
        ->set('sucursal_id', $this->sucursal->id)
        ->set('nombre', 'Unidad de Terapia Intensiva (UTI)')
        ->set('descripcion', 'Pabellón de cuidados críticos')
        ->set('es_almacen_principal', false)
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('swal');

    $this->assertDatabaseHas('secciones', [
        'nombre' => 'Unidad de Terapia Intensiva (UTI)',
        'sucursal_id' => $this->sucursal->id,
    ]);
});

test('un nuevo lote se asigna automáticamente al almacén principal de la sucursal', function () {
    $this->actingAs($this->farmaceutico);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-PRUEBA-01',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 1.50,
    ]);

    expect($lote->stockEnSeccion($this->seccionPrincipal->id))->toBe(100);
    expect($lote->stockEnSeccion($this->seccionEmergencias->id))->toBe(0);

    $this->assertDatabaseHas('lote_secciones', [
        'lote_id' => $lote->id,
        'seccion_id' => $this->seccionPrincipal->id,
        'cantidad_actual' => 100,
    ]);
});

test('puede transferir existencias entre áreas hospitalarias con trazabilidad en Kardex', function () {
    $this->actingAs($this->farmaceutico);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-DIST-01',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 1.50,
    ]);

    // Transferir 30 unidades de Farmacia Central a Emergencias
    Livewire::test(LotesIndex::class)
        ->call('abrirModalTransferencia', $lote->id)
        ->set('seccion_origen_id', $this->seccionPrincipal->id)
        ->set('seccion_destino_id', $this->seccionEmergencias->id)
        ->set('cantidad_transferir', 30)
        ->set('motivo_transferencia', 'Dotación de guardia para emergencias')
        ->call('transferirStock')
        ->assertHasNoErrors()
        ->assertDispatched('swal');

    // Verificar stocks por sección
    expect($lote->fresh()->stockEnSeccion($this->seccionPrincipal->id))->toBe(70);
    expect($lote->fresh()->stockEnSeccion($this->seccionEmergencias->id))->toBe(30);
    // El stock consolidado total debe mantenerse idéntico
    expect($lote->fresh()->cantidad_actual)->toBe(100);

    // Verificar asiento en Kardex
    $this->assertDatabaseHas('movimientos_inventario', [
        'lote_id' => $lote->id,
        'tipo_movimiento' => 'Transferencia Interna',
        'seccion_origen_id' => $this->seccionPrincipal->id,
        'seccion_destino_id' => $this->seccionEmergencias->id,
        'cantidad' => 30,
        'user_id' => $this->farmaceutico->id,
    ]);
});

test('rechaza la transferencia si el área origen no tiene existencias suficientes', function () {
    $this->actingAs($this->farmaceutico);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-INSUF-01',
        'cantidad_ingresada' => 20,
        'cantidad_actual' => 20,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 1.50,
    ]);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalTransferencia', $lote->id)
        ->set('seccion_origen_id', $this->seccionPrincipal->id)
        ->set('seccion_destino_id', $this->seccionEmergencias->id)
        ->set('cantidad_transferir', 50) // Mayor a los 20 disponibles
        ->call('transferirStock')
        ->assertDispatched('swal');

    // El stock no debió cambiar
    expect($lote->fresh()->stockEnSeccion($this->seccionPrincipal->id))->toBe(20);
    expect($lote->fresh()->stockEnSeccion($this->seccionEmergencias->id))->toBe(0);
});

test('el catálogo de productos calcula el stock por sección seleccionada', function () {
    $this->actingAs($this->farmaceutico);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-CAT-01',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 1.50,
    ]);

    // Transferir 25 a Emergencias
    $lote->transferirASeccion($this->seccionPrincipal->id, $this->seccionEmergencias->id, 25, 'Test', $this->farmaceutico->id);

    // Cuando no hay filtro de sección: stock consolidado = 100
    Livewire::test(ProductosIndex::class)
        ->assertSee('100')
        ->set('filtroSeccion', $this->seccionEmergencias->id)
        ->assertSee('25'); // En Emergencias hay 25
});

test('puede despachar medicamentos asignando una sección satélite y descontando atómicamente de esa sección', function () {
    $this->actingAs($this->farmaceutico);

    $paciente = Paciente::factory()->create();
    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0,
    ]);

    $receta = Receta::create([
        'proforma_id' => $proforma->id,
        'user_id' => $this->farmaceutico->id,
        'fecha' => now(),
        'activo' => true,
    ]);

    $det = RecetaDetalle::create([
        'receta_id' => $receta->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 10,
        'indicaciones' => 'Cada 8 hrs',
        'despachado' => false,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-SEC-DESP',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 2.00,
    ]);

    // Transferir 20 a Emergencias
    $lote->transferirASeccion($this->seccionPrincipal->id, $this->seccionEmergencias->id, 20, 'Traslado', $this->farmaceutico->id);
    expect($lote->stockEnSeccion($this->seccionEmergencias->id))->toBe(20);
    expect($lote->stockEnSeccion($this->seccionPrincipal->id))->toBe(30);

    // Despachar 5 unidades seleccionando explícitamente Emergencias
    Livewire::test(DespachosIndex::class)
        ->call('abrirModalDespacho', $receta->id)
        ->call('cambiarSeccionItem', $det->id, $this->seccionEmergencias->id)
        ->set('despachosItems.'.$det->id.'.cantidad_despachar', 5)
        ->call('procesarDespacho')
        ->assertDispatched('swal');

    // Emergencias ahora tiene 15, Farmacia Central sigue con 30, consolidado 45
    expect($lote->fresh()->stockEnSeccion($this->seccionEmergencias->id))->toBe(15);
    expect($lote->fresh()->stockEnSeccion($this->seccionPrincipal->id))->toBe(30);
    expect($lote->fresh()->cantidad_actual)->toBe(45);

    // El movimiento en Kardex registró seccion_origen_id = Emergencias
    $mov = MovimientoInventario::where('receta_id', $receta->id)->first();
    expect($mov)->not->toBeNull();
    expect($mov->seccion_origen_id)->toBe($this->seccionEmergencias->id);
    expect($mov->cantidad)->toBe(5);
});

test('puede registrar y revertir consumo extra asignando área hospitalaria en proforma detalle', function () {
    $permisoAgregar = Permiso::firstOrCreate(['nombre' => 'proformas.consumos.agregar'], ['descripcion' => 'Agregar consumos extras']);
    $permisoEliminar = Permiso::firstOrCreate(['nombre' => 'proformas.consumos.eliminar'], ['descripcion' => 'Eliminar consumos extras']);
    $permisoVer = Permiso::firstOrCreate(['nombre' => 'proformas.consumos.ver'], ['descripcion' => 'Ver consumos extras']);
    $this->farmaceutico->permisos()->syncWithoutDetaching([$permisoAgregar->id, $permisoEliminar->id, $permisoVer->id]);

    $this->actingAs($this->farmaceutico);

    $paciente = Paciente::factory()->create();
    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $paciente->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => now(),
        'estado' => 'En Curso',
        'costo_total' => 0,
    ]);

    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-CONS-SEC',
        'cantidad_ingresada' => 40,
        'cantidad_actual' => 40,
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 0.50,
        'precio_venta' => 2.50,
    ]);

    // Transferir 15 a Emergencias
    $lote->transferirASeccion($this->seccionPrincipal->id, $this->seccionEmergencias->id, 15, 'Suministro', $this->farmaceutico->id);

    // Cargar 4 consumos desde Emergencias
    $component = Livewire::test(ProformaDetalle::class, ['proforma' => $proforma])
        ->call('abrirModalConsumo')
        ->set('consumo_seccion_id', $this->seccionEmergencias->id)
        ->call('seleccionarInsumoConsumo', $this->producto->id)
        ->set('consumo_cantidad', 4)
        ->set('consumo_observaciones', 'Uso en guardia')
        ->call('agregarConsumoACola')
        ->call('registrarConsumoExtra')
        ->assertDispatched('swal');

    // Emergencias se redujo en 4 (15 - 4 = 11)
    expect($lote->fresh()->stockEnSeccion($this->seccionEmergencias->id))->toBe(11);
    expect($lote->fresh()->cantidad_actual)->toBe(36);

    $consumo = ConsumoExtra::where('proforma_id', $proforma->id)->first();
    expect($consumo)->not->toBeNull();
    expect($consumo->observaciones)->toContain('Emergencias');

    // Eliminar el consumo: debe reintegrar a Emergencias
    $component->call('eliminarConsumoExtra', $consumo->id)
        ->assertDispatched('swal');

    expect($lote->fresh()->stockEnSeccion($this->seccionEmergencias->id))->toBe(15);
    expect($lote->fresh()->cantidad_actual)->toBe(40);
});

test('puede registrar lote con fecha_compra y visualizarlo en el listado', function () {
    $this->actingAs($this->farmaceutico);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalLote')
        ->assertSet('modalLoteOpen', true)
        ->set('sucursal_id', $this->sucursal->id)
        ->set('producto_id', $this->producto->id)
        ->set('codigo_lote', 'LOT-COMPRA-2026')
        ->set('cantidad_ingresada', 50)
        ->set('fecha_compra', '2026-10-15')
        ->set('fecha_vencimiento', '2027-10-15')
        ->set('precio_compra', '12.50')
        ->set('precio_venta', '25.00')
        ->call('guardarLote')
        ->assertDispatched('swal');

    $lote = Lote::where('codigo_lote', 'LOT-COMPRA-2026')->first();
    expect($lote)->not->toBeNull();
    expect($lote->fecha_compra?->toDateString())->toBe('2026-10-15');
    expect((float) $lote->precio_compra)->toBe(12.50);
    expect((float) $lote->precio_venta)->toBe(25.00);

    Livewire::test(LotesIndex::class)
        ->assertSee('LOT-COMPRA-2026')
        ->assertSee('Compra: 15/10/2026');
});

test('puede editar y corregir datos de un lote con autorizacion farmacia.lotes.editar', function () {
    $lote = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOT-ERRONEO',
        'cantidad_ingresada' => 30,
        'cantidad_actual' => 30,
        'fecha_compra' => '2026-09-01',
        'fecha_vencimiento' => '2027-01-01',
        'precio_compra' => 5.00,
        'precio_venta' => 10.00,
    ]);

    // 1. Usuario sin permiso es denegado
    $this->actingAs($this->usuarioSinPermiso);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalEdicion', $lote->id)
        ->assertSet('modalEditarLoteOpen', false)
        ->assertDispatched('swal');

    // 2. Usuario con permiso farmacia.lotes.editar puede abrir y actualizar
    $this->actingAs($this->farmaceutico);

    Livewire::test(LotesIndex::class)
        ->call('abrirModalEdicion', $lote->id)
        ->assertSet('modalEditarLoteOpen', true)
        ->assertSet('edit_codigo_lote', 'LOT-ERRONEO')
        ->set('edit_codigo_lote', 'LOT-CORREGIDO-OK')
        ->set('edit_fecha_compra', '2026-10-20')
        ->set('edit_fecha_vencimiento', '2028-05-15')
        ->set('edit_precio_compra', '8.50')
        ->set('edit_precio_venta', '18.00')
        ->call('actualizarLote')
        ->assertDispatched('swal')
        ->assertSet('modalEditarLoteOpen', false);

    $loteActualizado = $lote->fresh();
    expect($loteActualizado->codigo_lote)->toBe('LOT-CORREGIDO-OK');
    expect($loteActualizado->fecha_compra->toDateString())->toBe('2026-10-20');
    expect($loteActualizado->fecha_vencimiento->toDateString())->toBe('2028-05-15');
    expect((float) $loteActualizado->precio_compra)->toBe(8.50);
    expect((float) $loteActualizado->precio_venta)->toBe(18.00);

    // Comprobar que sincronizó ultimo_precio_venta en el producto
    expect((float) $this->producto->fresh()->ultimo_precio_venta)->toBe(18.00);
});
