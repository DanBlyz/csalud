<?php

use App\Livewire\Caja\CajaIndex;
use App\Livewire\Caja\CierresIndex;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\CierreMensual;
use App\Models\Paciente;
use App\Models\Pago;
use App\Models\Permiso;
use App\Models\Proforma;
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

    // Caja abierta por defecto para el cajero
    $this->caja = Caja::create([
        'sucursal_id' => $this->sucursal->id,
        'user_id' => $this->cajero->id,
        'monto_apertura' => 100.00,
        'fecha_apertura' => now(),
        'estado' => 'Abierta',
    ]);
});

test('un usuario con permiso 10 puede acceder a la bandeja de caja', function () {
    $this->actingAs($this->cajero);

    $this->get(route('caja.index'))
        ->assertOk()
        ->assertSee('Cobro y Gestión de Caja')
        ->assertSee('Cuentas por Cobrar (Pendientes)');
});

test('un usuario sin permiso 10 no puede acceder a caja', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $this->get(route('caja.index'))
        ->assertForbidden();
});

test('bloquea cobros si el cajero no tiene una caja abierta', function () {
    // Cerrar la caja activa
    $this->caja->update(['estado' => 'Cerrada', 'fecha_cierre' => now()]);

    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Ambulatoria',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'costo_total' => 150.00,
    ]);

    Livewire::test(CajaIndex::class)
        ->call('abrirModalCobro', $proforma->id)
        ->assertSet('modalCobroOpen', false)
        ->assertDispatched('swal');
});

test('puede aperturar una caja con fondo inicial y observaciones', function () {
    $this->caja->delete(); // Eliminar caja existente

    $this->actingAs($this->cajero);

    Livewire::test(CajaIndex::class)
        ->call('abrirModalApertura')
        ->assertSet('modalAperturaOpen', true)
        ->set('monto_apertura', '50.00')
        ->set('observaciones_apertura', 'Fondo entregado por supervisión')
        ->call('aperturarCaja')
        ->assertSet('modalAperturaOpen', false);

    $nuevaCaja = Caja::where('user_id', $this->cajero->id)->where('estado', 'Abierta')->first();
    expect($nuevaCaja)->not->toBeNull()
        ->and((float) $nuevaCaja->monto_apertura)->toBe(50.00)
        ->and($nuevaCaja->observaciones_apertura)->toBe('Fondo entregado por supervisión');
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

    expect(Pago::where('proforma_id', $proforma->id)->count())->toBe(1);
    $pago = Pago::where('proforma_id', $proforma->id)->first();
    expect($pago->categoria)->toBe('Proforma')
        ->and($pago->caja_id)->toBe($this->caja->id);
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
        ->and(Pago::where('proforma_id', $proforma->id)->count())->toBe(2);

    $pagoTrans = Pago::where('proforma_id', $proforma->id)->where('tipo_pago', 'Transferencia')->first();
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
        ->and(Pago::where('proforma_id', $proforma->id)->count())->toBe(0);
});

test('puede registrar ingresos extras y egresos de caja con categoria y concepto', function () {
    $this->actingAs($this->cajero);

    // 1. Registrar Ingreso Extra
    Livewire::test(CajaIndex::class)
        ->call('abrirModalMovimiento', 'Ingreso Extra')
        ->assertSet('modalMovimientoOpen', true)
        ->set('mov_categoria', 'Certificados')
        ->set('mov_tipo_pago', 'Efectivo')
        ->set('mov_concepto', 'Emisión Certificado Médico prenupcial')
        ->set('mov_monto', '50.00')
        ->call('guardarMovimiento')
        ->assertSet('modalMovimientoOpen', false);

    $ingresoExtra = Pago::where('caja_id', $this->caja->id)->where('tipo_movimiento', 'Ingreso Extra')->first();
    expect($ingresoExtra)->not->toBeNull()
        ->and($ingresoExtra->categoria)->toBe('Certificados')
        ->and((float) $ingresoExtra->monto)->toBe(50.00)
        ->and($ingresoExtra->proforma_id)->toBeNull();

    // 2. Registrar Salida de Caja (Egreso)
    Livewire::test(CajaIndex::class)
        ->call('abrirModalMovimiento', 'Egreso Caja')
        ->assertSet('modalMovimientoOpen', true)
        ->set('mov_categoria', 'Insumos')
        ->set('mov_tipo_pago', 'Efectivo')
        ->set('mov_concepto', 'Compra de alcohol y bolsas de residuos')
        ->set('mov_monto', '30.00')
        ->call('guardarMovimiento')
        ->assertSet('modalMovimientoOpen', false);

    $egreso = Pago::where('caja_id', $this->caja->id)->where('tipo_movimiento', 'Egreso Caja')->first();
    expect($egreso)->not->toBeNull()
        ->and($egreso->categoria)->toBe('Insumos')
        ->and((float) $egreso->monto)->toBe(30.00);

    // Saldo esperado en efectivo: 100 (apertura) + 50 (extra) - 30 (salida) = 120
    expect($this->caja->saldoEsperadoEfectivo())->toBe(120.00);
});

