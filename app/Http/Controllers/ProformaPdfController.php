<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
use App\Models\Receta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class ProformaPdfController extends Controller
{
    /**
     * Genera e imprime el Detalle Clínico-Administrativo de la Proforma en PDF.
     */
    public function detalle(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'medicos',
            'servicios.servicio.categoria',
            'consumosExtras.producto',
            'recetaActiva.detalles.producto',
            'movimientosInventario' => function ($query) {
                $query->whereIn('tipo_movimiento', ['Salida Receta', 'Salida Farmacia', 'Devolución', 'Devolucion'])
                    ->with(['producto', 'lote'])
                    ->orderBy('created_at', 'asc');
            },
            'pagos.user',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-detalle', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("proforma-{$proforma->id}-detalle.pdf");
    }

    /**
     * Genera e imprime el Resumen Clínico-Administrativo de la Proforma en PDF (Costos Finales consolidados para Farmacia y Extras).
     */
    public function resumen(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'medicos',
            'servicios.servicio.categoria',
            'consumosExtras',
            'movimientosInventario' => function ($query) {
                $query->whereIn('tipo_movimiento', ['Salida Receta', 'Salida Farmacia', 'Devolución', 'Devolucion'])
                    ->with(['producto'])
                    ->orderBy('created_at', 'asc');
            },
            'pagos.user',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-resumen', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("proforma-{$proforma->id}-resumen.pdf");
    }

    /**
     * Genera e imprime el Recibo Oficial de Caja / Comprobante de Pago en PDF.
     */
    public function recibo(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'pagos.user',
        ]);

        $pdf = Pdf::loadView('pdf.recibo-caja', [
            'proforma' => $proforma,
            'pagos' => $proforma->pagos()->orderBy('created_at', 'asc')->get(),
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("recibo-caja-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Evolución en PDF.
     */
    public function evolucion(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'evoluciones.usuarioCreador',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-hoja-evolucion', [
            'proforma' => $proforma,
            'evoluciones' => $proforma->evoluciones()->orderBy('fecha_hora', 'asc')->get(),
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("hoja-evolucion-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Indicaciones en PDF.
     */
    public function indicacion(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'indicaciones.usuarioCreador',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-hoja-indicacion', [
            'proforma' => $proforma,
            'indicaciones' => $proforma->indicaciones()->orderBy('fecha_hora', 'asc')->get(),
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("hoja-indicaciones-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Internación en PDF.
     */
    public function internacion(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'internaciones.usuarioCreador',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-hoja-internacion', [
            'proforma' => $proforma,
            'internaciones' => $proforma->internaciones()->orderBy('fecha_hora', 'asc')->get(),
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("hoja-internacion-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Receta Médica en PDF.
     */
    public function receta(Proforma $proforma, ?Receta $receta = null): Response
    {
        if (! $receta || ! $receta->exists) {
            $receta = $proforma->recetaActiva ?? $proforma->recetas()->latest()->firstOrFail();
        }

        if ($receta->proforma_id !== $proforma->id) {
            abort(404, 'La receta no corresponde a la proforma indicada.');
        }

        $receta->load([
            'detalles.producto.marca',
            'doctor.especialidad',
            'user',
        ]);

        $proforma->load([
            'paciente.institucion',
            'sucursal',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-receta', [
            'proforma' => $proforma,
            'receta' => $receta,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("receta-{$receta->id}-proforma-{$proforma->id}.pdf");
    }
}
