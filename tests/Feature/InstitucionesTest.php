<?php

use App\Livewire\Pacientes\InstitucionesIndex;
use App\Livewire\Pacientes\PacientesIndex;
use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Permiso;
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

    // Permiso 5: Pacientes y Proformas
    DB::table('permisos')->insertOrIgnore([
        'id' => 5,
        'nombre' => 'pacientes-proformas',
        'descripcion' => 'Gestión de Pacientes y Expedientes',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->sucursal = Sucursal::create([
        'nombre' => 'Sede Central',
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
});

test('usuario autenticado puede ver la pantalla de instituciones', function () {
    $this->actingAs($this->admin)
        ->get(route('pacientes.instituciones'))
        ->assertOk()
        ->assertSee('Instituciones y Convenios');
});

test('se listan instituciones existentes y métricas en instituciones-index', function () {
    $inst1 = Institucion::factory()->create(['nombre' => 'BISA Seguros', 'estado' => 'Activo']);
    $inst2 = Institucion::factory()->create(['nombre' => 'Alianza Seguros', 'estado' => 'Inactivo']);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->assertSee('BISA Seguros')
        ->assertSee('Alianza Seguros')
        ->assertSee('Total Instituciones');
});

test('filtro de búsqueda y filtro de estado funcionan en instituciones-index', function () {
    $inst1 = Institucion::factory()->create(['nombre' => 'Caja Nacional de Salud', 'estado' => 'Activo']);
    $inst2 = Institucion::factory()->create(['nombre' => 'Caja Petrolera', 'estado' => 'Inactivo']);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->set('search', 'Petrolera')
        ->assertSee('Caja Petrolera')
        ->assertDontSee('Caja Nacional de Salud')
        ->set('search', '')
        ->set('filtroEstado', 'Inactivo')
        ->assertSee('Caja Petrolera')
        ->assertDontSee('Caja Nacional de Salud');
});

test('se puede registrar una nueva institucion', function () {
    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('abrirModal')
        ->assertSet('modalOpen', true)
        ->set('nombre', 'Seguros Illimani S.A.')
        ->set('descripcion', 'Convenio corporativo')
        ->set('estado', 'Activo')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('modalOpen', false)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('instituciones', [
        'nombre' => 'Seguros Illimani S.A.',
        'estado' => 'Activo',
    ]);
});

test('se valida que el nombre sea obligatorio y unico al crear institucion', function () {
    Institucion::factory()->create(['nombre' => 'Cruz Roja']);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('abrirModal')
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre' => 'required'])
        ->set('nombre', 'Cruz Roja')
        ->call('guardar')
        ->assertHasErrors(['nombre' => 'unique']);
});

test('se puede editar una institucion existente', function () {
    $inst = Institucion::factory()->create(['nombre' => 'Nombre Viejo']);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('abrirModal', $inst->id)
        ->assertSet('institucionId', $inst->id)
        ->assertSet('nombre', 'Nombre Viejo')
        ->set('nombre', 'Nombre Modificado')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('swal');

    expect($inst->fresh()->nombre)->toBe('Nombre Modificado');
});

test('se puede alternar el estado de una institucion', function () {
    $inst = Institucion::factory()->create(['estado' => 'Activo']);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('toggleEstado', $inst->id);

    expect($inst->fresh()->estado)->toBe('Inactivo');
});

test('se puede eliminar una institucion si no tiene pacientes vinculados', function () {
    $inst = Institucion::factory()->create();

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('eliminarInstitucion', $inst->id)
        ->assertDispatched('swal');

    $this->assertSoftDeleted('instituciones', ['id' => $inst->id]);
});

test('se bloquea la eliminacion si la institucion tiene pacientes vinculados', function () {
    $inst = Institucion::factory()->create();
    Paciente::factory()->create([
        'institucion_id' => $inst->id,
    ]);

    Livewire::actingAs($this->admin)
        ->test(InstitucionesIndex::class)
        ->call('eliminarInstitucion', $inst->id)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('instituciones', [
        'id' => $inst->id,
        'deleted_at' => null,
    ]);
});

test('se puede asignar institucion a un paciente desde pacientes-index', function () {
    $inst = Institucion::factory()->create(['nombre' => 'Seguro Salud Total']);

    Livewire::actingAs($this->admin)
        ->test(PacientesIndex::class)
        ->call('abrirModalCrear')
        ->set('nombres', 'Carlos')
        ->set('apellido_paterno', 'Mendoza')
        ->set('cedula', '9988776-LP')
        ->set('genero', 'Masculino')
        ->set('institucion_id', $inst->id)
        ->call('guardar')
        ->assertHasNoErrors();

    $this->assertDatabaseHas('pacientes', [
        'cedula' => '9988776-LP',
        'institucion_id' => $inst->id,
    ]);
});
