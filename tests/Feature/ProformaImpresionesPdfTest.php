<?php

use App\Livewire\Proformas\ProformaDetalle;
use App\Models\HistoriaClinica;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->rolAdmin = Rol::firstOrCreate(['nombre' => 'Admin'], ['descripcion' => 'Admin']);

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sucursal Central Test',
        'direccion' => 'Av. Principal #123',
        'telefono' => '70012345',
        'activo' => true,
    ]);

    $this->admin = User::factory()->create([
        'rol_id' => $this->rolAdmin->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->institucion = Institucion::create([
        'nombre' => 'Seguro Salud Integral',
        'tipo' => 'Seguro',
        'activo' => true,
    ]);

    $this->paciente = Paciente::create([
        'nombres' => 'Carlos Alberto',
        'apellido_paterno' => 'Mamani',
        'apellido_materno' => 'Quispe',
        'fecha_nacimiento' => '1985-05-15',
        'cedula' => 'CI-TEST-7788',
        'genero' => 'Masculino',
        'celular' => '71234567',
        'contacto_emergencia_nombre' => 'María Mamani Quispe',
        'contacto_emergencia_telefono' => '79998877',
        'antecedentes_alergias' => 'Penicilina, Sulfas',
        'institucion_id' => $this->institucion->id,
    ]);

    $this->proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Piso 3 - Cama 12',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
        'motivo_consulta' => 'Dificultad respiratoria aguda',
        'diagnostico' => 'Neumonía adquirida en la comunidad',
    ]);

    $this->historiaClinica = HistoriaClinica::create([
        'proforma_id' => $this->proforma->id,
        'peso' => '68.5',
        'talla' => '172',
        'pa' => '120/80',
        'fc' => '78',
        'fr' => '18',
        'temperatura' => '36.7',
        'sato2' => '97',
        'motivo_consulta' => 'Dificultad respiratoria aguda',
        'diagnostico' => 'Neumonía adquirida en la comunidad',
    ]);
});

test('la ruta de impresion de alta solicitada genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.alta_solicitada', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de registro de enfermeria en emergencia genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.registro_enfermeria', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de control de signos vitales genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.signos_vitales', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de kardex de enfermeria genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.kardex_enfermeria', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de control de liquidos genera un pdf valido apaisado', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.control_liquidos', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de consentimiento de anestesia raquidea genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.consentimiento_anestesia', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de consentimiento de procedimiento quirurgico genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.consentimiento_quirurgico', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de tarjeta unica de tratamiento genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.tarjeta_tratamiento', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de registro de tratamiento de enfermeria genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.tratamiento_enfermeria', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de grafica de signos vitales genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.grafica_signos_vitales', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de registro de glucemias genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.registro_glucemias', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('la ruta de impresion de descargo de medicamentos e insumos genera un pdf valido', function () {
    $this->actingAs($this->admin);

    $response = $this->get(route('proformas.pdf.descargo_medicamentos_insumos', $this->proforma->id));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('cualquier usuario puede acceder a la pestaña de impresiones sin requerir permisos especiales', function () {
    $rolRecepcion = Rol::firstOrCreate(['nombre' => 'Recepcion'], ['descripcion' => 'Recepcion']);
    $usuarioComun = User::factory()->create([
        'rol_id' => $rolRecepcion->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    $this->actingAs($usuarioComun);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('cambiarTab', 'impresiones')
        ->assertSet('tab', 'impresiones')
        ->assertSee('Centro de Impresiones y Documentación Clínica')
        ->assertSee('Alta Solicitada (Alta Voluntaria)')
        ->assertSee('Registro de Enfermería en Emergencia')
        ->assertSee('Control de Signos Vitales')
        ->assertSee('Kardex de Enfermería')
        ->assertSee('Hoja de Control de Líquidos')
        ->assertSee('Consentimiento Informado - Anestesia Raquídea')
        ->assertSee('Consentimiento y Aceptación de Procedimiento Quirúrgico')
        ->assertSee('Tarjeta Única de Tratamiento')
        ->assertSee('Registro Tratamiento de Enfermería')
        ->assertSee('Gráfica de Signos Vitales')
        ->assertSee('Registro de Glucemias')
        ->assertSee('Descargo de Insumos y Medicamentos (Piso)');
});
