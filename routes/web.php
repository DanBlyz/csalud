<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Administracion\EspecialidadesIndex;
use App\Livewire\Administracion\RolesIndex;
use App\Livewire\Administracion\ServiciosIndex;
use App\Livewire\Administracion\SucursalesIndex;
use App\Livewire\Administracion\UsuariosIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Módulo Administración
    Route::prefix('administracion')->as('administracion.')->group(function () {
        Route::get('/sucursales', SucursalesIndex::class)
            ->middleware('permiso:2')
            ->name('sucursales');

        Route::get('/roles', RolesIndex::class)
            ->middleware('permiso:3')
            ->name('roles');

        Route::get('/usuarios', UsuariosIndex::class)
            ->middleware('permiso:1')
            ->name('usuarios');

        Route::get('/especialidades', EspecialidadesIndex::class)
            ->middleware('permiso:4')
            ->name('especialidades');

        Route::get('/servicios', ServiciosIndex::class)
            ->middleware('permiso:4')
            ->name('servicios');
    });
});

require __DIR__.'/auth.php';
