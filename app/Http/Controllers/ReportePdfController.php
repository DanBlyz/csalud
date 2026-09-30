<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Sucursal;
use App\Services\ReporteMovimientosService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportePdfController extends Controller
{
    /**
     * Genera e imprime el reporte oficial de Kardex de Movimientos y Valorización de Inventario en PDF.
     */
    public function movimientos(Request $request, ReporteMovimientosService $service): Response
    {
        $fechaInicio = $request->query('fecha_inicio', Carbon::now()->startOfMonth()->toDateString());
        $fechaFin = $request->query('fecha_fin', Carbon::now()->toDateString());
        $productoId = $request->filled('producto_id') ? (int) $request->query('producto_id') : null;
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        $soloConActividad = $request->boolean('solo_con_actividad', true);

        $datos = $service->generar(
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin,
            productoId: $productoId,
            sucursalId: $sucursalId,
            soloConActividad: $soloConActividad
        );

        $sucursal = $sucursalId ? Sucursal::find($sucursalId) : null;
        $productoFiltro = $productoId ? Producto::with('marca')->find($productoId) : null;

        $pdf = Pdf::loadView('pdf.reporte-movimientos', [
            'reporte' => $datos,
            'sucursal' => $sucursal,
            'productoFiltro' => $productoFiltro,
            'fechaInicioFormato' => Carbon::parse($fechaInicio)->format('d/m/Y'),
            'fechaFinFormato' => Carbon::parse($fechaFin)->format('d/m/Y'),
            'fechaEmision' => Carbon::now()->format('d/m/Y H:i'),
            'usuarioEmisor' => $request->user()?->name ?? 'Administración',
        ])->setPaper('letter', 'portrait');

        $slugProducto = $productoFiltro ? '-prod-'.$productoFiltro->id : '-general';

        return $pdf->stream("reporte-kardex-movimientos{$slugProducto}-{$fechaInicio}-{$fechaFin}.pdf");
    }
}
