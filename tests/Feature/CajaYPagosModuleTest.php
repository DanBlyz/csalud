<?php

use App\Livewire\Caja\CajaIndex;
use App\Models\Categoria;
use App\Models\Paciente;
use App\Models\Permiso;
use App\Models\Proforma;
use App\Models\ProformaPago;
use App\Models\ProformaServicio;
use App\Models\Rol;
use App\Models\Servicio;
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

    $this->rolCajero = Rol::firstOrCreate(
        ['nombre' => 'Cajero'],
        ['descripcion' => 'Encargado de Caja y Cobros']
    );

    DB::table('permisos')->insertOrIgnore([
        'id' => 10,
        'nombre' => 'cobro-caja',
        'descripcion' => 'Cobro y liquidación en caja',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $this->permisoCaja = Permiso::find(10);

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

    $this->cajero = User::factory()->create([
        'rol_id' => $this->rolCajero->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);
    $this->cajero->permisos()->sync([$this->permisoCaja->id]);

    $this->usuarioSinPermiso = User::factory()->create([
        'rol_id' => $this->rolCajero->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->paciente = Paciente::create([
        'nombres' => 'Carlos',
        'apellido_paterno' => 'Mendoza',
        'apellido_materno' => 'Ramos',
        'cedula' => '6543210',
        'fecha_nacimiento' => '1990-05-15',
        'celular' => '71122334',
        'activo' => true,
    ]);

    $this->categoria = Categoria::create([
        'nombre' => 'Consultas Médicas',
        'descripcion' => 'Atenciones médicas',
        'estado' => true,
    ]);

    $this->servicio = Servicio::create([
        'categoria_id' => $this->categoria->id,
        'nombre' => 'Consulta Médica General',
        'descripcion' => 'Evaluación clínica inicial',
        'precio_tentativo' => 100.00,
        'estado' => true,
    ]);
});

test('un usuario con permiso 10 puede acceder a la bandeja de caja', function () {
    $this->actingAs($this->cajero);

    $this->get(route('caja.index'))
        ->assertOk()
        ->assertSee('Cobro y Liquidación de Pagos')
        ->assertSee('Cuentas por Cobrar (Pendientes)');
});

test('un usuario sin permiso 10 no puede acceder a caja', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $this->get(route('caja.index'))
        ->assertForbidden();
});

test('puede liquidar una proforma con un solo metodo de pago (Efectivo) cambiando su estado a Pagada', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'costo_total' => 150.00,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicio->id,
        'costo_final' => 150.00,
    ]);

    $proforma->recalcularTotal();
    expect($proforma->saldoPendiente())->toBe(150.0);

    Livewire::test(CajaIndex::class)
        ->call('abrirModalCobro', $proforma->id)
        ->assertSet('modalCobroOpen', true)
        ->assertSet('lineasPago.0.tipo_pago', 'Efectivo')
        ->assertSet('lineasPago.0.monto', '150.00')
        ->call('procesarCobro')
        ->assertSet('modalCobroOpen', false);

    $proforma->refresh();
    expect($proforma->estado)->toBe('Pagada')
        ->and($proforma->fecha_salida)->not->toBeNull()
        ->and($proforma->totalPagado())->toBe(150.0)
        ->and($proforma->saldoPendiente())->toBe(0.0);

    expect(ProformaPago::where('proforma_id', $proforma->id)->count())->toBe(1);
});

test('puede liquidar una proforma con pagos divididos (Efectivo + Transferencia) requiriendo numero de referencia para Transferencia', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'costo_total' => 200.00,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicio->id,
        'costo_final' => 200.00,
    ]);

    $proforma->recalcularTotal();

    // 1. Validar que si Transferencia no tiene referencia, no proceda
    Livewire::test(CajaIndex::class)
        ->call('abrirModalCobro', $proforma->id)
        ->set('lineasPago.0.tipo_pago', 'Efectivo')
        ->set('lineasPago.0.monto', '80.00')
        ->call('agregarLineaPago')
        ->set('lineasPago.1.tipo_pago', 'Transferencia')
        ->set('lineasPago.1.monto', '120.00')
        ->set('lineasPago.1.numero_referencia', '')
        ->call('procesarCobro')
        ->assertHasErrors(['lineasPago.1.numero_referencia'])
        ->assertSet('modalCobroOpen', true);

    // 2. Con referencia válida para Transferencia, procesar correctamente
    Livewire::test(CajaIndex::class)
        ->call('abrirModalCobro', $proforma->id)
        ->set('lineasPago.0.tipo_pago', 'Efectivo')
        ->set('lineasPago.0.monto', '80.00')
        ->call('agregarLineaPago')
        ->set('lineasPago.1.tipo_pago', 'Transferencia')
        ->set('lineasPago.1.monto', '120.00')
        ->set('lineasPago.1.numero_referencia', 'BCP-TRANS-987654')
        ->call('procesarCobro')
        ->assertSet('modalCobroOpen', false);

    $proforma->refresh();
    expect($proforma->estado)->toBe('Pagada')
        ->and($proforma->totalPagado())->toBe(200.0)
        ->and(ProformaPago::where('proforma_id', $proforma->id)->count())->toBe(2);

    $pagoTrans = ProformaPago::where('proforma_id', $proforma->id)->where('tipo_pago', 'Transferencia')->first();
    expect($pagoTrans->numero_referencia)->toBe('BCP-TRANS-987654');
});

test('valida que la suma de pagos no supere el saldo pendiente', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'costo_total' => 100.00,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicio->id,
        'costo_final' => 100.00,
    ]);

    $proforma->recalcularTotal();

    Livewire::test(CajaIndex::class)
        ->call('abrirModalCobro', $proforma->id)
        ->set('lineasPago.0.tipo_pago', 'Efectivo')
        ->set('lineasPago.0.monto', '250.00') // Supera saldo de 100
        ->call('procesarCobro')
        ->assertDispatched('swal');

    $proforma->refresh();
    expect($proforma->estado)->toBe('Confirmada')
        ->and(ProformaPago::where('proforma_id', $proforma->id)->count())->toBe(0);
});

test('puede generar y descargar el pdf de detalle de proforma con los servicios correctos', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'diagnostico' => 'Fiebre tifoidea',
        'costo_total' => 100.00,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicio->id,
        'costo_final' => 100.00,
        'observaciones' => 'Chequeo general preventivo',
    ]);

    $response = $this->get(route('proformas.pdf.detalle', $proforma->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');

    // Validar el contenido HTML de la plantilla del PDF
    $proforma->load('servicios.servicio.categoria');
    $html = view('pdf.proforma-detalle', [
        'proforma' => $proforma,
        'fechaEmision' => now()->format('d/m/Y H:i'),
    ])->render();

    expect($html)->toContain('Consulta Médica General')
        ->and($html)->toContain('Consultas Médicas')
        ->and($html)->toContain('Chequeo general preventivo')
        ->and($html)->not->toContain('Honorario Base')
        ->and($html)->not->toContain('Honorarios Médico');
});

test('puede generar y descargar el pdf del recibo de caja', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Pagada',
        'costo_total' => 100.00,
        'fecha_salida' => now(),
    ]);

    ProformaPago::create([
        'proforma_id' => $proforma->id,
        'tipo_pago' => 'Efectivo',
        'monto' => 100.00,
        'user_id' => $this->cajero->id,
    ]);

    $response = $this->get(route('proformas.pdf.recibo', $proforma->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});
