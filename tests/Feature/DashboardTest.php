<?php

use App\Livewire\DashboardIndex;
use App\Models\Lote;
use App\Models\LoteSeccion;
use App\Models\Marca;
use App\Models\Paciente;
use App\Models\Producto;
use App\Models\Proforma;
use App\Models\Rol;
use App\Models\Seccion;
use App\Models\Sucursal;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Livewire;

test('unauthenticated users are redirected from dashboard to login', function () {
    $this->get('/dashboard')->assertRedirect('/login');
});

test('authenticated user can view dashboard with livewire component', function () {
    $rol = Rol::factory()->create(['nombre' => 'Admin']);
    $user = User::factory()->create(['rol_id' => $rol->id]);

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertSeeLivewire(DashboardIndex::class);
});

test('dashboard displays accurate patient metrics for total, interned, ambulatory, and discharges', function () {
    $rol = Rol::factory()->create(['nombre' => 'Admin']);
    $sucursal = Sucursal::factory()->create();
    $user = User::factory()->create([
        'rol_id' => $rol->id,
        'sucursal_id' => $sucursal->id,
    ]);

    // Crear pacientes en el sistema
    $paciente1 = Paciente::factory()->create();
    $paciente2 = Paciente::factory()->create();
    $paciente3 = Paciente::factory()->create();
    $paciente4 = Paciente::factory()->create();

    $hoy = Carbon::today()->setTime(10, 30);
    $ayer = Carbon::yesterday()->setTime(14, 0);

    // 1. Paciente Internado hoy
    Proforma::create([
        'sucursal_id' => $sucursal->id,
        'paciente_id' => $paciente1->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => $hoy,
        'estado' => 'En Curso',
        'costo_total' => 500,
    ]);

    // 2. Paciente Atendido Ambulatorio hoy
    Proforma::create([
        'sucursal_id' => $sucursal->id,
        'paciente_id' => $paciente2->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => $hoy,
        'estado' => 'En Curso',
        'costo_total' => 100,
    ]);

    // 3. Paciente Dado de Alta hoy (ingresó ayer, sale hoy)
    Proforma::create([
        'sucursal_id' => $sucursal->id,
        'paciente_id' => $paciente3->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => $ayer,
        'fecha_salida' => $hoy,
        'estado' => 'Pagada',
        'costo_total' => 1200,
    ]);

    // 4. Proforma Anulada de hoy (no debe contabilizarse en internados ni ambulatorios)
    Proforma::create([
        'sucursal_id' => $sucursal->id,
        'paciente_id' => $paciente4->id,
        'tipo_atencion' => 'Internacion',
        'fecha_ingreso' => $hoy,
        'estado' => 'Anulada',
        'costo_total' => 0,
    ]);

    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->assertSet('fecha', today()->format('Y-m-d'))
        ->assertViewHas('totalPacientes', 4)
        ->assertViewHas('pacientesInternadosHoy', 1)
        ->assertViewHas('pacientesAmbulatoriosHoy', 1)
        ->assertViewHas('pacientesAltasHoy', 1);
});

test('dashboard displays medicines running out of stock filtered by section', function () {
    $rol = Rol::factory()->create(['nombre' => 'Admin']);
    $sucursal = Sucursal::factory()->create();
    $user = User::factory()->create([
        'rol_id' => $rol->id,
        'sucursal_id' => $sucursal->id,
    ]);

    $seccionPrincipal = Seccion::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Farmacia Central',
        'es_almacen_principal' => true,
        'activo' => true,
    ]);

    $seccionQuirofano = Seccion::create([
        'sucursal_id' => $sucursal->id,
        'nombre' => 'Quirófano',
        'es_almacen_principal' => false,
        'activo' => true,
    ]);

    $marca = Marca::create(['nombre' => 'Laboratorio Bagó']);

    // Producto 1: Stock bajo en Farmacia Central (3 unidades)
    $prodBajo = Producto::create([
        'marca_id' => $marca->id,
        'nombre' => 'Ibuprofeno 400mg',
        'unidad_medida' => 'Comprimidos',
        'stock_minimo' => 15,
    ]);

    $loteBajo = Lote::create([
        'sucursal_id' => $sucursal->id,
        'producto_id' => $prodBajo->id,
        'codigo_lote' => 'IBU-001',
        'cantidad_ingresada' => 3,
        'cantidad_actual' => 3,
        'fecha_vencimiento' => now()->addMonths(6),
        'precio_compra' => 5,
        'precio_venta' => 10,
    ]);

    // Producto 2: Stock crítico en Quirófano (2 unidades)
    $prodCritico = Producto::create([
        'marca_id' => $marca->id,
        'nombre' => 'Propofol 20ml',
        'unidad_medida' => 'Ampolla',
        'stock_minimo' => 5,
    ]);

    $loteCritico = Lote::create([
        'sucursal_id' => $sucursal->id,
        'producto_id' => $prodCritico->id,
        'codigo_lote' => 'PROP-999',
        'cantidad_ingresada' => 100,
        'cantidad_actual' => 100,
        'fecha_vencimiento' => now()->addMonths(12),
        'precio_compra' => 50,
        'precio_venta' => 80,
    ]);

    // Asignar en Quirófano stock bajo de 2 unidades
    LoteSeccion::updateOrCreate(
        ['lote_id' => $loteCritico->id, 'seccion_id' => $seccionQuirofano->id],
        ['cantidad_actual' => 2]
    );

    // Producto 3: Stock alto (50 unidades) - no debe figurar como por agotarse
    $prodAlto = Producto::create([
        'marca_id' => $marca->id,
        'nombre' => 'Paracetamol 500mg',
        'unidad_medida' => 'Comprimidos',
        'stock_minimo' => 10,
    ]);

    Lote::create([
        'sucursal_id' => $sucursal->id,
        'producto_id' => $prodAlto->id,
        'codigo_lote' => 'PARA-500',
        'cantidad_ingresada' => 50,
        'cantidad_actual' => 50,
        'fecha_vencimiento' => now()->addYear(),
        'precio_compra' => 2,
        'precio_venta' => 4,
    ]);

    // Test en "Todas las Secciones"
    Livewire::actingAs($user)
        ->test(DashboardIndex::class)
        ->assertSee('Ibuprofeno 400mg')
        ->assertSee('Propofol 20ml')
        ->assertDontSee('Paracetamol 500mg')
        // Filtrar por sección Quirófano
        ->call('seleccionarSeccion', $seccionQuirofano->id)
        ->assertSee('Propofol 20ml')
        ->assertDontSee('Ibuprofeno 400mg')
        // Filtrar por sección Farmacia Central
        ->call('seleccionarSeccion', $seccionPrincipal->id)
        ->assertSee('Ibuprofeno 400mg')
        ->assertDontSee('Propofol 20ml');
});
