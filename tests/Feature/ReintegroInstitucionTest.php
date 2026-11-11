<?php

use App\Livewire\Farmacia\ReintegrosInstitucionIndex;
use App\Models\ConsumoExtra;
use App\Models\Institucion;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\RecetaDetalle;
use App\Models\ReintegroInstitucion;
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
        ['nombre' => 'OperadorSinFarmacia'],
        ['descripcion' => 'Personal sin acceso a farmacia']
    );

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sede Central',
        'ciudad' => 'La Paz',
        'direccion' => 'Av. Arce #100',
        'telefono' => '22114455',
        'estado' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->seccionFarmacia = Seccion::create([
        'nombre' => 'Farmacia Central',
        'sucursal_id' => $this->sucursal->id,
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $this->institucion = Institucion::create([
        'nombre' => 'Caja Nacional de Salud Convenio',
        'descripcion' => 'Convenio institucional',
        'estado' => 'Activo',
    ]);

    $this->otraInstitucion = Institucion::create([
        'nombre' => 'Seguro Universitario',
        'descripcion' => 'Otro convenio',
        'estado' => 'Activo',
    ]);

    $this->producto = Producto::create([
        'nombre' => 'Ceftriaxona 1g Inyectable',
        'unidad_medida' => 'Ampolla',
        'stock_minimo' => 5,
        'ultimo_precio_venta' => 30.00,
    ]);

    $this->loteOriginal = Lote::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'codigo_lote' => 'LOTE-ORIG-01',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 80,
        'fecha_compra' => now()->subMonths(2)->toDateString(),
        'fecha_vencimiento' => now()->addYear()->toDateString(),
        'precio_compra' => 18.00,
        'precio_venta' => 30.00,
    ]);
});

test('usuario sin permiso de farmacia no puede acceder a reintegros por institucion', function (): void {
    $usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolOperador->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->actingAs($usuarioSinPermiso);

    $this->get(route('farmacia.reintegros'))
        ->assertStatus(403);
});

test('usuario autorizado puede acceder a la vista de reintegro por institucion', function (): void {
    $this->actingAs($this->admin);

    $this->get(route('farmacia.reintegros'))
        ->assertStatus(200)
        ->assertSee('Reintegro por Institución');
});

