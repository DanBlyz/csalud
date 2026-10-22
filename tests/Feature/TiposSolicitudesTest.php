<?php

use App\Livewire\Administracion\TiposSolicitudesIndex;
use App\Models\Permiso;
use App\Models\Proforma;
use App\Models\ProformaSolicitud;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\TipoSolicitud;
use App\Models\User;
use Database\Seeders\PermisoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermisoSeeder::class);

    $this->rolAdmin = Rol::firstOrCreate(
        ['nombre' => 'Admin'],
        ['descripcion' => 'Administrador Total']
    );

    $this->rolRecepcion = Rol::firstOrCreate(
        ['nombre' => 'Recepción'],
        ['descripcion' => 'Personal de Recepción']
    );

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

test('usuario invitado no puede acceder a la ruta de tipos de solicitudes', function () {
    $this->get(route('administracion.tipos-solicitudes'))
        ->assertRedirect(route('login'));
});

test('administrador puede visualizar la pantalla y listado de tipos de solicitudes', function () {
    TipoSolicitud::create([
        'nombre' => 'Ecografía Tiroidea',
        'descripcion' => 'Estudio ecográfico de glándula tiroides',
    ]);

    $this->actingAs($this->admin)
        ->get(route('administracion.tipos-solicitudes'))
        ->assertOk()
        ->assertSee('Tipos de Solicitud y Exámenes')
        ->assertSee('Ecografía Tiroidea');
});

test('se puede registrar un nuevo tipo de solicitud', function () {
    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->call('abrirModalCrear')
        ->assertSet('modalOpen', true)
        ->set('nombre', 'Tomografía Axial Computarizada')
        ->set('descripcion', 'Estudio tomográfico con contraste')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertSet('modalOpen', false)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('tipos_solicitudes', [
        'nombre' => 'Tomografía Axial Computarizada',
        'descripcion' => 'Estudio tomográfico con contraste',
    ]);
});

test('se valida que el nombre sea obligatorio y unico al registrar tipo de solicitud', function () {
    TipoSolicitud::create(['nombre' => 'Resonancia Magnética']);

    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->call('abrirModalCrear')
        ->set('nombre', '')
        ->call('guardar')
        ->assertHasErrors(['nombre' => 'required'])
        ->set('nombre', 'Resonancia Magnética')
        ->call('guardar')
        ->assertHasErrors(['nombre' => 'unique']);
});

test('se puede editar un tipo de solicitud existente', function () {
    $tipo = TipoSolicitud::create([
        'nombre' => 'Biometría Hemática',
        'descripcion' => 'Descripción antigua',
    ]);

    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->call('abrirModalEditar', $tipo->id)
        ->assertSet('tipoSolicitudId', $tipo->id)
        ->assertSet('nombre', 'Biometría Hemática')
        ->set('nombre', 'Biometría Hemática Completa')
        ->set('descripcion', 'Nueva descripción actualizada')
        ->call('guardar')
        ->assertHasNoErrors()
        ->assertDispatched('swal');

    expect($tipo->fresh()->nombre)->toBe('Biometría Hemática Completa')
        ->and($tipo->fresh()->descripcion)->toBe('Nueva descripción actualizada');
});

test('se puede eliminar un tipo de solicitud si no tiene estudios vinculados en proformas', function () {
    $tipo = TipoSolicitud::create([
        'nombre' => 'Examen Sin Uso',
        'descripcion' => 'Temporal',
    ]);

    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->call('eliminar', $tipo->id)
        ->assertDispatched('swal');

    $this->assertSoftDeleted('tipos_solicitudes', ['id' => $tipo->id]);
});

test('se bloquea la eliminacion si el tipo de solicitud tiene solicitudes vinculadas en proformas', function () {
    $tipo = TipoSolicitud::create([
        'nombre' => 'Urocultivo con Antibiograma',
    ]);

    $proforma = Proforma::factory()->create([
        'sucursal_id' => $this->sucursal->id,
        'usuario_creador_id' => $this->admin->id,
    ]);

    ProformaSolicitud::create([
        'proforma_id' => $proforma->id,
        'tipo_solicitud_id' => $tipo->id,
        'observaciones' => 'Solicitud de control',
    ]);

    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->call('eliminar', $tipo->id)
        ->assertDispatched('swal');

    $this->assertDatabaseHas('tipos_solicitudes', [
        'id' => $tipo->id,
        'deleted_at' => null,
    ]);
});

test('el buscador en tiempo real filtra por nombre y descripcion', function () {
    TipoSolicitud::create(['nombre' => 'Electrocardiograma', 'descripcion' => 'Trazo basal']);
    TipoSolicitud::create(['nombre' => 'Espirometría', 'descripcion' => 'Capacidad pulmonar']);

    Livewire::actingAs($this->admin)
        ->test(TiposSolicitudesIndex::class)
        ->set('search', 'Electro')
        ->assertSee('Electrocardiograma')
        ->assertDontSee('Espirometría');
});

test('usuario sin permiso tipos-solicitudes.gestionar no ve boton de crear y se bloquean las acciones', function () {
    $usuario = User::factory()->create([
        'rol_id' => $this->rolRecepcion->id,
        'sucursal_id' => $this->sucursal->id,
        'activo' => true,
    ]);

    // Asignar permiso para ver el catálogo (ID 4 y ID 71), pero NO el permiso 72 (tipos-solicitudes.gestionar)
    $usuario->permisos()->attach([4, 71]);

    Livewire::actingAs($usuario)
        ->test(TiposSolicitudesIndex::class)
        ->assertDontSee('Nuevo Tipo de Solicitud')
        ->call('abrirModalCrear')
        ->assertDispatched('swal')
        ->call('guardar')
        ->assertDispatched('swal')
        ->call('eliminar', 1)
        ->assertDispatched('swal');
});
