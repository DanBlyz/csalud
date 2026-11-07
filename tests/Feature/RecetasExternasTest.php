<?php

use App\Livewire\Proformas\ProformaDetalle;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\RecetaExterna;
use App\Models\RecetaExternaDetalle;
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
        'nombres' => 'Juan Carlos',
        'apellido_paterno' => 'Pérez',
        'apellido_materno' => 'Gómez',
        'fecha_nacimiento' => '1990-08-10',
        'cedula' => 'CI-EXT-12345',
        'genero' => 'Masculino',
        'celular' => '71122334',
        'institucion_id' => $this->institucion->id,
    ]);

    $this->proforma = Proforma::create([
        'sucursal_id' => $this->sucursal->id,
        'paciente_id' => $this->paciente->id,
        'fecha_ingreso' => now(),
        'tipo_atencion' => 'Internacion',
        'pieza' => 'Sala 2 - Cama 4',
        'estado' => 'En Curso',
        'costo_total' => 0.00,
        'motivo_consulta' => 'Infección severa',
        'diagnostico' => 'Sepsis de foco abdominal',
    ]);
});

test('un usuario administrador puede visualizar la pestaña de recetas externas', function () {
    $this->actingAs($this->admin);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('cambiarTab', 'recetas_externas')
        ->assertSet('tab', 'recetas_externas')
        ->assertSee('Recetas Médicas Externas')
        ->assertSee('Compra Externa por el Paciente');
});

test('se puede emitir una receta externa con medicamentos ingresados libremente sin producto de inventario', function () {
    $this->actingAs($this->admin);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('abrirModalRecetaExterna')
        ->assertSet('modalRecetaExternaOpen', true)
        ->set('receta_externa_medico_id', $this->admin->id)
        ->set('receta_externa_diagnostico', 'Infección multirresistente')
        ->set('receta_externa_observaciones', 'Comprar en farmacia especializada')
        // Agregar primer medicamento
        ->set('receta_externa_nuevo_medicamento', 'Meropenem 1g ampolla')
        ->set('receta_externa_nuevo_cantidad', '10 ampollas')
        ->set('receta_externa_nuevo_indicaciones', '1g EV cada 8 horas')
        ->call('agregarMedicamentoARecetaExterna')
        // Agregar segundo medicamento
        ->set('receta_externa_nuevo_medicamento', 'Solución Fisiológica 0.9% 100ml')
        ->set('receta_externa_nuevo_cantidad', '10 frascos')
        ->set('receta_externa_nuevo_indicaciones', 'Dilución para antibiótico')
        ->call('agregarMedicamentoARecetaExterna')
        ->assertCount('receta_externa_medicamentos', 2)
        // Guardar la receta externa
        ->call('guardarRecetaExterna')
        ->assertSet('modalRecetaExternaOpen', false)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('recetas_externas', [
        'proforma_id' => $this->proforma->id,
        'user_id' => $this->admin->id,
        'diagnostico' => 'Infección multirresistente',
        'observaciones' => 'Comprar en farmacia especializada',
    ]);

    $recetaExterna = RecetaExterna::where('proforma_id', $this->proforma->id)->first();
    expect($recetaExterna)->not->toBeNull();

    $this->assertDatabaseHas('receta_externa_detalles', [
        'receta_externa_id' => $recetaExterna->id,
        'medicamento' => 'Meropenem 1g ampolla',
        'cantidad' => '10 ampollas',
        'indicaciones' => '1g EV cada 8 horas',
    ]);

    $this->assertDatabaseHas('receta_externa_detalles', [
        'receta_externa_id' => $recetaExterna->id,
        'medicamento' => 'Solución Fisiológica 0.9% 100ml',
        'cantidad' => '10 frascos',
        'indicaciones' => 'Dilución para antibiótico',
    ]);
});

test('se puede generar el pdf oficial de la receta externa', function () {
    $this->actingAs($this->admin);

    $recetaExterna = RecetaExterna::create([
        'proforma_id' => $this->proforma->id,
        'user_id' => $this->admin->id,
        'fecha' => now(),
        'diagnostico' => 'Infección dérmica',
        'observaciones' => 'Adquirir en droguería externa',
        'activo' => true,
    ]);

    RecetaExternaDetalle::create([
        'receta_externa_id' => $recetaExterna->id,
        'medicamento' => 'Mupirocina ungüento 2%',
        'cantidad' => '1 tubo',
        'indicaciones' => 'Aplicar cada 12 horas en herida',
    ]);

    $response = $this->get(route('proformas.pdf.receta_externa', [$this->proforma->id, $recetaExterna->id]));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/pdf');
});

test('se puede eliminar una receta externa del expediente', function () {
    $this->actingAs($this->admin);

    $recetaExterna = RecetaExterna::create([
        'proforma_id' => $this->proforma->id,
        'user_id' => $this->admin->id,
        'fecha' => now(),
        'diagnostico' => 'Diagnóstico prueba',
        'activo' => true,
    ]);

    RecetaExternaDetalle::create([
        'receta_externa_id' => $recetaExterna->id,
        'medicamento' => 'Paracetamol 1g',
        'cantidad' => '5 comprimidos',
        'indicaciones' => '1 cada 8 horas',
    ]);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('eliminarRecetaExterna', $recetaExterna->id)
        ->assertDispatched('swal');

    $this->assertSoftDeleted('recetas_externas', ['id' => $recetaExterna->id]);
    $this->assertSoftDeleted('receta_externa_detalles', ['receta_externa_id' => $recetaExterna->id]);
});

test('no se puede emitir una receta externa si la proforma esta pagada', function () {
    $this->proforma->update(['estado' => 'Pagada']);

    $this->actingAs($this->admin);

    Livewire::test(ProformaDetalle::class, ['proforma' => $this->proforma])
        ->call('abrirModalRecetaExterna')
        ->assertSet('modalRecetaExternaOpen', false)
        ->assertDispatched('swal');
});
