<?php

use App\Http\Controllers\BajaMedicamentoPdfController;
use App\Http\Controllers\CajaPdfController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProformaPdfController;
use App\Http\Controllers\ReporteExcelController;
use App\Http\Controllers\ReportePdfController;
use App\Livewire\Administracion\EspecialidadesIndex;
use App\Livewire\Administracion\ReportesIndex;
use App\Livewire\Administracion\RolesIndex;
use App\Livewire\Administracion\ServiciosIndex;
use App\Livewire\Administracion\SucursalesIndex;
use App\Livewire\Administracion\TiposSolicitudesIndex;
use App\Livewire\Administracion\UsuariosIndex;
use App\Livewire\Caja\CajaIndex;
use App\Livewire\Caja\CierresIndex;
use App\Livewire\DashboardIndex;
use App\Livewire\Farmacia\BajasMedicamentosIndex;
use App\Livewire\Farmacia\DespachosIndex;
use App\Livewire\Farmacia\LotesIndex;
use App\Livewire\Farmacia\MarcasIndex;
use App\Livewire\Farmacia\MovimientosIndex;
use App\Livewire\Farmacia\ProductosIndex;
use App\Livewire\Farmacia\ProveedoresIndex;
use App\Livewire\Farmacia\SeccionesIndex;
use App\Livewire\Pacientes\InstitucionesIndex;
use App\Livewire\Pacientes\PacientesIndex;
use App\Livewire\Proformas\ProformaCrear;
use App\Livewire\Proformas\ProformaDetalle;
use App\Livewire\Proformas\ProformasIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/dashboard', DashboardIndex::class)->middleware(['auth', 'verified'])->name('dashboard');

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

        Route::get('/tipos-solicitudes', TiposSolicitudesIndex::class)
            ->middleware('permiso:4')
            ->name('tipos-solicitudes');

        Route::get('/reportes', ReportesIndex::class)
            ->middleware('permiso:1')
            ->name('reportes');

        Route::get('/reportes/movimientos/pdf', [ReportePdfController::class, 'movimientos'])
            ->middleware('permiso:1')
            ->name('reportes.movimientos.pdf');

        Route::get('/reportes/pacientes/pdf', [ReportePdfController::class, 'pacientes'])
            ->middleware('permiso:1')
            ->name('reportes.pacientes.pdf');

        Route::get('/reportes/convenios/excel', [ReporteExcelController::class, 'planillaConvenios'])
            ->middleware('permiso:1')
            ->name('reportes.convenios.excel');

        Route::get('/reportes/flujo-caja/excel', [ReporteExcelController::class, 'flujoCaja'])
            ->middleware('permiso:1')
            ->name('reportes.flujo_caja.excel');

        Route::get('/reportes/resumen-anual/excel', [ReporteExcelController::class, 'resumenAnual'])
            ->middleware('permiso:1')
            ->name('reportes.resumen_anual.excel');
    });

    // Módulo Pacientes e Instituciones
    Route::prefix('pacientes')->as('pacientes.')->group(function () {
        Route::get('/', PacientesIndex::class)
            ->middleware('permiso:5')
            ->name('index');

        Route::get('/instituciones', InstitucionesIndex::class)
            ->middleware('permiso:5')
            ->name('instituciones');
    });

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

        Route::get('/secciones', SeccionesIndex::class)
            ->middleware('permiso:9')
            ->name('secciones');

        Route::get('/despachos', DespachosIndex::class)
            ->middleware('permiso:9')
            ->name('despachos');

        Route::get('/movimientos', MovimientosIndex::class)
            ->middleware('permiso:9')
            ->name('movimientos');

        Route::get('/marcas', MarcasIndex::class)
            ->middleware('permiso:9')
            ->name('marcas');

        Route::get('/proveedores', ProveedoresIndex::class)
            ->middleware('permiso:9')
            ->name('proveedores');

        Route::get('/bajas', BajasMedicamentosIndex::class)
            ->middleware('permiso:farmacia.bajas.ver')
            ->name('bajas');

        Route::get('/bajas/{baja}/pdf', [BajaMedicamentoPdfController::class, 'pdf'])
            ->middleware('permiso:farmacia.bajas.ver')
            ->name('bajas.pdf');
    });

    // Módulo Caja y Pagos
    Route::prefix('caja')->as('caja.')->group(function () {
        Route::get('/', CajaIndex::class)
            ->middleware('permiso:10')
            ->name('index');

        Route::get('/cierres', CierresIndex::class)
            ->middleware('permiso:10')
            ->name('cierres');

        Route::get('/cajas/{caja}/pdf/arqueo', [CajaPdfController::class, 'actaCierre'])
            ->middleware('permiso:10')
            ->name('pdf.arqueo');
    });

    // Rutas de Documentos e Impresión PDF
    Route::prefix('proformas/{proforma}/pdf')->as('proformas.pdf.')->group(function () {
        Route::get('/detalle', [ProformaPdfController::class, 'detalle'])->name('detalle');
        Route::get('/resumen', [ProformaPdfController::class, 'resumen'])->name('resumen');
        Route::get('/recibo', [ProformaPdfController::class, 'recibo'])->name('recibo');
        Route::get('/evolucion', [ProformaPdfController::class, 'evolucion'])->name('evolucion');
        Route::get('/indicacion', [ProformaPdfController::class, 'indicacion'])->name('indicacion');
        Route::get('/internacion', [ProformaPdfController::class, 'internacion'])->name('internacion');
        Route::get('/receta/{receta?}', [ProformaPdfController::class, 'receta'])->name('receta');
        Route::get('/recetas-externas/{recetaExterna}', [ProformaPdfController::class, 'recetaExterna'])->name('receta_externa');
        Route::get('/historia-clinica', [ProformaPdfController::class, 'historiaClinica'])->name('historia_clinica');
        Route::get('/epicrisis', [ProformaPdfController::class, 'epicrisis'])->name('epicrisis');
        Route::get('/cirugias/{cirugia}', [ProformaPdfController::class, 'cirugia'])->name('cirugia');
        Route::get('/cirugias/{cirugia}/anestesiologia', [ProformaPdfController::class, 'cirugiaAnestesiologia'])->name('cirugia_anestesiologia');
        Route::get('/cirugias/{cirugia}/enfermeria', [ProformaPdfController::class, 'cirugiaEnfermeria'])->name('cirugia_enfermeria');
        Route::get('/alta-solicitada', [ProformaPdfController::class, 'altaSolicitada'])->name('alta_solicitada');
        Route::get('/registro-enfermeria', [ProformaPdfController::class, 'registroEnfermeria'])->name('registro_enfermeria');
        Route::get('/signos-vitales', [ProformaPdfController::class, 'signosVitales'])->name('signos_vitales');
        Route::get('/kardex-enfermeria', [ProformaPdfController::class, 'kardexEnfermeria'])->name('kardex_enfermeria');
        Route::get('/control-liquidos', [ProformaPdfController::class, 'controlLiquidos'])->name('control_liquidos');
        Route::get('/consentimiento-anestesia', [ProformaPdfController::class, 'consentimientoAnestesia'])->name('consentimiento_anestesia');
        Route::get('/consentimiento-quirurgico', [ProformaPdfController::class, 'consentimientoQuirurgico'])->name('consentimiento_quirurgico');
        Route::get('/tarjeta-tratamiento', [ProformaPdfController::class, 'tarjetaTratamiento'])->name('tarjeta_tratamiento');
        Route::get('/tratamiento-enfermeria', [ProformaPdfController::class, 'tratamientoEnfermeria'])->name('tratamiento_enfermeria');
        Route::get('/grafica-signos-vitales', [ProformaPdfController::class, 'graficaSignosVitales'])->name('grafica_signos_vitales');
        Route::get('/registro-glucemias', [ProformaPdfController::class, 'registroGlucemias'])->name('registro_glucemias');
        Route::get('/descargo-medicamentos-insumos', [ProformaPdfController::class, 'descargoMedicamentosInsumos'])->name('descargo_medicamentos_insumos');
        Route::get('/reporte-enfermeria-doc', [ProformaPdfController::class, 'reporteEnfermeriaDoc'])->name('reporte_enfermeria_doc');
    });
});

require __DIR__.'/auth.php';
