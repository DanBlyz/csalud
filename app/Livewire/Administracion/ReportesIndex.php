<?php

namespace App\Livewire\Administracion;

use App\Models\Institucion;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\ReporteFlujoCajaService;
use App\Services\ReporteMovimientosService;
use App\Services\ReportePacientesService;
use App\Services\ReportePlanillaConvenioService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

#[Layout('layouts.app')]
class ReportesIndex extends Component
{
    // Pestaña o tipo de reporte activo: 'kardex', 'pacientes', 'convenios', 'flujo_caja'
    public string $reporteActivo = 'kardex';

    // =========================================================================
    // FILTROS: REPORTE KARDEX DE MOVIMIENTOS DE INVENTARIO
    // =========================================================================
    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public ?int $sucursal_id = null;

    public ?int $producto_id = null;

    public string $buscarProducto = '';

    public bool $solo_con_actividad = true;

    public bool $reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $datosReporte = null;

    // =========================================================================
    // FILTROS: REPORTE DE INGRESOS Y SALIDAS DE PACIENTES
    // =========================================================================
    public string $pac_fecha_inicio = '';

    public string $pac_fecha_fin = '';

    public ?int $pac_institucion_id = null; // null = Todas, -1 = Solo Particulares, >0 = ID Institución

    public ?int $pac_sucursal_id = null;

    public string $pac_tipo_filtro = 'todos'; // 'todos', 'ingresos', 'salidas', 'internados'

    public string $pac_tipo_atencion = ''; // '' = Todas, 'Ambulatoria', 'Internacion'

    public bool $pac_reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $pac_datosReporte = null;

    // =========================================================================
    // FILTROS: PLANILLA DE PACIENTES POR CONVENIO / INSTITUCIÓN (EXCEL)
    // =========================================================================
    public ?int $conv_institucion_id = null;

    public int $conv_mes = 1;

    public int $conv_anio = 2026;

    public ?int $conv_sucursal_id = null;

    public bool $conv_solo_atendidos = true;

    public bool $conv_reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $conv_datosReporte = null;

    // =========================================================================
    // FILTROS: REPORTE MENSUAL DE FLUJO DE CAJA, INGRESOS Y EGRESOS (EXCEL)
    // =========================================================================
    public int $flujo_mes = 1;

    public int $flujo_anio = 2026;

    public ?int $flujo_sucursal_id = null;

    public bool $flujo_reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $flujo_datosReporte = null;

    // =========================================================================
    // FILTROS: RESUMEN ANUAL DE INGRESOS Y GASTOS (EXCEL)
    // =========================================================================
    public int $anual_anio = 2026;

    public ?int $anual_sucursal_id = null;

    public bool $anual_reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $anual_datosReporte = null;

    protected $queryString = [
        'reporteActivo' => ['except' => 'kardex'],
    ];

    public function mount(): void
    {
        $now = Carbon::now();

        // Inicializar Kardex
        $this->fecha_inicio = $now->copy()->startOfMonth()->toDateString();
        $this->fecha_fin = $now->toDateString();
        $this->sucursal_id = Auth::user()->sucursal_id;

        // Inicializar Reporte Pacientes
        $this->pac_fecha_inicio = $now->copy()->startOfMonth()->toDateString();
        $this->pac_fecha_fin = $now->toDateString();
        $this->pac_sucursal_id = Auth::user()->sucursal_id;

        // Inicializar Planilla de Convenios
        $this->conv_mes = (int) $now->month;
        $this->conv_anio = (int) $now->year;
        $this->conv_sucursal_id = Auth::user()->sucursal_id;
        $primeraInstitucion = Institucion::where('estado', 'Activo')->orWhere('estado', 1)->first();
        if ($primeraInstitucion) {
            $this->conv_institucion_id = $primeraInstitucion->id;
        }

        // Inicializar Flujo de Caja
        $this->flujo_mes = (int) $now->month;
        $this->flujo_anio = (int) $now->year;
        $this->flujo_sucursal_id = Auth::user()->sucursal_id;

        // Inicializar Resumen Anual
        $this->anual_anio = (int) $now->year;
        $this->anual_sucursal_id = Auth::user()->sucursal_id;
    }

    public function cambiarTipoReporte(string $tipo): void
    {
        $this->reporteActivo = $tipo;
    }

    // =========================================================================
    // ACCIONES: KARDEX DE INVENTARIO
    // =========================================================================

