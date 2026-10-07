<?php

namespace App\Services;

use App\Models\Institucion;
use App\Models\Paciente;
use App\Models\Proforma;
use App\Models\Seccion;
use App\Models\Servicio;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReportePlanillaConvenioService
{
    /**
     * Nombres de los meses en español.
     *
     * @var array<int, string>
     */
    public const MESES = [
        1 => 'ENERO',
        2 => 'FEBRERO',
        3 => 'MARZO',
        4 => 'ABRIL',
        5 => 'MAYO',
        6 => 'JUNIO',
        7 => 'JULIO',
        8 => 'AGOSTO',
        9 => 'SEPTIEMBRE',
        10 => 'OCTUBRE',
        11 => 'NOVIEMBRE',
        12 => 'DICIEMBRE',
    ];

    /**
     * Genera la matriz consolidada de datos para la planilla de pacientes atendidos por convenio / institución.
     *
     * @return array{
     *     institucion: Institucion,
     *     mes: int,
     *     mes_nombre: string,
     *     anio: int,
     *     sucursal_id: ?int,
     *     solo_atendidos: bool,
     *     columnas_pacientes: array<int, array{
     *         index: int,
     *         proforma_id: ?int,
     *         proforma_numero: string,
     *         paciente_id: int,
     *         paciente_nombre: string,
     *         foleado: string,
     *         datos_generales: string,
     *         valores: array<string, float>,
     *         total: float,
     *         descuento: float,
     *         total_a_cancelar: float
     *     }>,
     *     filas_conceptos: array<int, array{
     *         clave: string,
     *         tipo: 'servicio'|'seccion',
     *         nombre: string,
     *         es_seccion: bool,
     *         total_fila: float
     *     }>,
     *     gran_total: float,
     *     gran_descuento: float,
     *     gran_total_a_cancelar: float
     * }
     */
    public function generar(
        int $institucionId,
        int $mes,
        int $anio,
        ?int $sucursalId = null,
        bool $soloAtendidos = true
    ): array {
        $institucion = Institucion::findOrFail($institucionId);
        $mesNombre = self::MESES[$mes] ?? 'MES';

        // 1. Obtener todas las secciones activas para los insumos y medicamentos
        $secciones = Seccion::orderBy('nombre')->get();

        // 2. Obtener todos los servicios clínicos activos
        $serviciosCatalogo = Servicio::orderBy('nombre')->get();

        // 3. Consultar proformas vinculadas a pacientes de esta institución en el mes y año dados
        $proformasQuery = Proforma::query()
            ->whereHas('paciente', function ($q) use ($institucionId) {
                $q->where('institucion_id', $institucionId);
            })
            ->where(function ($q) use ($mes, $anio) {
                $q->where(function ($sub) use ($mes, $anio) {
                    $sub->whereNotNull('fecha_ingreso')
                        ->whereMonth('fecha_ingreso', $mes)
                        ->whereYear('fecha_ingreso', $anio);
                })->orWhere(function ($sub) use ($mes, $anio) {
                    $sub->whereNull('fecha_ingreso')
                        ->whereMonth('created_at', $mes)
                        ->whereYear('created_at', $anio);
                });
            })
            ->with([
                'paciente',
                'servicios.servicio',
                'consumosExtras.producto',
                'movimientosInventario.producto',
                'movimientosInventario.seccionOrigen',
            ]);

        if ($sucursalId) {
            $proformasQuery->where('sucursal_id', $sucursalId);
        }

        $proformas = $proformasQuery->orderBy('fecha_ingreso', 'asc')->orderBy('id', 'asc')->get();

        // 4. Si no es "solo atendidos", obtener también pacientes de la institución sin proforma en este periodo
        $pacientesSinProforma = collect();
        if (! $soloAtendidos) {
            $pacientesConProformaIds = $proformas->pluck('paciente_id')->unique()->toArray();
            $pacientesSinProforma = Paciente::where('institucion_id', $institucionId)
                ->whereNotIn('id', $pacientesConProformaIds)
                ->orderBy('apellido_paterno')
                ->orderBy('nombres')
                ->get();
        }

        // 5. Construir lista de filas (conceptos): Servicios clínicos e Insumos por sección
        $filasConceptos = [];

        // Filas para servicios de catálogo
        foreach ($serviciosCatalogo as $serv) {
            $filasConceptos[] = [
                'clave' => 'serv_'.$serv->id,
                'tipo' => 'servicio',
                'nombre' => mb_strtoupper($serv->nombre),
                'es_seccion' => false,
                'total_fila' => 0.00,
            ];
        }

        // Filas para insumos y medicamentos por sección
        foreach ($secciones as $sec) {
            $nombreSeccion = mb_strtoupper($sec->nombre);
            $nombreFila = str_contains($nombreSeccion, 'FARMACIA')
                ? 'INSUMOS Y MEDICAMENTOS FCIA.'
                : 'INSUMOS Y MEDICAMENTOS DE '.$nombreSeccion;

            $filasConceptos[] = [
                'clave' => 'sec_'.$sec->id,
                'tipo' => 'seccion',
                'nombre' => $nombreFila,
                'es_seccion' => true,
                'total_fila' => 0.00,
            ];
        }

        // 6. Construir las columnas de pacientes / atenciones
        $columnasPacientes = [];
        $colIndex = 1;

        // Procesar proformas atendidas
        foreach ($proformas as $proforma) {
            $paciente = $proforma->paciente;
            if (! $paciente) {
                continue;
            }

            // Fechas de atención
            $datosGenerales = '-';
            if ($proforma->fecha_ingreso) {
                if ($proforma->fecha_salida && $proforma->fecha_salida->format('Y-m-d') !== $proforma->fecha_ingreso->format('Y-m-d')) {
                    $datosGenerales = $proforma->fecha_ingreso->format('d/m/Y').' A '.$proforma->fecha_salida->format('d/m/Y');
                } else {
                    $datosGenerales = $proforma->fecha_ingreso->format('d/m/Y');
                }
            }

            // Calcular valores para cada concepto
            $valores = [];
            $totalColumna = 0.00;

            // A) Servicios
            $serviciosAgrupados = $proforma->servicios->groupBy('servicio_id');
            foreach ($serviciosCatalogo as $serv) {
                $clave = 'serv_'.$serv->id;
                $montoServ = 0.00;
                if ($serviciosAgrupados->has($serv->id)) {
                    $montoServ = (float) $serviciosAgrupados->get($serv->id)->sum('costo_final');
                }
                $valores[$clave] = round($montoServ, 2);
                $totalColumna += $valores[$clave];
            }

            // B) Insumos y Medicamentos por sección
            foreach ($secciones as $sec) {
                $clave = 'sec_'.$sec->id;
                $montoSeccion = 0.00;

                // 1. Consumos extras cargados a esta sección
                foreach ($proforma->consumosExtras as $consumo) {
                    $esDeEstaSeccion = false;
                    $obs = (string) $consumo->observaciones;

                    if (stripos($obs, '['.$sec->nombre.']') !== false) {
                        $esDeEstaSeccion = true;
                    } elseif ($sec->es_almacen_principal || stripos($sec->nombre, 'farmacia') !== false) {
                        // Si no tiene corchete de sección, atribuir a Farmacia Central / almacén principal
                        $tieneOtraSeccion = false;
                        foreach ($secciones as $otraSec) {
                            if ($otraSec->id !== $sec->id && stripos($obs, '['.$otraSec->nombre.']') !== false) {
                                $tieneOtraSeccion = true;
                                break;
                            }
                        }
                        if (! $tieneOtraSeccion) {
                            $esDeEstaSeccion = true;
                        }
                    }

                    if ($esDeEstaSeccion) {
                        $montoSeccion += (float) ($consumo->cantidad * ($consumo->precio_unitario ?? 0));
                    }
                }

                // 2. Salidas directas de farmacia (recetas despachadas)
                foreach ($proforma->movimientosInventario as $mov) {
                    if (in_array($mov->tipo_movimiento, ['Salida Receta', 'Salida Farmacia'], true)) {
                        $origenId = $mov->seccion_origen_id;
                        $esDeEstaSeccion = false;

                        if ($origenId === $sec->id) {
                            $esDeEstaSeccion = true;
                        } elseif (! $origenId && ($sec->es_almacen_principal || stripos($sec->nombre, 'farmacia') !== false)) {
                            $esDeEstaSeccion = true;
                        }

                        if ($esDeEstaSeccion && $mov->producto) {
                            $precioVenta = (float) ($mov->producto->ultimo_precio_venta ?? 0);
                            $montoSeccion += (float) ($mov->cantidad * $precioVenta);
                        }
                    }
                }

                $valores[$clave] = round($montoSeccion, 2);
                $totalColumna += $valores[$clave];
            }

            $columnasPacientes[] = [
                'index' => $colIndex++,
                'proforma_id' => $proforma->id,
                'proforma_numero' => (string) $proforma->id,
                'paciente_id' => $paciente->id,
                'paciente_nombre' => mb_strtoupper($paciente->nombre_completo),
                'foleado' => '',
                'datos_generales' => $datosGenerales,
                'valores' => $valores,
                'total' => round($totalColumna, 2),
                'descuento' => 0.00,
                'total_a_cancelar' => round($totalColumna, 2),
            ];
        }

        // Agregar pacientes sin proforma si correspondía
        foreach ($pacientesSinProforma as $pac) {
            $valores = [];
            foreach ($filasConceptos as $fila) {
                $valores[$fila['clave']] = 0.00;
            }

            $columnasPacientes[] = [
                'index' => $colIndex++,
                'proforma_id' => null,
                'proforma_numero' => '-',
                'paciente_id' => $pac->id,
                'paciente_nombre' => mb_strtoupper($pac->nombre_completo),
                'foleado' => '',
                'datos_generales' => '-',
                'valores' => $valores,
                'total' => 0.00,
                'descuento' => 0.00,
                'total_a_cancelar' => 0.00,
            ];
        }

        // 7. Calcular totales por fila y gran total
        $granTotal = 0.00;
        $granDescuento = 0.00;

        foreach ($filasConceptos as &$fila) {
            $clave = $fila['clave'];
            $totalFila = 0.00;
            foreach ($columnasPacientes as $col) {
                $totalFila += (float) ($col['valores'][$clave] ?? 0.00);
            }
            $fila['total_fila'] = round($totalFila, 2);
            $granTotal += $fila['total_fila'];
        }
        unset($fila);

        foreach ($columnasPacientes as $col) {
            $granDescuento += (float) ($col['descuento'] ?? 0.00);
        }

        return [
            'institucion' => $institucion,
            'mes' => $mes,
            'mes_nombre' => $mesNombre,
            'anio' => $anio,
            'sucursal_id' => $sucursalId,
            'solo_atendidos' => $soloAtendidos,
            'columnas_pacientes' => $columnasPacientes,
            'filas_conceptos' => $filasConceptos,
            'gran_total' => round($granTotal, 2),
            'gran_descuento' => round($granDescuento, 2),
            'gran_total_a_cancelar' => round($granTotal - $granDescuento, 2),
        ];
    }

    /**
     * Construye y retorna la hoja de cálculo de PhpSpreadsheet formateada exactamente según el requerimiento.
     */
    public function exportarExcel(array $datos): Spreadsheet
    {
        $institucion = $datos['institucion'];
        $mesNombre = $datos['mes_nombre'];
        $anio = $datos['anio'];
        $columnas = $datos['columnas_pacientes'];
        $filasConceptos = $datos['filas_conceptos'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Planilla_{$mesNombre}", 0, 31));
        $sheet->setShowGridLines(true);

        // Estilos base
        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // Fila 2: TÍTULO PRINCIPAL
        $tituloTexto = "PLANILLA PACIENTES ATENDIDOS {$institucion->nombre} MES DE {$mesNombre} {$anio}";
        $sheet->setCellValue('A2', mb_strtoupper($tituloTexto));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);

        // Determinación de rangos de columnas
        $totalPacientes = count($columnas);
        $colInicioPacientesIndex = 3; // Columna 'C'
        $colFinPacientesIndex = $totalPacientes > 0 ? (2 + $totalPacientes) : 3;
        $colTotalesIndex = $totalPacientes > 0 ? ($colFinPacientesIndex + 1) : 4;

        $letraInicioPac = Coordinate::stringFromColumnIndex($colInicioPacientesIndex);
        $letraFinPac = Coordinate::stringFromColumnIndex($colFinPacientesIndex);
        $letraTotales = Coordinate::stringFromColumnIndex($colTotalesIndex);

        // Fila 4: NÚMEROS CORRELATIVOS (1, 2, 3...)
        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.'4', $colData['index']);
            $sheet->getStyle($letraCol.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.'4')->getFont()->setBold(true);
        }

        // Fila 5: NO. PROF. (Verde suave #A9D08E)
        $sheet->setCellValue('B5', 'NO. PROF.');
        $sheet->getStyle('B5')->getFont()->setBold(true);
        $colorVerdeCabecera = 'A9D08E';

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.'5', $colData['proforma_numero']);
            $sheet->getStyle($letraCol.'5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.'5')->getFont()->setBold(true);
            $sheet->getStyle($letraCol.'5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorVerdeCabecera);
        }

        // Fila 6: NOMBRE PACIENTE
        $sheet->setCellValue('B6', 'NOMBRE PACIENTE');
        $sheet->getStyle('B6')->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.'6', $colData['paciente_nombre']);
            $sheet->getStyle($letraCol.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $sheet->getStyle($letraCol.'6')->getFont()->setBold(true)->setSize(9);
        }

        // Encabezado de la columna TOTALES
        $sheet->setCellValue($letraTotales.'6', 'TOTALES');
        $sheet->getStyle($letraTotales.'6')->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Fila 7: NUMERO DE FOLEADO (Amarillo #FFFF00)
        $sheet->setCellValue('B7', 'NUMERO DE FOLEADO');
        $sheet->getStyle('B7')->getFont()->setBold(true);
        $colorAmarilloFoleado = 'FFFF00';

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.'7', $colData['foleado']);
            $sheet->getStyle($letraCol.'7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.'7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorAmarilloFoleado);
        }

        // Fila 8: DATOS GENERALES
        $sheet->setCellValue('A8', 'DETALLE DE SERVICIOS');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->setCellValue('B8', 'DATOS GENERALES');
        $sheet->getStyle('B8')->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.'8', $colData['datos_generales']);
            $sheet->getStyle($letraCol.'8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
            $sheet->getStyle($letraCol.'8')->getFont()->setSize(8);
        }

        // Filas 9 en adelante: DETALLE DE SERVICIOS E INSUMOS POR SECCIÓN
        $currentRow = 9;
        $colorVerdeInsumos = 'E2EFDA'; // Verde suave del screenshot

        foreach ($filasConceptos as $concepto) {
            $clave = $concepto['clave'];
            $nombre = $concepto['nombre'];
            $esSeccion = $concepto['es_seccion'];

            $sheet->setCellValue('B'.$currentRow, $nombre);
            $sheet->getStyle('B'.$currentRow)->getFont()->setSize(9);

            if ($esSeccion) {
                $sheet->getStyle('B'.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorVerdeInsumos);
                $sheet->getStyle('B'.$currentRow)->getFont()->setBold(true);
            }

            // Celdas de cada paciente
            foreach ($columnas as $colData) {
                $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
                $monto = (float) ($colData['valores'][$clave] ?? 0.00);

                $sheet->setCellValue($letraCol.$currentRow, $monto);
                $sheet->getStyle($letraCol.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($letraCol.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                if ($esSeccion) {
                    $sheet->getStyle($letraCol.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorVerdeInsumos);
                }
            }

            // Celda de TOTALES de la fila con fórmula
            if ($totalPacientes > 0) {
                $sheet->setCellValue($letraTotales.$currentRow, "=SUM({$letraInicioPac}{$currentRow}:{$letraFinPac}{$currentRow})");
            } else {
                $sheet->setCellValue($letraTotales.$currentRow, 0.00);
            }
            $sheet->getStyle($letraTotales.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraTotales.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($letraTotales.$currentRow)->getFont()->setBold(true);

            if ($esSeccion) {
                $sheet->getStyle($letraTotales.$currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorVerdeInsumos);
            }

            $currentRow++;
        }

        $ultimoConceptoRow = $currentRow - 1;

        // Fila TOTAL
        $totalRow = $currentRow;
        $sheet->setCellValue('A'.$totalRow, 'TOTAL');
        $sheet->setCellValue('B'.$totalRow, 'TOTAL');
        $sheet->getStyle('A'.$totalRow)->getFont()->setBold(true);
        $sheet->getStyle('B'.$totalRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.$totalRow, "=SUM({$letraCol}9:{$letraCol}{$ultimoConceptoRow})");
            $sheet->getStyle($letraCol.$totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$totalRow)->getFont()->setBold(true);
            $sheet->getStyle($letraCol.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        if ($totalPacientes > 0) {
            $sheet->setCellValue($letraTotales.$totalRow, "=SUM({$letraTotales}9:{$letraTotales}{$ultimoConceptoRow})");
        } else {
            $sheet->setCellValue($letraTotales.$totalRow, 0.00);
        }
        $sheet->getStyle($letraTotales.$totalRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$totalRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$totalRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Fila 33: Espacio en blanco
        $currentRow++;

        // Fila DESCUENTOS
        $descRow = $totalRow + 2;
        $sheet->setCellValue('A'.$descRow, 'DESCUENTOS');
        $sheet->setCellValue('B'.$descRow, 'DESCUENTOS');
        $sheet->getStyle('A'.$descRow)->getFont()->setBold(true);
        $sheet->getStyle('B'.$descRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.$descRow, 0.00);
            $sheet->getStyle($letraCol.$descRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$descRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        if ($totalPacientes > 0) {
            $sheet->setCellValue($letraTotales.$descRow, "=SUM({$letraInicioPac}{$descRow}:{$letraFinPac}{$descRow})");
        } else {
            $sheet->setCellValue($letraTotales.$descRow, 0.00);
        }
        $sheet->getStyle($letraTotales.$descRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$descRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Fila TOTAL A CANCELAR
        $cancelarRow = $descRow + 1;
        $sheet->setCellValue('A'.$cancelarRow, 'TOTAL A CANCELAR');
        $sheet->setCellValue('B'.$cancelarRow, 'TOTAL A CANCELAR');
        $sheet->getStyle('A'.$cancelarRow)->getFont()->setBold(true);
        $sheet->getStyle('B'.$cancelarRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->setCellValue($letraCol.$cancelarRow, "={$letraCol}{$totalRow}-{$letraCol}{$descRow}");
            $sheet->getStyle($letraCol.$cancelarRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$cancelarRow)->getFont()->setBold(true);
            $sheet->getStyle($letraCol.$cancelarRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->setCellValue($letraTotales.$cancelarRow, "={$letraTotales}{$totalRow}-{$letraTotales}{$descRow}");
        $sheet->getStyle($letraTotales.$cancelarRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$cancelarRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$cancelarRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bordes en toda la cuadrícula (desde Columna B hasta la columna de Totales, filas 4 a cancelarRow)
        $rangoTabla = "B4:{$letraTotales}{$cancelarRow}";
        $sheet->getStyle($rangoTabla)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Bordes adicionales en filas de total
        $sheet->getStyle("A{$totalRow}:{$letraTotales}{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalRow}:{$letraTotales}{$totalRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        $sheet->getStyle("A{$cancelarRow}:{$letraTotales}{$cancelarRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$cancelarRow}:{$letraTotales}{$cancelarRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // Anchos de columna
        $sheet->getColumnDimension('A')->setWidth(14);
        $sheet->getColumnDimension('B')->setWidth(38);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(2 + $colData['index']);
            $sheet->getColumnDimension($letraCol)->setWidth(18);
        }

        $sheet->getColumnDimension($letraTotales)->setWidth(16);

        return $spreadsheet;
    }
}
