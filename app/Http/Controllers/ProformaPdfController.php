<?php

namespace App\Http\Controllers;

use App\Models\Cirugia;
use App\Models\Proforma;
use App\Models\Receta;
use App\Models\RecetaExterna;
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

    /**
     * Genera e imprime la Historia Clínica en PDF.
     */
    public function historiaClinica(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'medicos.especialidad',
            'historiaClinica.usuarioCreador',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-historia-clinica', [
            'proforma' => $proforma,
            'historia' => $proforma->historiaClinica,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("historia-clinica-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Epicrisis de Alta Médica en PDF.
     */
    public function epicrisis(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'medicos.especialidad',
            'epicrisis.usuarioCreador',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-epicrisis', [
            'proforma' => $proforma,
            'epicrisis' => $proforma->epicrisis,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("epicrisis-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Descargo Completo de Quirófano (Hoja Anestesiología + Hoja Enfermería).
     */
    public function cirugia(Proforma $proforma, Cirugia $cirugia): Response
    {
        if ($cirugia->proforma_id !== $proforma->id) {
            abort(404, 'La cirugía no corresponde a la proforma indicada.');
        }

        $proforma->load(['paciente.institucion', 'sucursal']);
        $cirugia->load([
            'cirujano',
            'primerAyudante',
            'segundoAyudante',
            'anestesiologo',
            'instrumentador',
            'circulante',
            'receta.detalles.producto',
            'receta.detalles.seccion',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-cirugia', [
            'proforma' => $proforma,
            'cirugia' => $cirugia,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("descargo-quirofano-{$cirugia->numero}-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Anestesiología del Descargo de Quirófano.
     */
    public function cirugiaAnestesiologia(Proforma $proforma, Cirugia $cirugia): Response
    {
        if ($cirugia->proforma_id !== $proforma->id) {
            abort(404, 'La cirugía no corresponde a la proforma indicada.');
        }

        $proforma->load(['paciente.institucion', 'sucursal']);
        $cirugia->load([
            'cirujano',
            'primerAyudante',
            'segundoAyudante',
            'anestesiologo',
            'instrumentador',
            'circulante',
            'receta.detalles.producto',
            'receta.detalles.seccion',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-cirugia-anestesiologia', [
            'proforma' => $proforma,
            'cirugia' => $cirugia,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("hoja-anestesiologia-{$cirugia->numero}-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Enfermería del Descargo de Quirófano.
     */
    public function cirugiaEnfermeria(Proforma $proforma, Cirugia $cirugia): Response
    {
        if ($cirugia->proforma_id !== $proforma->id) {
            abort(404, 'La cirugía no corresponde a la proforma indicada.');
        }

        $proforma->load(['paciente.institucion', 'sucursal']);
        $cirugia->load([
            'cirujano',
            'primerAyudante',
            'segundoAyudante',
            'anestesiologo',
            'instrumentador',
            'circulante',
            'receta.detalles.producto',
            'receta.detalles.seccion',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-cirugia-enfermeria', [
            'proforma' => $proforma,
            'cirugia' => $cirugia,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("hoja-enfermeria-{$cirugia->numero}-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Solicitud de Alta Voluntaria (Alta Solicitada) en PDF.
     */
    public function altaSolicitada(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'medicos.especialidad',
            'historiaClinica',
            'servicios.servicio',
            'cirugias',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-alta-solicitada', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y'),
            'horaEmision' => now()->format('H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("alta-solicitada-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Registro de Enfermería en Emergencia en PDF.
     */
    public function registroEnfermeria(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'historiaClinica',
            'servicios.servicio',
            'cirugias',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-registro-enfermeria', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("registro-enfermeria-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Planilla de Control de Signos Vitales en PDF.
     */
    public function signosVitales(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'historiaClinica',
            'servicios.servicio',
            'cirugias',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-control-signos-vitales', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("control-signos-vitales-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Kardex de Enfermería en PDF.
     */
    public function kardexEnfermeria(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'medicos.especialidad',
            'cirugias',
            'historiaClinica',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-kardex-enfermeria', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("kardex-enfermeria-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Control de Líquidos (Balance Hídrico) en PDF.
     */
    public function controlLiquidos(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'historiaClinica',
            'servicios.servicio',
            'cirugias',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-control-liquidos', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream("control-liquidos-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Consentimiento Informado para Anestesia Raquídea en PDF.
     */
    public function consentimientoAnestesia(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'medicos.especialidad',
            'cirugias.cirujano',
            'cirugias.anestesiologo',
            'historiaClinica',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-consentimiento-anestesia', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y'),
            'horaEmision' => now()->format('H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("consentimiento-anestesia-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Consentimiento Informado para Procedimiento Quirúrgico en PDF.
     */
    public function consentimientoQuirurgico(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente.institucion',
            'sucursal',
            'medicos.especialidad',
            'cirugias.cirujano',
            'cirugias.anestesiologo',
            'historiaClinica',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-consentimiento-quirurgico', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y'),
            'horaEmision' => now()->format('H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("consentimiento-quirurgico-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Tarjeta Única de Tratamiento y Soluciones Parenterales en PDF.
     */
    public function tarjetaTratamiento(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'cirugias',
            'historiaClinica',
            'recetaActiva.detalles.producto',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-tarjeta-tratamiento', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("tarjeta-tratamiento-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Registro de Tratamiento de Enfermería (Medicamentos y Venoclisis) en PDF.
     */
    public function tratamientoEnfermeria(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'cirugias',
            'historiaClinica',
            'recetaActiva.detalles.producto',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-tratamiento-enfermeria', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("tratamiento-enfermeria-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Gráfica de Signos Vitales (Curva Térmica, Pulso y Respiración) en PDF.
     */
    public function graficaSignosVitales(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'cirugias',
            'historiaClinica',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-grafica-signos-vitales', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("grafica-signos-vitales-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Registro de Glucemias en PDF.
     */
    public function registroGlucemias(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'cirugias',
            'historiaClinica',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-registro-glucemias', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("registro-glucemias-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime el Descargo de Insumos y Medicamentos (Piso / Sala) en PDF.
     */
    public function descargoMedicamentosInsumos(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'cirugias',
            'medicos',
            'historiaClinica',
            'consumosExtras.producto',
            'consumosExtras.user',
            'servicios.servicio',
            'epicrisis',
        ]);

        $pdf = Pdf::loadView('pdf.proforma-descargo-medicamentos-insumos', [
            'proforma' => $proforma,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("descargo-medicamentos-insumos-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Receta Médica Externa en PDF.
     */
    public function recetaExterna(Proforma $proforma, RecetaExterna $recetaExterna): Response
    {
        $proforma->load(['paciente', 'sucursal']);
        $recetaExterna->load(['doctor.especialidad', 'detalles']);

        $pdf = Pdf::loadView('pdf.proforma-receta-externa', [
            'proforma' => $proforma,
            'recetaExterna' => $recetaExterna,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("receta-externa-{$recetaExterna->id}-proforma-{$proforma->id}.pdf");
    }

    /**
     * Genera e imprime la Hoja de Reporte de Enfermería en PDF.
     */
    public function reporteEnfermeriaDoc(Proforma $proforma): Response
    {
        $proforma->load([
            'paciente',
            'sucursal',
            'reportesEnfermeria.usuarioCreador',
        ]);

        $reportes = $proforma->reportesEnfermeria()->with('usuarioCreador')->orderBy('fecha_hora', 'asc')->get();

        $pdf = Pdf::loadView('pdf.proforma-reporte-enfermeria', [
            'proforma' => $proforma,
            'reportes' => $reportes,
            'fechaEmision' => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte-enfermeria-proforma-{$proforma->id}.pdf");
    }
}
