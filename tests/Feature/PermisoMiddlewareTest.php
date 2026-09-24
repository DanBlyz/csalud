<?php

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::get('/test-permiso-endpoint', fn () => 'acceso concedido')
        ->middleware(['auth', 'permiso:1']);
});

test('unauthenticated user is unauthorized', function () {
    $response = $this->get('/test-permiso-endpoint');

    $response->assertRedirect('/login');
});

test('inactive user is blocked even with admin role or permission', function () {
    $rolAdmin = Rol::factory()->create(['nombre' => 'Admin']);
    $user = User::factory()->create([
        'rol_id' => $rolAdmin->id,
        'activo' => false,
    ]);

    $response = $this->actingAs($user)->get('/test-permiso-endpoint');

    $response->assertStatus(403);
});

test('admin user bypasses permission checks', function () {
    $rolAdmin = Rol::factory()->create(['nombre' => 'Admin']);
    $user = User::factory()->create([
        'rol_id' => $rolAdmin->id,
        'activo' => true,
    ]);

    $response = $this->actingAs($user)->get('/test-permiso-endpoint');

    $response->assertOk();
    $response->assertSee('acceso concedido');
});

test('user without specific permission is forbidden', function () {
    $rol = Rol::factory()->create(['nombre' => 'Médico']);
    $user = User::factory()->create([
        'rol_id' => $rol->id,
        'activo' => true,
    ]);

    $response = $this->actingAs($user)->get('/test-permiso-endpoint');

    $response->assertStatus(403);
});

test('user with specific permission can access', function () {
    $rol = Rol::factory()->create(['nombre' => 'Médico']);
    $permiso = Permiso::firstOrCreate(['id' => 1], [
        'nombre' => 'ver-pacientes',
        'descripcion' => 'Ver lista de pacientes',
    ]);

    $user = User::factory()->create([
        'rol_id' => $rol->id,
        'activo' => true,
    ]);
    $user->permisos()->attach($permiso->id);

    $response = $this->actingAs($user)->get('/test-permiso-endpoint');

    $response->assertOk();
    $response->assertSee('acceso concedido');
});