test('verifica la cantidad total usada solo de proformas En Curso de la institucion seleccionada', function (): void {
    $this->actingAs($this->admin);

    $pacienteInst = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mamani',
        'cedula' => '6011223',
        'fecha_nacimiento' => '1985-05-10',
        'genero' => 'M',
    ]);

    $pacienteOtraInst = Paciente::create([
        'institucion_id' => $this->otraInstitucion->id,
        'nombres' => 'Ana',
        'apellido_paterno' => 'Flores',
        'cedula' => '7011224',
        'fecha_nacimiento' => '1990-02-15',
        'genero' => 'F',
    ]);

    // Proforma 1: En Curso (debe contarse: 4 unidades)
    $proformaEnCurso = Proforma::create([
        'paciente_id' => $pacienteInst->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internación',
        'fecha_ingreso' => now()->subDays(5),
        'estado' => 'En Curso',
        'costo_total' => 120.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 4,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proformaEnCurso->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    // Proforma 2: Completada / Pagada de la misma institución (NO debe contarse)
    $proformaCerrada = Proforma::create([
        'paciente_id' => $pacienteInst->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now()->subDays(10),
        'estado' => 'Completada',
        'costo_total' => 90.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 3,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proformaCerrada->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    // Proforma 3: En Curso pero de OTRA institución (NO debe contarse)
    $proformaOtra = Proforma::create([
        'paciente_id' => $pacienteOtraInst->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internación',
        'fecha_ingreso' => now()->subDays(2),
        'estado' => 'En Curso',
        'costo_total' => 150.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 5,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proformaOtra->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    $component = Livewire::test(ReintegrosInstitucionIndex::class)
        ->call('abrirModalReintegro')
        ->set('institucion_id', $this->institucion->id);

    $consumos = $component->get('consumos_institucion');

    expect($consumos)->toHaveKey($this->producto->id)
        ->and($consumos[$this->producto->id]['cantidad_total_usada'])->toBe(4)
        ->and($consumos[$this->producto->id]['desglose_proformas'])->toHaveCount(1)
        ->and($consumos[$this->producto->id]['desglose_proformas'][0]['proforma_id'])->toBe($proformaEnCurso->id);
});

test('descuenta medicamentos por orden de inicio de proforma mas antigua y crea nuevo lote disminuyendo el costo de proformas', function (): void {
    $this->actingAs($this->admin);

    $pacienteAntiguo = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Juan',
        'apellido_paterno' => 'Antiguo',
        'cedula' => '4001111',
        'fecha_nacimiento' => '1975-03-10',
        'genero' => 'M',
    ]);

    $pacienteIntermedio = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Pedro',
        'apellido_paterno' => 'Intermedio',
        'cedula' => '4002222',
        'fecha_nacimiento' => '1980-06-20',
        'genero' => 'M',
    ]);

    $pacienteReciente = Paciente::create([
        'institucion_id' => $this->institucion->id,
        'nombres' => 'Luis',
        'apellido_paterno' => 'Reciente',
        'cedula' => '4003333',
        'fecha_nacimiento' => '1992-11-05',
        'genero' => 'M',
    ]);

    // 1. Proforma Más Antigua (usó 4 ampollas por Receta = Bs. 120)
    $proformaAntigua = Proforma::create([
        'paciente_id' => $pacienteAntiguo->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internación',
        'fecha_ingreso' => now()->subDays(10),
        'estado' => 'En Curso',
        'costo_total' => 120.00,
    ]);

    $recetaAntigua = Receta::create([
        'proforma_id' => $proformaAntigua->id,
        'user_id' => $this->admin->id,
        'activo' => true,
    ]);

    $recetaDetAntigua = RecetaDetalle::create([
        'receta_id' => $recetaAntigua->id,
        'producto_id' => $this->producto->id,
        'cantidad' => 4,
        'costo_unitario' => 30.00,
        'despachado' => true,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 4,
        'tipo_movimiento' => 'Salida Receta',
        'receta_id' => $recetaAntigua->id,
        'proforma_id' => $proformaAntigua->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    // 2. Proforma Intermedia (usó 5 ampollas en Consumo Extra = Bs. 150)
    $proformaIntermedia = Proforma::create([
        'paciente_id' => $pacienteIntermedio->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internación',
        'fecha_ingreso' => now()->subDays(5),
        'estado' => 'En Curso',
        'costo_total' => 150.00,
    ]);

    $consumoIntermedio = ConsumoExtra::create([
        'proforma_id' => $proformaIntermedia->id,
        'producto_id' => $this->producto->id,
        'user_id' => $this->admin->id,
        'cantidad' => 5,
        'precio_unitario' => 30.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 5,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proformaIntermedia->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    // 3. Proforma Más Reciente (usó 3 ampollas en Consumo Extra = Bs. 90)
    $proformaReciente = Proforma::create([
        'paciente_id' => $pacienteReciente->id,
        'sucursal_id' => $this->sucursal->id,
        'tipo_atencion' => 'Internación',
        'fecha_ingreso' => now()->subDay(),
        'estado' => 'En Curso',
        'costo_total' => 90.00,
    ]);

    $consumoReciente = ConsumoExtra::create([
        'proforma_id' => $proformaReciente->id,
        'producto_id' => $this->producto->id,
        'user_id' => $this->admin->id,
        'cantidad' => 3,
        'precio_unitario' => 30.00,
    ]);

    MovimientoInventario::create([
        'sucursal_id' => $this->sucursal->id,
        'producto_id' => $this->producto->id,
        'lote_id' => $this->loteOriginal->id,
        'cantidad' => 3,
        'tipo_movimiento' => 'Consumo Extra',
        'proforma_id' => $proformaReciente->id,
        'seccion_origen_id' => $this->seccionFarmacia->id,
        'user_id' => $this->admin->id,
    ]);

    // Total usado entre las 3 proformas = 4 + 5 + 3 = 12 unidades.
    // La institución realiza un reintegro parcial de 6 unidades con un nuevo lote 'LOTE-REINT-2026'.
    Livewire::test(ReintegrosInstitucionIndex::class)
        ->call('abrirModalReintegro')
        ->set('institucion_id', $this->institucion->id)
        ->set('persona_entrega', 'Lic. Mariana Torrez')
        ->set('ci_persona_entrega', '5930211 LP')
        ->set('observaciones', 'Reposición parcial de Ceftriaxona')
        ->call('seleccionarMedicamentoParaReintegro', $this->producto->id)
        ->set('item_cantidad_reintegrar', 6)
        ->set('item_codigo_lote', 'LOTE-REINT-2026')
        ->set('item_fecha_vencimiento', '2028-06-30')
        ->set('item_precio_compra', '19.00')
        ->set('item_precio_venta', '30.00')
        ->call('agregarItemReintegro')
        ->call('guardarReintegro')
        ->assertSet('modalReintegroOpen', false);

    // Verificar creación del Acta de Reintegro
    $reintegro = ReintegroInstitucion::first();
    expect($reintegro)->not->toBeNull()
        ->and($reintegro->institucion_id)->toBe($this->institucion->id)
        ->and($reintegro->persona_entrega)->toBe('Lic. Mariana Torrez')
        ->and($reintegro->total_unidades)->toBe(6)
        ->and((float) $reintegro->monto_total_descontado)->toBe(180.00);

    // Verificar creación del Nuevo Lote en inventario con la cantidad reintegrada
    $nuevoLote = Lote::where('codigo_lote', 'LOTE-REINT-2026')->first();
    expect($nuevoLote)->not->toBeNull()
        ->and($nuevoLote->cantidad_ingresada)->toBe(6)
        ->and($nuevoLote->cantidad_actual)->toBe(6)
        ->and($nuevoLote->fecha_vencimiento->format('Y-m-d'))->toBe('2028-06-30');

    // Verificar que la Proforma Antigua quedó con costo 0 y su RecetaDetalle fue eliminado
    $proformaAntigua->refresh();
    expect((float) $proformaAntigua->costo_total)->toBe(0.00)
        ->and(RecetaDetalle::withTrashed()->find($recetaDetAntigua->id)->trashed())->toBeTrue();

    // Verificar que la Proforma Intermedia disminuyó su ConsumoExtra de 5 a 3 y su costo_total de 150 a 90
    $proformaIntermedia->refresh();
    $consumoIntermedio->refresh();
    expect($consumoIntermedio->cantidad)->toBe(3)
        ->and((float) $proformaIntermedia->costo_total)->toBe(90.00);

    // Verificar que la Proforma Más Reciente NO fue disminuida (mantiene 3 unidades y Bs. 90.00)
    $proformaReciente->refresh();
    $consumoReciente->refresh();
    expect($consumoReciente->cantidad)->toBe(3)
        ->and((float) $proformaReciente->costo_total)->toBe(90.00);

    // Verificar registros en reintegro_institucion_aplicaciones (2 aplicaciones: Proforma Antigua y Proforma Intermedia)
    expect($reintegro->aplicaciones)->toHaveCount(2);
});

test('puede generar el documento pdf del reintegro por institucion con firmas', function (): void {
    $this->actingAs($this->admin);

    $reintegro = ReintegroInstitucion::create([
        'sucursal_id' => $this->sucursal->id,
        'institucion_id' => $this->institucion->id,
        'seccion_destino_id' => $this->seccionFarmacia->id,
        'codigo' => 'RI-2026-0001',
        'fecha_reintegro' => now()->toDateString(),
        'persona_entrega' => 'Dr. Fernando Villarroel',
        'ci_persona_entrega' => '3344556 LP',
        'total_unidades' => 5,
        'monto_total_descontado' => 150.00,
    ]);

    $response = $this->get(route('farmacia.reintegros.pdf', $reintegro->id));

    $response->assertStatus(200);
    expect($response->headers->get('content-type'))->toContain('application/pdf');
});
