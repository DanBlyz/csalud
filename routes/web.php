<?php

use App\Http\Controllers\ProfileController;
use App\Livewire\Administracion\EspecialidadesIndex;
use App\Livewire\Administracion\RolesIndex;
use App\Livewire\Administracion\ServiciosIndex;
use App\Livewire\Administracion\SucursalesIndex;
use App\Livewire\Administracion\UsuariosIndex;
use App\Livewire\Farmacia\DespachosIndex;
use App\Livewire\Farmacia\LotesIndex;
use App\Livewire\Farmacia\MovimientosIndex;
use App\Livewire\Farmacia\ProductosIndex;
use App\Livewire\Pacientes\PacientesIndex;
use App\Livewire\Proformas\ProformaCrear;
use App\Livewire\Proformas\ProformaDetalle;
use App\Livewire\Proformas\ProformasIndex;
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

    // Módulo Pacientes
    Route::get('/pacientes', PacientesIndex::class)
        ->middleware('permiso:5')
        ->name('pacientes.index');

    // Módulo Proformas Clínicas
    Route::prefix('proformas')->as('proformas.')->group(function () {
        Route::get('/', ProformasIndex::class)
            ->middleware('permiso:7')
            ->name('index');

        Route::get('/nueva', ProformaCrear::class)
            ->middleware('permiso:6')
            ->name('crear');

        Route::get('/{proforma}', ProformaDetalle::class)
            ->middleware('permiso:7')
            ->name('show');
    });

    // Módulo Farmacia e Inventario
    Route::prefix('farmacia')->as('farmacia.')->group(function () {
        Route::get('/productos', ProductosIndex::class)
            ->middleware('permiso:9')
            ->name('productos');

        Route::get('/lotes', LotesIndex::class)
            ->middleware('permiso:9')
            ->name('lotes');

        Route::get('/despachos', DespachosIndex::class)
            ->middleware('permiso:9')
            ->name('despachos');

        Route::get('/movimientos', MovimientosIndex::class)
            ->middleware('permiso:9')
            ->name('movimientos');
    });
});

require __DIR__.'/auth.php';
