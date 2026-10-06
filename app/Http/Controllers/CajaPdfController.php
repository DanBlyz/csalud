<?php

namespace App\Http\Controllers;

use App\Models\Caja;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class CajaPdfController extends Controller
{
    /**
     * Genera e imprime el Acta de Cierre y Arqueo de Caja en PDF.
     */
    public function actaCierre(Caja $caja): Response
    {
        $caja->load(['sucursal', 'user', 'pagos.user']);

        $pdf = Pdf::loadView('pdf.cierre-caja', [
            'caja' => $caja,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("acta-cierre-caja-{$caja->id}.pdf");
    }
}