test('valida saldo suficiente al registrar egresos de efectivo de caja', function () {
    $this->actingAs($this->cajero);

    // Saldo inicial es 100. Intentar sacar 500 debe fallar por saldo insuficiente
    Livewire::test(CajaIndex::class)
        ->call('abrirModalMovimiento', 'Egreso Caja')
        ->set('mov_categoria', 'Gasto Operativo')
        ->set('mov_tipo_pago', 'Efectivo')
        ->set('mov_concepto', 'Reparación de aire acondicionado')
        ->set('mov_monto', '500.00')
        ->call('guardarMovimiento')
        ->assertDispatched('swal');

    expect(Pago::where('caja_id', $this->caja->id)->where('tipo_movimiento', 'Egreso Caja')->count())->toBe(0);
});

test('puede realizar el cierre de caja y arqueo calculando diferencias y emitiendo el acta pdf', function () {
    $this->actingAs($this->cajero);

    // Generar un ingreso en efectivo de 50 y un ingreso QR de 80
    Pago::create([
        'caja_id' => $this->caja->id,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Extra',
        'tipo_pago' => 'Efectivo',
        'concepto' => 'Cobro directo',
        'monto' => 50.00,
        'user_id' => $this->cajero->id,
    ]);

    Pago::create([
        'caja_id' => $this->caja->id,
        'tipo_movimiento' => 'Ingreso Extra',
        'categoria' => 'Extra',
        'tipo_pago' => 'QR',
        'concepto' => 'Cobro QR',
        'monto' => 80.00,
        'numero_referencia' => 'QR-12345',
        'user_id' => $this->cajero->id,
    ]);

    // Saldo esperado en efectivo: 100 (apertura) + 50 = 150
    // Realizamos el cierre declarando exactamente 150 en efectivo y 80 en QR
    Livewire::test(CajaIndex::class)
        ->call('abrirModalCierre')
        ->assertSet('modalCierreOpen', true)
        ->set('cierre_efectivo', '150.00')
        ->set('cierre_qr', '80.00')
        ->set('cierre_transferencia', '0.00')
        ->set('cierre_observaciones', 'Cierre de turno normal sin incidencias')
        ->call('ejecutarCierreCaja')
        ->assertSet('modalCierreOpen', false);

    $this->caja->refresh();
    expect($this->caja->estado)->toBe('Cerrada')
        ->and($this->caja->fecha_cierre)->not->toBeNull()
        ->and((float) $this->caja->monto_cierre_efectivo)->toBe(150.00)
        ->and((float) $this->caja->monto_cierre_qr)->toBe(80.00)
        ->and((float) $this->caja->diferencia_efectivo)->toBe(0.00);

    // Comprobar la generación del Acta de Arqueo en PDF
    $response = $this->get(route('caja.pdf.arqueo', $this->caja->id));
    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
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

    Pago::create([
        'caja_id' => $this->caja->id,
        'proforma_id' => $proforma->id,
        'tipo_movimiento' => 'Ingreso Proforma',
        'categoria' => 'Proforma',
        'concepto' => "Cobro Proforma #{$proforma->id}",
        'tipo_pago' => 'Efectivo',
        'monto' => 100.00,
        'user_id' => $this->cajero->id,
    ]);

    $response = $this->get(route('proformas.pdf.recibo', $proforma->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
});

test('puede generar y descargar el pdf del resumen de proforma con costos finales de farmacia y extras', function () {
    $this->actingAs($this->cajero);

    $proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'tipo_atencion' => 'Hospitalaria',
        'pieza' => 'Sala 102',
        'fecha_ingreso' => now(),
        'estado' => 'Confirmada',
        'diagnostico' => 'Apendicitis aguda',
        'costo_total' => 250.00,
    ]);

    ProformaServicio::create([
        'proforma_id' => $proforma->id,
        'servicio_id' => $this->servicio->id,
        'costo_final' => 150.00,
        'observaciones' => 'Atención quirúrgica',
    ]);

    $response = $this->get(route('proformas.pdf.resumen', $proforma->id));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');

    $proforma->load('servicios.servicio.categoria', 'movimientosInventario', 'consumosExtras');
    $html = view('pdf.proforma-resumen', [
        'proforma' => $proforma,
        'fechaEmision' => now()->format('d/m/Y H:i'),
    ])->render();

    expect($html)->toContain('RESUMEN DE CUENTA CLÍNICO')
        ->and($html)->toContain('1. Información del Paciente y Admisión')
        ->and($html)->toContain('2. Servicios Médicos y Procedimientos Realizados')
        ->and($html)->toContain('3. Medicamentos e Insumos Despachados por Farmacia')
        ->and($html)->toContain('TOTAL CUENTA:');
});

