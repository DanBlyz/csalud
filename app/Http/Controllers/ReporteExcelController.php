<?php

namespace App\Http\Controllers;

use App\Models\Institucion;
use App\Services\ReportePlanillaConvenioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteExcelController extends Controller
{
    /**
     * Descarga la planilla consolidada de pacientes atendidos por convenio / institución en formato Excel (.xlsx).
     */
    public function planillaConvenios(Request $request, ReportePlanillaConvenioService $service): StreamedResponse
    {
        $request->validate([
            'institucion_id' => ['required', 'exists:instituciones,id'],
            'mes' => ['required', 'integer', 'min:1', 'max:12'],
            'anio' => ['required', 'integer', 'min:2000', 'max:2100'],
            'sucursal_id' => ['nullable', 'exists:sucursales,id'],
            'solo_atendidos' => ['nullable', 'boolean'],
        ]);

        $institucionId = (int) $request->query('institucion_id');
        $mes = (int) $request->query('mes', Carbon::now()->month);
        $anio = (int) $request->query('anio', Carbon::now()->year);
        $sucursalId = $request->filled('sucursal_id') ? (int) $request->query('sucursal_id') : null;
        $soloAtendidos = $request->boolean('solo_atendidos', true);

        $datos = $service->generar(
            institucionId: $institucionId,
            mes: $mes,
            anio: $anio,
            sucursalId: $sucursalId,
            soloAtendidos: $soloAtendidos
        );

        $spreadsheet = $service->exportarExcel($datos);
        $institucion = Institucion::find($institucionId);
        $slugInstitucion = $institucion ? str_replace(' ', '_', strtolower($institucion->nombre)) : 'convenio';
        $mesNombre = ReportePlanillaConvenioService::MESES[$mes] ?? (string) $mes;
        $filename = "Planilla_Pacientes_{$slugInstitucion}_{$mesNombre}_{$anio}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
