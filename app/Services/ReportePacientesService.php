<?php

namespace App\Services;

use App\Models\Institucion;
use App\Models\Proforma;
use App\Models\Sucursal;
use Carbon\Carbon;

class ReportePacientesService
{
    /**
     * Genera la estructura de datos para el reporte de ingresos y salidas de pacientes.
     *
     * @return array{
     *     fecha_inicio: string,
     *     fecha_fin: string,
     *     institucion_id: ?int,
     *     institucion_nombre: string,
     *     sucursal_id: ?int,
     *     sucursal_nombre: string,
     *     tipo_filtro: string,
     *     tipo_atencion: ?string,
     *     totales: array{
     *         total_registros: int,
     *         ingresos_periodo: int,
     *         salidas_periodo: int,
     *         internados_activos: int,
     *         ambulatorios: int,
     *         hospitalarios: int
     *     },
     *     desglose_instituciones: array<string, int>,
     *     desglose_piezas: array<string, int>,
     *     items: array<int, array{
     *         proforma_id: int,
     *         paciente_id: int,
     *         paciente_nombre: string,
     *         paciente_cedula: string,
     *         paciente_edad: ?int,
     *         paciente_celular: ?string,
     *         institucion_nombre: string,
     *         tipo_atencion: string,
     *         pieza: string,
     *         fecha_ingreso: string,
     *         fecha_salida: ?string,
     *         es_alta: bool,
     *         dias_estancia: int,
     *         diagnostico: ?string,
     *         motivo_consulta: ?string,
     *         estado: string,
     *         costo_total: float,
     *         sucursal_nombre: string,
     *         medicos: string
     *     }>
     * }
     */
    public function generar(
        string $fechaInicio,
        string $fechaFin,
        ?int $institucionId = null,
        ?int $sucursalId = null,
        string $tipoFiltro = 'todos',
        ?string $tipoAtencion = null
    ): array {
        $inicio = Carbon::parse($fechaInicio)->startOfDay();
        $fin = Carbon::parse($fechaFin)->endOfDay();

        $query = Proforma::query()
            ->with(['paciente.institucion', 'sucursal', 'medicos'])
            ->where('estado', '!=', 'Anulada');

        // Filtro por Sucursal
        if ($sucursalId) {
            $query->where('sucursal_id', $sucursalId);
        }

        // Filtro por Institución:
        // -1: Solo Particulares (sin institución)
        // >0: Institución específica
        // null: Todas las instituciones
        if ($institucionId === -1) {
            $query->whereHas('paciente', fn ($q) => $q->whereNull('institucion_id'));
        } elseif ($institucionId > 0) {
            $query->whereHas('paciente', fn ($q) => $q->where('institucion_id', $institucionId));
        }

        // Filtro por Tipo de Atención
        if (! empty($tipoAtencion)) {
            $query->where('tipo_atencion', $tipoAtencion);
        }

        // Filtro cronológico según el tipo de evento
        switch ($tipoFiltro) {
            case 'ingresos':
                $query->whereBetween('fecha_ingreso', [$inicio, $fin]);
                break;

            case 'salidas':
                $query->whereNotNull('fecha_salida')
                    ->whereBetween('fecha_salida', [$inicio, $fin]);
                break;

            case 'internados':
                $query->where('tipo_atencion', 'Internacion')
                    ->whereNull('fecha_salida')
                    ->where('fecha_ingreso', '<=', $fin);
                break;

            case 'todos':
            default:
                $query->where(function ($q) use ($inicio, $fin) {
                    $q->whereBetween('fecha_ingreso', [$inicio, $fin])
                        ->orWhere(function ($qs) use ($inicio, $fin) {
                            $qs->whereNotNull('fecha_salida')
                                ->whereBetween('fecha_salida', [$inicio, $fin]);
                        })
                        ->orWhere(function ($qi) use ($inicio, $fin) {
                            $qi->where('fecha_ingreso', '<=', $fin)
                                ->where(function ($qn) use ($inicio) {
                                    $qn->whereNull('fecha_salida')
                                        ->orWhere('fecha_salida', '>=', $inicio);
                                });
                        });
                });
                break;
        }

        $proformas = $query->orderBy('fecha_ingreso', 'desc')->get();

        $items = [];
        $ingresosPeriodo = 0;
        $salidasPeriodo = 0;
        $internadosActivos = 0;
        $ambulatorios = 0;
        $hospitalarios = 0;
        $desgloseInstituciones = [];
        $desglosePiezas = [];

        foreach ($proformas as $prof) {
            $paciente = $prof->paciente;
            $institucionNombre = $paciente?->institucion?->nombre ?? 'Particular';
            $piezaTexto = $prof->pieza ? trim($prof->pieza) : ($prof->tipo_atencion === 'Ambulatoria' ? 'Ambulatorio' : 'Sin asignar');

            $fIngreso = $prof->fecha_ingreso;
            $fSalida = $prof->fecha_salida;

            // Identificar si el ingreso ocurrió dentro del rango evaluado
            $ingresoEnRango = $fIngreso && $fIngreso->between($inicio, $fin);
            if ($ingresoEnRango) {
                $ingresosPeriodo++;
            }

            // Identificar si la salida/alta ocurrió dentro del rango evaluado
            $salidaEnRango = $fSalida && $fSalida->between($inicio, $fin);
            if ($salidaEnRango) {
                $salidasPeriodo++;
            }

            // Paciente internado sin alta
            $esInternadoActivo = ($prof->tipo_atencion === 'Internacion' && ! $fSalida);
            if ($esInternadoActivo) {
                $internadosActivos++;
            }

            if ($prof->tipo_atencion === 'Internacion') {
                $hospitalarios++;
            } else {
                $ambulatorios++;
            }

            // Días de estancia
            $fechaFinEstancia = $fSalida ?? Carbon::now();
            $diasEstancia = $fIngreso ? max(1, (int) $fIngreso->diffInDays($fechaFinEstancia)) : 1;

            // Desgloses para reportería
            $desgloseInstituciones[$institucionNombre] = ($desgloseInstituciones[$institucionNombre] ?? 0) + 1;
            if ($prof->pieza) {
                $desglosePiezas[$prof->pieza] = ($desglosePiezas[$prof->pieza] ?? 0) + 1;
            }

            $medicosNombres = $prof->medicos->pluck('name')->implode(', ');

            $items[] = [
                'proforma_id' => $prof->id,
                'paciente_id' => $prof->paciente_id,
                'paciente_nombre' => $paciente?->nombre_completo ?? 'N/D',
                'paciente_cedula' => $paciente?->cedula ?? 'S/N',
                'paciente_edad' => $paciente?->fecha_nacimiento?->age,
                'paciente_celular' => $paciente?->celular,
                'institucion_nombre' => $institucionNombre,
                'tipo_atencion' => $prof->tipo_atencion,
                'pieza' => $piezaTexto,
                'fecha_ingreso' => $fIngreso ? $fIngreso->format('d/m/Y H:i') : '-',
                'fecha_salida' => $fSalida ? $fSalida->format('d/m/Y H:i') : null,
                'es_alta' => $fSalida !== null,
                'dias_estancia' => $diasEstancia,
                'diagnostico' => $prof->diagnostico,
                'motivo_consulta' => $prof->motivo_consulta,
                'estado' => $prof->estado,
                'costo_total' => (float) $prof->costo_total,
                'sucursal_nombre' => $prof->sucursal?->nombre ?? 'Principal',
                'medicos' => $medicosNombres ?: 'Sin asignar',
            ];
        }

        // Determinar etiquetas de filtros
        $nombreInstitucion = 'Todas las Instituciones (Incluye Particulares)';
        if ($institucionId === -1) {
            $nombreInstitucion = 'Solo Pacientes Particulares (Sin Seguro/Convenio)';
        } elseif ($institucionId > 0) {
            $nombreInstitucion = Institucion::find($institucionId)?->nombre ?? "Institución #{$institucionId}";
        }

        $nombreSucursal = $sucursalId ? (Sucursal::find($sucursalId)?->nombre ?? 'Sucursal Seleccionada') : 'Todas las Sucursales';

        arsort($desgloseInstituciones);
        arsort($desglosePiezas);

        return [
            'fecha_inicio' => $inicio->toDateString(),
            'fecha_fin' => $fin->toDateString(),
            'institucion_id' => $institucionId,
            'institucion_nombre' => $nombreInstitucion,
            'sucursal_id' => $sucursalId,
            'sucursal_nombre' => $nombreSucursal,
            'tipo_filtro' => $tipoFiltro,
            'tipo_atencion' => $tipoAtencion,
            'totales' => [
                'total_registros' => count($items),
                'ingresos_periodo' => $ingresosPeriodo,
                'salidas_periodo' => $salidasPeriodo,
                'internados_activos' => $internadosActivos,
                'ambulatorios' => $ambulatorios,
                'hospitalarios' => $hospitalarios,
            ],
            'desglose_instituciones' => $desgloseInstituciones,
            'desglose_piezas' => $desglosePiezas,
            'items' => $items,
        ];
    }
}
