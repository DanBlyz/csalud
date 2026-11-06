<?php

namespace App\Http\Controllers;

use App\Models\BajaMedicamento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class BajaMedicamentoPdfController extends Controller
{
    /**
     * Genera e imprime el Formulario Oficial de Baja de Medicamentos en PDF.
     */
    public function pdf(BajaMedicamento $baja): Response
    {
        $user = Auth::user();
        if ($user && ! $user->tienePermiso('farmacia.bajas.ver') && ! $user->tienePermiso(9)) {
            abort(403, 'No cuenta con autorización para visualizar este reporte.');
        }

        $baja->load([
            'sucursal',
            'aprobadoPor',
            'usuarioCreador',
            'detalles.producto.marca',
            'detalles.lote',
            'detalles.seccion',
        ]);

        $pdf = Pdf::loadView('pdf.baja-medicamento', [
            'baja' => $baja,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream("formulario-baja-medicamentos-{$baja->codigo}.pdf");
    }
}
