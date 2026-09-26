<?php

namespace App\Http\Controllers;

use App\Models\Proforma;
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
                $query->whereIn('tipo_movimiento', ['Salida Receta', 'Salida Farmacia'])
                    ->with(['producto', 'lote']);
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
}