    public function aplicarRango(string $preset): void
    {
        $now = Carbon::now();

        switch ($preset) {
            case 'mes_actual':
                $this->fecha_inicio = $now->copy()->startOfMonth()->toDateString();
                $this->fecha_fin = $now->toDateString();
                break;
            case 'mes_anterior':
                $this->fecha_inicio = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $this->fecha_fin = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'ultimos_30':
                $this->fecha_inicio = $now->copy()->subDays(30)->toDateString();
                $this->fecha_fin = $now->toDateString();
                break;
            case 'anio_actual':
                $this->fecha_inicio = $now->copy()->startOfYear()->toDateString();
                $this->fecha_fin = $now->toDateString();
                break;
        }

        $this->reporteGenerado = false;
        $this->datosReporte = null;
    }

    public function updatedFechaInicio(): void
    {
        $this->reporteGenerado = false;
    }

    public function updatedFechaFin(): void
    {
        $this->reporteGenerado = false;
    }

    public function updatedProductoId(): void
    {
        $this->reporteGenerado = false;
    }

    public function updatedSucursalId(): void
    {
        $this->reporteGenerado = false;
    }

    public function updatedSoloConActividad(): void
    {
        $this->reporteGenerado = false;
    }

    public function previsualizarReporte(ReporteMovimientosService $service): void
    {
        $this->validate([
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'producto_id' => ['nullable', 'exists:productos,id'],
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es requerida.',
            'fecha_fin.required' => 'La fecha de fin es requerida.',
            'fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ]);

        $this->datosReporte = $service->generar(
            fechaInicio: $this->fecha_inicio,
            fechaFin: $this->fecha_fin,
            productoId: $this->producto_id,
            sucursalId: $this->sucursal_id,
            soloConActividad: $this->solo_con_actividad
        );

        $this->reporteGenerado = true;
    }

    public function limpiarFiltros(): void
    {
        $this->fecha_inicio = Carbon::now()->startOfMonth()->toDateString();
        $this->fecha_fin = Carbon::now()->toDateString();
        $this->producto_id = null;
        $this->buscarProducto = '';
        $this->solo_con_actividad = true;
        $this->reporteGenerado = false;
        $this->datosReporte = null;
    }

    // =========================================================================
    // ACCIONES: REPORTE DE INGRESOS Y SALIDAS DE PACIENTES
    // =========================================================================

    public function aplicarRangoPacientes(string $preset): void
    {
        $now = Carbon::now();

        switch ($preset) {
            case 'mes_actual':
                $this->pac_fecha_inicio = $now->copy()->startOfMonth()->toDateString();
                $this->pac_fecha_fin = $now->toDateString();
                break;
            case 'mes_anterior':
                $this->pac_fecha_inicio = $now->copy()->subMonth()->startOfMonth()->toDateString();
                $this->pac_fecha_fin = $now->copy()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'ultimos_30':
                $this->pac_fecha_inicio = $now->copy()->subDays(30)->toDateString();
                $this->pac_fecha_fin = $now->toDateString();
                break;
            case 'anio_actual':
                $this->pac_fecha_inicio = $now->copy()->startOfYear()->toDateString();
                $this->pac_fecha_fin = $now->toDateString();
                break;
        }

        $this->pac_reporteGenerado = false;
        $this->pac_datosReporte = null;
    }

    public function updatedPacFechaInicio(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function updatedPacFechaFin(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function updatedPacInstitucionId(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function updatedPacSucursalId(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function updatedPacTipoFiltro(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function updatedPacTipoAtencion(): void
    {
        $this->pac_reporteGenerado = false;
    }

    public function previsualizarReportePacientes(ReportePacientesService $service): void
    {
        $this->validate([
            'pac_fecha_inicio' => ['required', 'date'],
            'pac_fecha_fin' => ['required', 'date', 'after_or_equal:pac_fecha_inicio'],
            'pac_sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'pac_tipo_filtro' => ['required', 'in:todos,ingresos,salidas,internados'],
            'pac_tipo_atencion' => ['nullable', 'in:Ambulatoria,Internacion'],
        ], [
            'pac_fecha_inicio.required' => 'La fecha de inicio es obligatoria.',
            'pac_fecha_fin.required' => 'La fecha de fin es obligatoria.',
            'pac_fecha_fin.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la de inicio.',
        ]);

        $this->pac_datosReporte = $service->generar(
            fechaInicio: $this->pac_fecha_inicio,
            fechaFin: $this->pac_fecha_fin,
            institucionId: $this->pac_institucion_id,
            sucursalId: $this->pac_sucursal_id,
            tipoFiltro: $this->pac_tipo_filtro,
            tipoAtencion: ! empty($this->pac_tipo_atencion) ? $this->pac_tipo_atencion : null
        );

        $this->pac_reporteGenerado = true;
    }

    public function limpiarFiltrosPacientes(): void
    {
        $now = Carbon::now();
        $this->pac_fecha_inicio = $now->copy()->startOfMonth()->toDateString();
        $this->pac_fecha_fin = $now->toDateString();
        $this->pac_institucion_id = null;
        $this->pac_sucursal_id = Auth::user()->sucursal_id;
        $this->pac_tipo_filtro = 'todos';
        $this->pac_tipo_atencion = '';
        $this->pac_reporteGenerado = false;
        $this->pac_datosReporte = null;
    }

    // =========================================================================
    // ACCIONES: PLANILLA DE PACIENTES POR CONVENIO / INSTITUCIÓN (EXCEL)
    // =========================================================================

    public function updatedConvInstitucionId(): void
    {
        $this->conv_reporteGenerado = false;
    }

    public function updatedConvMes(): void
    {
        $this->conv_reporteGenerado = false;
    }

    public function updatedConvAnio(): void
    {
        $this->conv_reporteGenerado = false;
    }

    public function updatedConvSucursalId(): void
    {
        $this->conv_reporteGenerado = false;
    }

    public function updatedConvSoloAtendidos(): void
    {
        $this->conv_reporteGenerado = false;
    }

    public function previsualizarReporteConvenio(ReportePlanillaConvenioService $service): void
    {
        $this->validate([
            'conv_institucion_id' => ['required', 'exists:instituciones,id'],
            'conv_mes' => ['required', 'integer', 'min:1', 'max:12'],
            'conv_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'conv_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'conv_institucion_id.required' => 'Debe seleccionar una institución o convenio.',
            'conv_mes.required' => 'El mes es requerido.',
            'conv_anio.required' => 'El año es requerido.',
        ]);

        $this->conv_datosReporte = $service->generar(
            institucionId: (int) $this->conv_institucion_id,
            mes: (int) $this->conv_mes,
            anio: (int) $this->conv_anio,
            sucursalId: $this->conv_sucursal_id,
            soloAtendidos: $this->conv_solo_atendidos
        );

        $this->conv_reporteGenerado = true;
    }

    public function descargarExcelConvenio(ReportePlanillaConvenioService $service)
    {
        if (! Auth::user()?->tienePermiso('reportes.exportar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para exportar reportes.',
            ]);

            return null;
        }

        $this->validate([
            'conv_institucion_id' => ['required', 'exists:instituciones,id'],
            'conv_mes' => ['required', 'integer', 'min:1', 'max:12'],
            'conv_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'conv_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'conv_institucion_id.required' => 'Debe seleccionar una institución o convenio.',
            'conv_mes.required' => 'El mes es requerido.',
            'conv_anio.required' => 'El año es requerido.',
        ]);

        $datos = $service->generar(
            institucionId: (int) $this->conv_institucion_id,
            mes: (int) $this->conv_mes,
            anio: (int) $this->conv_anio,
            sucursalId: $this->conv_sucursal_id,
            soloAtendidos: $this->conv_solo_atendidos
        );

        $spreadsheet = $service->exportarExcel($datos);
        $institucion = Institucion::find($this->conv_institucion_id);
        $slugInstitucion = $institucion ? str_replace(' ', '_', strtolower($institucion->nombre)) : 'convenio';
        $mesNombre = ReportePlanillaConvenioService::MESES[(int) $this->conv_mes] ?? (string) $this->conv_mes;
        $filename = "Planilla_Pacientes_{$slugInstitucion}_{$mesNombre}_{$this->conv_anio}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function limpiarFiltrosConvenio(): void
    {
        $now = Carbon::now();
        $this->conv_mes = (int) $now->month;
        $this->conv_anio = (int) $now->year;
        $this->conv_sucursal_id = Auth::user()->sucursal_id;
        $this->conv_solo_atendidos = true;
        $this->conv_reporteGenerado = false;
        $this->conv_datosReporte = null;
    }

    // =========================================================================
    // ACCIONES: REPORTE MENSUAL DE FLUJO DE CAJA, INGRESOS Y EGRESOS (EXCEL)
    // =========================================================================

    public function updatedFlujoMes(): void
    {
        $this->flujo_reporteGenerado = false;
    }

    public function updatedFlujoAnio(): void
    {
        $this->flujo_reporteGenerado = false;
    }

    public function updatedFlujoSucursalId(): void
    {
        $this->flujo_reporteGenerado = false;
    }

    public function previsualizarReporteFlujoCaja(ReporteFlujoCajaService $service): void
    {
        $this->validate([
            'flujo_mes' => ['required', 'integer', 'min:1', 'max:12'],
            'flujo_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'flujo_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'flujo_mes.required' => 'El mes es requerido.',
            'flujo_anio.required' => 'El año es requerido.',
        ]);

        $this->flujo_datosReporte = $service->generar(
            mes: (int) $this->flujo_mes,
            anio: (int) $this->flujo_anio,
            sucursalId: $this->flujo_sucursal_id
        );

        $this->flujo_reporteGenerado = true;
    }

    public function descargarExcelFlujoCaja(ReporteFlujoCajaService $service)
    {
        if (! Auth::user()?->tienePermiso('reportes.exportar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para exportar reportes.',
            ]);

            return null;
        }

        $this->validate([
            'flujo_mes' => ['required', 'integer', 'min:1', 'max:12'],
            'flujo_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'flujo_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'flujo_mes.required' => 'El mes es requerido.',
            'flujo_anio.required' => 'El año es requerido.',
        ]);

        $datos = $service->generar(
            mes: (int) $this->flujo_mes,
            anio: (int) $this->flujo_anio,
            sucursalId: $this->flujo_sucursal_id
        );

        $spreadsheet = $service->exportarExcel($datos);
        $mesNombre = ReporteFlujoCajaService::MESES[(int) $this->flujo_mes] ?? (string) $this->flujo_mes;
        $filename = "Flujo_Caja_Ingresos_Egresos_{$mesNombre}_{$this->flujo_anio}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function limpiarFiltrosFlujoCaja(): void
    {
        $now = Carbon::now();
        $this->flujo_mes = (int) $now->month;
        $this->flujo_anio = (int) $now->year;
        $this->flujo_sucursal_id = Auth::user()->sucursal_id;
        $this->flujo_reporteGenerado = false;
        $this->flujo_datosReporte = null;
    }

    // =========================================================================
    // ACCIONES: RESUMEN ANUAL DE INGRESOS Y GASTOS (EXCEL)
    // =========================================================================

    public function previsualizarReporteAnual(ReporteFlujoCajaService $service): void
    {
        $this->validate([
            'anual_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'anual_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'anual_anio.required' => 'El año de gestión es requerido.',
        ]);

        $this->anual_datosReporte = $service->generarAnual(
            anio: (int) $this->anual_anio,
            sucursalId: $this->anual_sucursal_id
        );

        $this->anual_reporteGenerado = true;
    }

    public function descargarExcelAnual(ReporteFlujoCajaService $service)
    {
        if (! Auth::user()?->tienePermiso('reportes.exportar')) {
            $this->dispatch('swal', [
                'icon' => 'error',
                'title' => 'Acceso Denegado',
                'text' => 'No tiene permiso para exportar reportes.',
            ]);

            return null;
        }

        $this->validate([
            'anual_anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'anual_sucursal_id' => ['nullable', 'exists:sucursales,id'],
        ], [
            'anual_anio.required' => 'El año de gestión es requerido.',
        ]);

        $datos = $service->generarAnual(
            anio: (int) $this->anual_anio,
            sucursalId: $this->anual_sucursal_id
        );

        $spreadsheet = $service->exportarExcelAnual($datos);
        $filename = "Resumen_Ingresos_y_Gastos_Gestion_{$this->anual_anio}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public function limpiarFiltrosAnual(): void
    {
        $now = Carbon::now();
        $this->anual_anio = (int) $now->year;
        $this->anual_sucursal_id = Auth::user()->sucursal_id;
        $this->anual_reporteGenerado = false;
        $this->anual_datosReporte = null;
    }

    public function render(): View
    {
        $sucursales = Sucursal::orderBy('nombre')->get();
        $instituciones = Institucion::orderBy('nombre')->get();

        $productosQuery = Producto::query()
            ->with('marca')
            ->orderBy('nombre', 'asc');

        if (! empty($this->buscarProducto)) {
            $search = '%' . trim($this->buscarProducto) . '%';
            $productosQuery->where('nombre', 'like', $search)
                ->orWhereHas('marca', fn($m) => $m->where('nombre', 'like', $search));
        }

        $productos = $productosQuery->get(['id', 'nombre', 'marca_id', 'unidad_medida']);

        return view('livewire.administracion.reportes-index', [
            'sucursales' => $sucursales,
            'instituciones' => $instituciones,
            'productos' => $productos,
        ]);
    }
}
