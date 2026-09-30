<?php

namespace App\Livewire\Administracion;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\ReporteMovimientosService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReportesIndex extends Component
{
    // Pestaña o tipo de reporte activo en el Centro de Reportes
    public string $reporteActivo = 'kardex'; // 'kardex', etc.

    // Filtros para Reporte Kardex de Movimientos
    public string $fecha_inicio = '';

    public string $fecha_fin = '';

    public ?int $sucursal_id = null;

    public ?int $producto_id = null;

    public string $buscarProducto = '';

    public bool $solo_con_actividad = true;

    // Estado de la previsualización
    public bool $reporteGenerado = false;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $datosReporte = null;

    public function mount(): void
    {
        $this->fecha_inicio = Carbon::now()->startOfMonth()->toDateString();
        $this->fecha_fin = Carbon::now()->toDateString();
        $this->sucursal_id = Auth::user()->sucursal_id;
    }

    /**
     * Presets rápidos para rango de fechas.
     */
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

    public function render(): View
    {
        $sucursales = Sucursal::orderBy('nombre')->get();

        $productosQuery = Producto::query()
            ->with('marca')
            ->orderBy('nombre', 'asc');

        if (! empty($this->buscarProducto)) {
            $search = '%'.trim($this->buscarProducto).'%';
            $productosQuery->where('nombre', 'like', $search)
                ->orWhereHas('marca', fn ($m) => $m->where('nombre', 'like', $search));
        }

        $productos = $productosQuery->get(['id', 'nombre', 'marca_id', 'unidad_medida']);

        return view('livewire.administracion.reportes-index', [
            'sucursales' => $sucursales,
            'productos' => $productos,
        ]);
    }
}
