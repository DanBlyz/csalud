<?php

namespace App\Http\Controllers;

use App\Models\ReintegroInstitucion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReintegroInstitucionPdfController extends Controller
{
    /**
     * Genera e imprime el Acta Oficial de Reintegro de Medicamentos por Institución en formato PDF.
     */
    public function pdf(ReintegroInstitucion $reintegro): Response
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.reintegros.ver') && ! $user->tienePermiso(9)) {
            abort(403, 'No cuenta con autorización para visualizar este reporte.');
        }

        $reintegro->load([
            'institucion',
            'sucursal',
            'seccionDestino',
            'usuarioCreador',
            'detalles.producto.marca',
            'detalles.lote',
            'detalles.seccion',
            'aplicaciones.paciente',
            'aplicaciones.proforma',
            'aplicaciones.producto',
        ]);

        $pdf = Pdf::loadView('pdf.reintegro-institucion', [
            'reintegro' => $reintegro,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("acta-reintegro-institucion-{$reintegro->codigo}.pdf");
    }
}
