<?php

use App\Models\ConsumoExtra;
use App\Models\Lote;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\ProformaPago;
use App\Models\ProformaServicio;
use App\Models\Receta;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\User;

test('lote creation updates parent producto ultimo_precio_venta', function () {
    $producto = Producto::factory()->create([
        'ultimo_precio_venta' => 10.00,
    ]);

    $lote = Lote::factory()->create([
        'producto_id' => $producto->id,
        'precio_venta' => 45.50,
    ]);

    expect($producto->fresh()->ultimo_precio_venta)->toEqual('45.50');

    $lote->update(['precio_venta' => 60.00]);
    expect($producto->fresh()->ultimo_precio_venta)->toEqual('60.00');
});

test('only one receta can be active per proforma', function () {
    $proforma = Proforma::factory()->create();
    $doctor = User::factory()->create();

    $receta1 = Receta::factory()->create([
        'proforma_id' => $proforma->id,
        'user_id' => $doctor->id,
        'activo' => true,
    ]);

    expect($receta1->fresh()->activo)->toBeTrue();

    $receta2 = Receta::factory()->create([
        'proforma_id' => $proforma->id,
        'user_id' => $doctor->id,
        'activo' => true,
    ]);

    expect($receta1->fresh()->activo)->toBeFalse();
    expect($receta2->fresh()->activo)->toBeTrue();
});

test('proforma recalculates total with servicios and consumos extras', function () {
    $proforma = Proforma::factory()->create(['costo_total' => 0.00]);

    $servicio = Servicio::factory()->create();

    ProformaServicio::factory()->create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $servicio->id,
        'costo_final' => 150.00,
    ]);

    $producto = Producto::factory()->create();

    ConsumoExtra::factory()->create([
        'proforma_id' => $proforma->id,
        'producto_id' => $producto->id,
        'cantidad' => 2,
        'precio_unitario' => 25.00,
    ]);

    $proforma->recalcularTotal();

    expect((float) $proforma->fresh()->costo_total)->toEqual(200.00);

    ProformaPago::factory()->create([
        'proforma_id' => $proforma->id,
        'monto' => 120.00,
    ]);

    expect($proforma->totalPagado())->toEqual(120.00);
    expect($proforma->saldoPendiente())->toEqual(80.00);
});

test('auditable trait sets usuario_creador_id on authenticated user', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $sucursal = Sucursal::factory()->create();

    expect($sucursal->usuario_creador_id)->toBe($user->id);
    expect($sucursal->usuarioCreador->id)->toBe($user->id);
});
