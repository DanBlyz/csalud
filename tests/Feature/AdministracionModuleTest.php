<?php

use App\Livewire\Administracion\EspecialidadesIndex;
use App\Livewire\Administracion\RolesIndex;
use App\Livewire\Administracion\ServiciosIndex;
use App\Livewire\Administracion\SucursalesIndex;
use App\Livewire\Administracion\UsuariosIndex;
use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('unauthenticated users are redirected to login', function () {
    $this->get('/administracion/sucursales')->assertRedirect('/login');
    $this->get('/administracion/roles')->assertRedirect('/login');
    $this->get('/administracion/usuarios')->assertRedirect('/login');
    $this->get('/administracion/especialidades')->assertRedirect('/login');
    $this->get('/administracion/servicios')->assertRedirect('/login');
});

test('admin can access all administration pages', function () {
    $rolAdmin = Rol::factory()->create(['nombre' => 'Admin']);
    $admin = User::factory()->create([
        'rol_id' => $rolAdmin->id,
        'activo' => true,
    ]);

    $this->actingAs($admin)->get('/administracion/sucursales')->assertOk()->assertSeeLivewire(SucursalesIndex::class);
    $this->actingAs($admin)->get('/administracion/roles')->assertOk()->assertSeeLivewire(RolesIndex::class);
    $this->actingAs($admin)->get('/administracion/usuarios')->assertOk()->assertSeeLivewire(UsuariosIndex::class);
    $this->actingAs($admin)->get('/administracion/especialidades')->assertOk()->assertSeeLivewire(EspecialidadesIndex::class);
    $this->actingAs($admin)->get('/administracion/servicios')->assertOk()->assertSeeLivewire(ServiciosIndex::class);
});

test('sucursales index can create branch and enforces delete rule when users exist', function () {
    $rolAdmin = Rol::factory()->create(['nombre' => 'Admin']);
    $admin = User::factory()->create(['rol_id' => $rolAdmin->id, 'activo' => true]);

    Livewire::actingAs($admin)
        ->test(SucursalesIndex::class)
        ->call('abrirModalCrear')
        ->set('nombre', 'Sucursal Este')
        ->set('ciudad', 'Santa Cruz')
        ->set('telefono', '3-3445566')
        ->call('guardar')
        ->assertDispatched('swal');

    $sucursal = Sucursal::where('nombre', 'Sucursal Este')->first();
    expect($sucursal)->not->toBeNull();

    // Assign a user to the sucursal
    User::factory()->create(['sucursal_id' => $sucursal->id]);

    // Attempt to delete
    Livewire::actingAs($admin)
        ->test(SucursalesIndex::class)
        ->call('eliminar', $sucursal->id)
        ->assertDispatched('swal');

    // Branch should still exist because it has users
    expect(Sucursal::find($sucursal->id))->not->toBeNull();
});

test('usuarios index can create user, reset password and assign permissions', function () {
    $rolAdmin = Rol::factory()->create(['nombre' => 'Admin']);
    $rolDoctor = Rol::factory()->create(['nombre' => 'Médico']);
    $sucursal = Sucursal::factory()->create();
    $admin = User::factory()->create(['rol_id' => $rolAdmin->id, 'activo' => true]);

    $permiso = Permiso::firstOrCreate(['id' => 1], [
        'nombre' => 'gestion-usuarios',
        'descripcion' => 'Administración de usuarios',
    ]);

    // 1. Create User
    Livewire::actingAs($admin)
        ->test(UsuariosIndex::class)
        ->call('abrirModalCrear')
        ->set('nombres', 'Carlos')
        ->set('apellido_paterno', 'Mendoza')
        ->set('cedula', '99887766')
        ->set('email', 'cmendoza@csalud.com')
        ->set('sucursal_id', $sucursal->id)
        ->set('rol_id', $rolDoctor->id)
        ->set('password', 'password123')
        ->set('password_confirmation', 'password123')
        ->call('guardarUsuario')
        ->assertDispatched('swal');

    $nuevoUser = User::where('email', 'cmendoza@csalud.com')->first();
    expect($nuevoUser)->not->toBeNull();
    expect($nuevoUser->cedula)->toBe('99887766');

    // 2. Reset Password Modal (fas fa-key)
    Livewire::actingAs($admin)
        ->test(UsuariosIndex::class)
        ->call('abrirModalPassword', $nuevoUser->id)
        ->set('nuevaPassword', 'newsecret123')
        ->set('nuevaPassword_confirmation', 'newsecret123')
        ->call('guardarPassword')
        ->assertDispatched('swal');

    expect(Hash::check('newsecret123', $nuevoUser->fresh()->password))->toBeTrue();

    // 3. Assign Permissions Modal (fas fa-user-shield)
    Livewire::actingAs($admin)
        ->test(UsuariosIndex::class)
        ->call('abrirModalPermisos', $nuevoUser->id)
        ->set('permisosSeleccionados', [(string) $permiso->id])
        ->call('guardarPermisos')
        ->assertDispatched('swal');

    expect($nuevoUser->fresh()->permisos()->where('permisos.id', 1)->exists())->toBeTrue();

    // 4. Toggle Active Status
    Livewire::actingAs($admin)
        ->test(UsuariosIndex::class)
        ->call('toggleActivo', $nuevoUser->id)
        ->assertDispatched('swal');

    expect($nuevoUser->fresh()->activo)->toBeFalse();
});