test('usuario con permiso puede acceder a la seccion de cierres mensuales', function () {
    $this->actingAs($this->cajero);

    $response = $this->get(route('caja.cierres'));

    $response->assertOk()
        ->assertSeeLivewire(CierresIndex::class);
});

test('usuario sin permiso no puede acceder a la seccion de cierres mensuales', function () {
    $this->actingAs($this->usuarioSinPermiso);

    $response = $this->get(route('caja.cierres'));

    $response->assertForbidden();
});

test('puede aperturar un cierre mensual, registrar egresos y finalizar el periodo', function () {
    $this->actingAs($this->cajero);

    $component = Livewire::test(CierresIndex::class)
        ->call('abrirModalCrear')
        ->set('nuevo_anio', 2026)
        ->set('nuevo_mes', 9)
        ->set('nuevo_fecha_inicio', '2026-09-01')
        ->set('nuevo_fecha_fin', '2026-09-30')
        ->set('nuevo_observaciones', 'Cierre de prueba Septiembre 2026')
        ->call('crearCierre');

    $cierre = CierreMensual::where('anio', 2026)->where('mes', 9)->first();
    expect($cierre)->not->toBeNull()
        ->and($cierre->estado)->toBe('Borrador');

    // Agregar partida manual de egreso operativo (ej. Luz y Agua)
    $component->call('abrirModalPartida', 'Egreso')
        ->set('partida_categoria', 'Servicio Básico')
        ->set('partida_concepto', 'Pago Servicio de Electricidad y Agua')
        ->set('partida_monto', '450.50')
        ->set('partida_fecha', '2026-09-15')
        ->call('guardarPartida');

    $cierre->refresh();
    expect((float) $cierre->total_egresos)->toBe(450.50)
        ->and((float) $cierre->utilidad_neta)->toBe(-450.50);

    // Agregar partida manual de ingreso extra
    $component->call('abrirModalPartida', 'Ingreso')
        ->set('partida_categoria', 'Cobro Proforma')
        ->set('partida_concepto', 'Ingreso por atención especializada')
        ->set('partida_monto', '1000.00')
        ->set('partida_fecha', '2026-09-20')
        ->call('guardarPartida');

    $cierre->refresh();
    expect((float) $cierre->total_ingresos)->toBe(1000.00)
        ->and((float) $cierre->total_egresos)->toBe(450.50)
        ->and((float) $cierre->utilidad_neta)->toBe(549.50);

    // Cerrar el período
    $component->call('cambiarEstadoCierre', 'Cerrado');
    $cierre->refresh();
    expect($cierre->estado)->toBe('Cerrado');

    // Reabrir el período
    $component->call('cambiarEstadoCierre', 'Borrador');
    $cierre->refresh();
    expect($cierre->estado)->toBe('Borrador');
});
