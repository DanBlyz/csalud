<?php

namespace App\Services;

use App\Models\Caja;
use App\Models\Pago;
use App\Models\Servicio;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ReporteFlujoCajaService
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
     * Categorías estándar de egresos de caja.
     *
     * @var array<string, string>
     */
    public const EGRESOS_ESTANDAR = [
        'egr_compras' => 'COMPRA MEDICAMENTOS E INSUMOS',
        'egr_honorarios' => 'HONORARIOS DOCTORES',
        'egr_pendiente_doctores' => 'PENDIENTE DE PAGO DOCTORES',
        'egr_deudores' => 'DEUDORES / VECINO',
        'egr_servicios_basicos' => 'SERVICIOS BASICOS',
        'egr_refrigerio' => 'REFRIGERIO',
        'egr_escritorio' => 'MAT. ESCRITORIO',
        'egr_laboratorios' => 'LABORATORIOS',
        'egr_movilidad' => 'MOVILIDAD',
        'egr_otros' => 'OTROS EGRESOS',
    ];

    /**
     * Categorías estándar de ingresos extra.
     *
     * @var array<string, string>
     */
    public const INGRESOS_EXTRA_ESTANDAR = [
        'ing_alquileres' => 'ALQUILERES',
        'ing_serv_basicos' => 'SERV. BASICOS',
        'ing_laboratorios' => 'LABORATORIOS EXTERNOS',
        'ing_intereses' => 'INTERESES',
        'ing_otros' => 'OTROS INGRESOS',
    ];

    /**
     * Genera la matriz de datos para el reporte mensual de flujo de caja e ingresos/egresos.
     *
     * @return array{
     *     mes: int,
     *     mes_nombre: string,
     *     anio: int,
     *     sucursal_id: ?int,
     *     columnas_cajas: array<int, array{
     *         index: int,
     *         caja_id: int,
     *         fecha: string,
     *         nro_reporte: string,
     *         valores_ingresos: array<string, float>,
     *         valores_egresos: array<string, float>,
     *         total_ingresos: float,
     *         total_egresos: float,
     *         saldo_neto: float
     *     }>,
     *     filas_ingresos: array<int, array{
     *         clave: string,
     *         nombre: string,
     *         tipo: string,
     *         total_fila: float
     *     }>,
     *     filas_egresos: array<int, array{
     *         clave: string,
     *         nombre: string,
     *         tipo: string,
     *         total_fila: float
     *     }>,
     *     gran_total_ingresos: float,
     *     gran_total_egresos: float,
     *     gran_saldo_neto: float
     * }
     */
    public function generar(int $mes, int $anio, ?int $sucursalId = null): array
    {
        $mesNombre = self::MESES[$mes] ?? 'MES';

        // 1. Obtener todas las sesiones de Caja en el mes y año
        $cajasQuery = Caja::query()
            ->where(function ($q) use ($mes, $anio) {
                $q->where(function ($sub) use ($mes, $anio) {
                    $sub->whereNotNull('fecha_apertura')
                        ->whereMonth('fecha_apertura', $mes)
                        ->whereYear('fecha_apertura', $anio);
                })->orWhere(function ($sub) use ($mes, $anio) {
                    $sub->whereNull('fecha_apertura')
                        ->whereMonth('created_at', $mes)
                        ->whereYear('created_at', $anio);
                });
            })
            ->with([
                'pagos.proforma.servicios.servicio',
                'pagos.proforma.consumosExtras',
                'pagos.proforma.movimientosInventario.producto',
            ]);

        if ($sucursalId) {
            $cajasQuery->where('sucursal_id', $sucursalId);
        }

        $cajas = $cajasQuery->orderBy('fecha_apertura', 'asc')->orderBy('id', 'asc')->get();

        // 2. Catálogo de servicios clínicos para las filas de ingresos
        $serviciosCatalogo = Servicio::orderBy('nombre')->get();

        // 3. Estructurar las filas de conceptos de INGRESOS
        $filasIngresos = [];
        // A) Fila PROFORMAS (Medicamentos e Insumos dispensados)
        $filasIngresos[] = [
            'clave' => 'ing_proformas_meds',
            'nombre' => 'PROFORMAS (MEDICAMENTOS E INSUMOS)',
            'tipo' => 'proformas',
            'total_fila' => 0.00,
        ];

        // B) Servicios del catálogo
        foreach ($serviciosCatalogo as $serv) {
            $filasIngresos[] = [
                'clave' => 'ing_serv_'.$serv->id,
                'nombre' => mb_strtoupper($serv->nombre),
                'tipo' => 'servicio',
                'total_fila' => 0.00,
            ];
        }

        // C) Ingresos extra estándar
        foreach (self::INGRESOS_EXTRA_ESTANDAR as $clave => $nombre) {
            $filasIngresos[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo' => 'extra',
                'total_fila' => 0.00,
            ];
        }

        // 4. Estructurar las filas de conceptos de EGRESOS
        $filasEgresos = [];
        foreach (self::EGRESOS_ESTANDAR as $clave => $nombre) {
            $filasEgresos[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo' => 'egreso',
                'total_fila' => 0.00,
            ];
        }

        // 5. Procesar cada caja / día como una columna
        $columnasCajas = [];
        $colIdx = 1;

        foreach ($cajas as $caja) {
            $fechaCol = $caja->fecha_apertura
                ? $caja->fecha_apertura->format('d/m/Y')
                : $caja->created_at->format('d/m/Y');

            $valoresIngresos = [];
            foreach ($filasIngresos as $f) {
                $valoresIngresos[$f['clave']] = 0.00;
            }

            $valoresEgresos = [];
            foreach ($filasEgresos as $f) {
                $valoresEgresos[$f['clave']] = 0.00;
            }

            $totalIngresosCaja = 0.00;
            $totalEgresosCaja = 0.00;

            // Procesar pagos asentados en esta caja
            foreach ($caja->pagos as $pago) {
                $monto = (float) $pago->monto;

                if ($pago->isIngreso()) {
                    $totalIngresosCaja += $monto;

                    if ($pago->tipo_movimiento === 'Ingreso Proforma' && $pago->proforma) {
                        $proforma = $pago->proforma;
                        $costoTotalProf = max(0.01, (float) $proforma->costo_total);

                        // Calcular costo de medicamentos e insumos en la proforma
                        $costoMedsDespacho = (float) $proforma->totalDespachosFarmacia();
                        $costoConsumosExtra = (float) $proforma->consumosExtras()
                            ->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')
                            ->value('total');
                        $costoTotalInsumos = $costoMedsDespacho + $costoConsumosExtra;

                        // Proporción del pago cobrado
                        $ratio = min(1.0, $monto / $costoTotalProf);

                        // Asignar medicamentos a 'ing_proformas_meds'
                        $montoMeds = round($costoTotalInsumos * $ratio, 2);
                        $valoresIngresos['ing_proformas_meds'] += $montoMeds;

                        // Asignar cada servicio clínico
                        $serviciosProf = $proforma->servicios;
                        foreach ($serviciosProf as $ps) {
                            $servClave = 'ing_serv_'.$ps->servicio_id;
                            if (isset($valoresIngresos[$servClave])) {
                                $montoServ = round(((float) $ps->costo_final) * $ratio, 2);
                                $valoresIngresos[$servClave] += $montoServ;
                            }
                        }
                    } else {
                        // Ingreso Extra: clasificar según categoría o concepto
                        $claveAsignada = $this->clasificarIngresoExtra($pago->categoria, $pago->concepto);
                        $valoresIngresos[$claveAsignada] += $monto;
                    }
                } elseif ($pago->isEgreso()) {
                    $totalEgresosCaja += $monto;
                    $claveEgreso = $this->clasificarEgreso($pago->categoria, $pago->concepto);
                    $valoresEgresos[$claveEgreso] += $monto;
                }
            }

            $saldoNetoCaja = round($totalIngresosCaja - $totalEgresosCaja, 2);

            $columnasCajas[] = [
                'index' => $colIdx++,
                'caja_id' => $caja->id,
                'fecha' => $fechaCol,
                'nro_reporte' => (string) $caja->id,
                'valores_ingresos' => $valoresIngresos,
                'valores_egresos' => $valoresEgresos,
                'total_ingresos' => round($totalIngresosCaja, 2),
                'total_egresos' => round($totalEgresosCaja, 2),
                'saldo_neto' => $saldoNetoCaja,
            ];
        }

        // 6. Calcular sumatorias de filas y grandes totales
        $granTotalIngresos = 0.00;
        foreach ($filasIngresos as &$fila) {
            $clave = $fila['clave'];
            $sumFila = 0.00;
            foreach ($columnasCajas as $col) {
                $sumFila += (float) ($col['valores_ingresos'][$clave] ?? 0.00);
            }
            $fila['total_fila'] = round($sumFila, 2);
            $granTotalIngresos += $fila['total_fila'];
        }
        unset($fila);

        $granTotalEgresos = 0.00;
        foreach ($filasEgresos as &$fila) {
            $clave = $fila['clave'];
            $sumFila = 0.00;
            foreach ($columnasCajas as $col) {
                $sumFila += (float) ($col['valores_egresos'][$clave] ?? 0.00);
            }
            $fila['total_fila'] = round($sumFila, 2);
            $granTotalEgresos += $fila['total_fila'];
        }
        unset($fila);

        $granSaldoNeto = round($granTotalIngresos - $granTotalEgresos, 2);

        return [
            'mes' => $mes,
            'mes_nombre' => $mesNombre,
            'anio' => $anio,
            'sucursal_id' => $sucursalId,
            'columnas_cajas' => $columnasCajas,
            'filas_ingresos' => $filasIngresos,
            'filas_egresos' => $filasEgresos,
            'gran_total_ingresos' => round($granTotalIngresos, 2),
            'gran_total_egresos' => round($granTotalEgresos, 2),
            'gran_saldo_neto' => $granSaldoNeto,
        ];
    }

    /**
     * Clasifica un ingreso extra en las categorías estándar.
     */
    private function clasificarIngresoExtra(?string $categoria, ?string $concepto): string
    {
        $texto = mb_strtolower(($categoria ?? '').' '.($concepto ?? ''));

        if (str_contains($texto, 'alquiler')) {
            return 'ing_alquileres';
        }
        if (str_contains($texto, 'basico') || str_contains($texto, 'básico') || str_contains($texto, 'luz') || str_contains($texto, 'agua')) {
            return 'ing_serv_basicos';
        }
        if (str_contains($texto, 'laboratorio')) {
            return 'ing_laboratorios';
        }
        if (str_contains($texto, 'interes') || str_contains($texto, 'interés')) {
            return 'ing_intereses';
        }

        return 'ing_otros';
    }

    /**
     * Clasifica un egreso de caja en las categorías estándar.
     */
    private function clasificarEgreso(?string $categoria, ?string $concepto): string
    {
        $texto = mb_strtolower(($categoria ?? '').' '.($concepto ?? ''));

        if (str_contains($texto, 'compra') || str_contains($texto, 'medicament') || str_contains($texto, 'insumo') || str_contains($texto, 'farmacia')) {
            return 'egr_compras';
        }
        if (str_contains($texto, 'honorario') || str_contains($texto, 'doctor') || str_contains($texto, 'médico') || str_contains($texto, 'medico')) {
            return 'egr_honorarios';
        }
        if (str_contains($texto, 'basico') || str_contains($texto, 'básico') || str_contains($texto, 'luz') || str_contains($texto, 'agua') || str_contains($texto, 'internet') || str_contains($texto, 'telefono')) {
            return 'egr_servicios_basicos';
        }
        if (str_contains($texto, 'refrigerio') || str_contains($texto, 'comida') || str_contains($texto, 'alimento') || str_contains($texto, 'desayuno')) {
            return 'egr_refrigerio';
        }
        if (str_contains($texto, 'escritorio') || str_contains($texto, 'papel') || str_contains($texto, 'impres') || str_contains($texto, 'librer')) {
            return 'egr_escritorio';
        }
        if (str_contains($texto, 'laboratorio')) {
            return 'egr_laboratorios';
        }
        if (str_contains($texto, 'movilidad') || str_contains($texto, 'transporte') || str_contains($texto, 'taxi') || str_contains($texto, 'gasolina')) {
            return 'egr_movilidad';
        }

        if (str_contains($texto, 'pendiente') || str_contains($texto, 'dalton') || str_contains($texto, 'calcina')) {
            return 'egr_pendiente_doctores';
        }
        if (str_contains($texto, 'deudor') || str_contains($texto, 'vecino')) {
            return 'egr_deudores';
        }

        return 'egr_otros';
    }

    /**
     * Construye y retorna la hoja de cálculo de PhpSpreadsheet con la estructura exacta solicitada.
     */
    public function exportarExcel(array $datos): Spreadsheet
    {
        $mesNombre = $datos['mes_nombre'];
        $anio = $datos['anio'];
        $columnas = $datos['columnas_cajas'];
        $filasIngresos = $datos['filas_ingresos'];
        $filasEgresos = $datos['filas_egresos'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Flujo_{$mesNombre}", 0, 31));
        $sheet->setShowGridLines(true);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        $totalCajas = count($columnas);
        $colInicioIndex = 2; // Columna 'B'
        $colFinIndex = $totalCajas > 0 ? (1 + $totalCajas) : 2;
        $colTotalesIndex = $totalCajas > 0 ? ($colFinIndex + 1) : 3;

        $letraInicio = Coordinate::stringFromColumnIndex($colInicioIndex);
        $letraFin = Coordinate::stringFromColumnIndex($colFinIndex);
        $letraTotales = Coordinate::stringFromColumnIndex($colTotalesIndex);

        // =====================================================================
        // TABLA 1: INGRESOS
        // =====================================================================
        $sheet->setCellValue('A4', 'INGRESOS');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(11);

        // Fila 5: TIPO DE INGRESOS y FECHAS
        $sheet->setCellValue('A5', 'TIPO DE INGRESOS');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.'5', $colData['fecha']);
            $sheet->getStyle($letraCol.'5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.'5')->getFont()->setBold(true);
        }

        $sheet->setCellValue($letraTotales.'5', 'TOTALES');
        $sheet->getStyle($letraTotales.'5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($letraTotales.'5')->getFont()->setBold(true);

        // Fila 6: Nro.Reporte Caja (Fondo amarillo #FFE599)
        $sheet->setCellValue('A6', 'Nro.Reporte Caja');
        $sheet->getStyle('A6')->getFont()->setBold(true);

        $colorAmarilloReporte = 'FFE599';
        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.'6', $colData['nro_reporte']);
            $sheet->getStyle($letraCol.'6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.'6')->getFont()->setBold(true);
            $sheet->getStyle($letraCol.'6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorAmarilloReporte);
        }

        // Filas 7 en adelante: Conceptos de Ingresos
        $currentRow = 7;
        foreach ($filasIngresos as $ing) {
            $clave = $ing['clave'];
            $nombre = $ing['nombre'];

            $sheet->setCellValue('A'.$currentRow, $nombre);
            $sheet->getStyle('A'.$currentRow)->getFont()->setSize(9);

            foreach ($columnas as $colData) {
                $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
                $val = (float) ($colData['valores_ingresos'][$clave] ?? 0.00);

                $sheet->setCellValue($letraCol.$currentRow, $val);
                $sheet->getStyle($letraCol.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($letraCol.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Columna TOTALES
            if ($totalCajas > 0) {
                $sheet->setCellValue($letraTotales.$currentRow, "=SUM({$letraInicio}{$currentRow}:{$letraFin}{$currentRow})");
            } else {
                $sheet->setCellValue($letraTotales.$currentRow, 0.00);
            }
            $sheet->getStyle($letraTotales.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraTotales.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($letraTotales.$currentRow)->getFont()->setBold(true);

            $currentRow++;
        }

        $ultimoIngresoRow = $currentRow - 1;

        // Fila TOTALES INGRESOS
        $totalIngresosRow = $currentRow;
        $sheet->setCellValue('A'.$totalIngresosRow, 'TOTALES INGRESOS');
        $sheet->getStyle('A'.$totalIngresosRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.$totalIngresosRow, "=SUM({$letraCol}7:{$letraCol}{$ultimoIngresoRow})");
            $sheet->getStyle($letraCol.$totalIngresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$totalIngresosRow)->getFont()->setBold(true);
            $sheet->getStyle($letraCol.$totalIngresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        if ($totalCajas > 0) {
            $sheet->setCellValue($letraTotales.$totalIngresosRow, "=SUM({$letraTotales}7:{$letraTotales}{$ultimoIngresoRow})");
        } else {
            $sheet->setCellValue($letraTotales.$totalIngresosRow, 0.00);
        }
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bordes de la tabla de ingresos
        $sheet->getStyle("A5:{$letraTotales}{$totalIngresosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalIngresosRow}:{$letraTotales}{$totalIngresosRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalIngresosRow}:{$letraTotales}{$totalIngresosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // =====================================================================
        // TABLA 2: EGRESOS
        // =====================================================================
        $egresosStartRow = $totalIngresosRow + 3; // Dejar 2 filas de espacio
        $sheet->setCellValue('A'.$egresosStartRow, 'EGRESOS');
        $sheet->getStyle('A'.$egresosStartRow)->getFont()->setBold(true)->setSize(11);

        $headerEgresosRow = $egresosStartRow + 1;
        $sheet->setCellValue('A'.$headerEgresosRow, 'TIPO DE EGRESOS');
        $sheet->getStyle('A'.$headerEgresosRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.$headerEgresosRow, $colData['fecha']);
            $sheet->getStyle($letraCol.$headerEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($letraCol.$headerEgresosRow)->getFont()->setBold(true);
        }

        $sheet->setCellValue($letraTotales.$headerEgresosRow, 'TOTALES');
        $sheet->getStyle($letraTotales.$headerEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($letraTotales.$headerEgresosRow)->getFont()->setBold(true);

        // Fila de reporte en egresos (opcional vacía o con id de reporte)
        $subHeaderEgresosRow = $headerEgresosRow + 1;
        $sheet->setCellValue('A'.$subHeaderEgresosRow, '');
        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.$subHeaderEgresosRow, '');
        }

        // Filas de conceptos de Egresos
        $currentEgrRow = $subHeaderEgresosRow + 1;
        $primerConceptoEgrRow = $currentEgrRow;

        foreach ($filasEgresos as $egr) {
            $clave = $egr['clave'];
            $nombre = $egr['nombre'];

            $sheet->setCellValue('A'.$currentEgrRow, $nombre);
            $sheet->getStyle('A'.$currentEgrRow)->getFont()->setSize(9);

            foreach ($columnas as $colData) {
                $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
                $val = (float) ($colData['valores_egresos'][$clave] ?? 0.00);

                $sheet->setCellValue($letraCol.$currentEgrRow, $val);
                $sheet->getStyle($letraCol.$currentEgrRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($letraCol.$currentEgrRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Columna TOTALES
            if ($totalCajas > 0) {
                $sheet->setCellValue($letraTotales.$currentEgrRow, "=SUM({$letraInicio}{$currentEgrRow}:{$letraFin}{$currentEgrRow})");
            } else {
                $sheet->setCellValue($letraTotales.$currentEgrRow, 0.00);
            }
            $sheet->getStyle($letraTotales.$currentEgrRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraTotales.$currentEgrRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($letraTotales.$currentEgrRow)->getFont()->setBold(true);

            $currentEgrRow++;
        }

        $ultimoEgresoRow = $currentEgrRow - 1;

        // Fila TOTALES EGRESOS
        $totalEgresosRow = $currentEgrRow;
        $sheet->setCellValue('A'.$totalEgresosRow, 'TOTALES EGRESOS');
        $sheet->getStyle('A'.$totalEgresosRow)->getFont()->setBold(true);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.$totalEgresosRow, "=SUM({$letraCol}{$primerConceptoEgrRow}:{$letraCol}{$ultimoEgresoRow})");
            $sheet->getStyle($letraCol.$totalEgresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$totalEgresosRow)->getFont()->setBold(true);
            $sheet->getStyle($letraCol.$totalEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        if ($totalCajas > 0) {
            $sheet->setCellValue($letraTotales.$totalEgresosRow, "=SUM({$letraTotales}{$primerConceptoEgrRow}:{$letraTotales}{$ultimoEgresoRow})");
        } else {
            $sheet->setCellValue($letraTotales.$totalEgresosRow, 0.00);
        }
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bordes de la tabla de egresos
        $sheet->getStyle("A{$headerEgresosRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalEgresosRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalEgresosRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // =====================================================================
        // FILA ESPECIAL: SALDOS (INGRESOS - EGRESOS)
        // =====================================================================
        $saldosRow = $totalEgresosRow + 2;
        $sheet->setCellValue('A'.$saldosRow, 'SALDOS');
        $sheet->getStyle('A'.$saldosRow)->getFont()->setBold(true);

        $colorVerdeSaldos = 'D9E1F2'; // Azul claro elegante
        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorVerdeSaldos);

        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->setCellValue($letraCol.$saldosRow, "={$letraCol}{$totalIngresosRow}-{$letraCol}{$totalEgresosRow}");
            $sheet->getStyle($letraCol.$saldosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraCol.$saldosRow)->getFont()->setBold(true);
            $sheet->getStyle($letraCol.$saldosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->setCellValue($letraTotales.$saldosRow, "={$letraTotales}{$totalIngresosRow}-{$letraTotales}{$totalEgresosRow}");
        $sheet->getStyle($letraTotales.$saldosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$saldosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$saldosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // Anchos de columna
        $sheet->getColumnDimension('A')->setWidth(38);
        foreach ($columnas as $colData) {
            $letraCol = Coordinate::stringFromColumnIndex(1 + $colData['index']);
            $sheet->getColumnDimension($letraCol)->setWidth(14);
        }
        $sheet->getColumnDimension($letraTotales)->setWidth(16);

        return $spreadsheet;
    }

    /**
     * Genera la matriz de datos para el Resumen Anual de Ingresos y Gastos Gestión [Año].
     *
     * @return array{
     *     anio: int,
     *     sucursal_id: ?int,
     *     meses: array<int, array{
     *         mes: int,
     *         mes_nombre: string,
     *         valores_ingresos: array<string, float>,
     *         valores_egresos: array<string, float>,
     *         total_ingresos: float,
     *         total_egresos: float,
     *         saldo_neto: float
     *     }>,
     *     filas_ingresos: array<int, array{
     *         clave: string,
     *         nombre: string,
     *         tipo: string,
     *         total_fila: float
     *     }>,
     *     filas_egresos: array<int, array{
     *         clave: string,
     *         nombre: string,
     *         tipo: string,
     *         total_fila: float
     *     }>,
     *     gran_total_ingresos: float,
     *     gran_total_egresos: float,
     *     gran_saldo_neto: float
     * }
     */
    public function generarAnual(int $anio, ?int $sucursalId = null): array
    {
        // 1. Obtener todas las sesiones de Caja en el año especificado
        $cajasQuery = Caja::query()
            ->where(function ($q) use ($anio) {
                $q->where(function ($sub) use ($anio) {
                    $sub->whereNotNull('fecha_apertura')
                        ->whereYear('fecha_apertura', $anio);
                })->orWhere(function ($sub) use ($anio) {
                    $sub->whereNull('fecha_apertura')
                        ->whereYear('created_at', $anio);
                });
            })
            ->with([
                'pagos.proforma.servicios.servicio',
                'pagos.proforma.consumosExtras',
                'pagos.proforma.movimientosInventario.producto',
            ]);

        if ($sucursalId) {
            $cajasQuery->where('sucursal_id', $sucursalId);
        }

        $cajas = $cajasQuery->orderBy('fecha_apertura', 'asc')->orderBy('id', 'asc')->get();

        // 2. Catálogo de servicios clínicos
        $serviciosCatalogo = Servicio::orderBy('nombre')->get();

        // 3. Estructurar filas de conceptos de INGRESOS
        $filasIngresos = [];
        $filasIngresos[] = [
            'clave' => 'ing_proformas_meds',
            'nombre' => 'PROFORMAS (MEDICAMENTOS E INSUMOS)',
            'tipo' => 'proformas',
            'total_fila' => 0.00,
        ];

        foreach ($serviciosCatalogo as $serv) {
            $filasIngresos[] = [
                'clave' => 'ing_serv_'.$serv->id,
                'nombre' => mb_strtoupper($serv->nombre),
                'tipo' => 'servicio',
                'total_fila' => 0.00,
            ];
        }

        foreach (self::INGRESOS_EXTRA_ESTANDAR as $clave => $nombre) {
            $filasIngresos[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo' => 'extra',
                'total_fila' => 0.00,
            ];
        }

        // 4. Estructurar filas de conceptos de EGRESOS
        $filasEgresos = [];
        foreach (self::EGRESOS_ESTANDAR as $clave => $nombre) {
            $filasEgresos[] = [
                'clave' => $clave,
                'nombre' => $nombre,
                'tipo' => 'egreso',
                'total_fila' => 0.00,
            ];
        }

        // 5. Inicializar columnas de los 12 meses (1 a 12)
        $mesesData = [];
        for ($m = 1; $m <= 12; $m++) {
            $valoresIng = [];
            foreach ($filasIngresos as $f) {
                $valoresIng[$f['clave']] = 0.00;
            }
            $valoresEgr = [];
            foreach ($filasEgresos as $f) {
                $valoresEgr[$f['clave']] = 0.00;
            }

            $mesesData[$m] = [
                'mes' => $m,
                'mes_nombre' => self::MESES[$m] ?? "MES {$m}",
                'valores_ingresos' => $valoresIng,
                'valores_egresos' => $valoresEgr,
                'total_ingresos' => 0.00,
                'total_egresos' => 0.00,
                'saldo_neto' => 0.00,
            ];
        }

        // 6. Procesar cada caja y acumular en el mes correspondiente
        foreach ($cajas as $caja) {
            $mesCaja = (int) ($caja->fecha_apertura ? $caja->fecha_apertura->month : $caja->created_at->month);
            if (! isset($mesesData[$mesCaja])) {
                continue;
            }

            foreach ($caja->pagos as $pago) {
                $monto = (float) $pago->monto;

                if ($pago->isIngreso()) {
                    $mesesData[$mesCaja]['total_ingresos'] += $monto;

                    if ($pago->tipo_movimiento === 'Ingreso Proforma' && $pago->proforma) {
                        $proforma = $pago->proforma;
                        $costoTotalProf = max(0.01, (float) $proforma->costo_total);

                        $costoMedsDespacho = (float) $proforma->totalDespachosFarmacia();
                        $costoConsumosExtra = (float) $proforma->consumosExtras()
                            ->selectRaw('SUM(cantidad * COALESCE(precio_unitario, 0)) as total')
                            ->value('total');
                        $costoTotalInsumos = $costoMedsDespacho + $costoConsumosExtra;

                        $ratio = min(1.0, $monto / $costoTotalProf);

                        $montoMeds = round($costoTotalInsumos * $ratio, 2);
                        $mesesData[$mesCaja]['valores_ingresos']['ing_proformas_meds'] += $montoMeds;

                        $serviciosProf = $proforma->servicios;
                        foreach ($serviciosProf as $ps) {
                            $servClave = 'ing_serv_'.$ps->servicio_id;
                            if (isset($mesesData[$mesCaja]['valores_ingresos'][$servClave])) {
                                $montoServ = round(((float) $ps->costo_final) * $ratio, 2);
                                $mesesData[$mesCaja]['valores_ingresos'][$servClave] += $montoServ;
                            }
                        }
                    } else {
                        $claveAsignada = $this->clasificarIngresoExtra($pago->categoria, $pago->concepto);
                        $mesesData[$mesCaja]['valores_ingresos'][$claveAsignada] += $monto;
                    }
                } elseif ($pago->isEgreso()) {
                    $mesesData[$mesCaja]['total_egresos'] += $monto;
                    $claveEgreso = $this->clasificarEgreso($pago->categoria, $pago->concepto);
                    $mesesData[$mesCaja]['valores_egresos'][$claveEgreso] += $monto;
                }
            }
        }

        // Calcular saldo neto por mes
        foreach ($mesesData as $m => &$mInfo) {
            $mInfo['total_ingresos'] = round($mInfo['total_ingresos'], 2);
            $mInfo['total_egresos'] = round($mInfo['total_egresos'], 2);
            $mInfo['saldo_neto'] = round($mInfo['total_ingresos'] - $mInfo['total_egresos'], 2);
        }
        unset($mInfo);

        // 7. Calcular sumatorias de filas y grandes totales
        $granTotalIngresos = 0.00;
        foreach ($filasIngresos as &$fila) {
            $clave = $fila['clave'];
            $sumFila = 0.00;
            foreach ($mesesData as $mInfo) {
                $sumFila += (float) ($mInfo['valores_ingresos'][$clave] ?? 0.00);
            }
            $fila['total_fila'] = round($sumFila, 2);
            $granTotalIngresos += $fila['total_fila'];
        }
        unset($fila);

        $granTotalEgresos = 0.00;
        foreach ($filasEgresos as &$fila) {
            $clave = $fila['clave'];
            $sumFila = 0.00;
            foreach ($mesesData as $mInfo) {
                $sumFila += (float) ($mInfo['valores_egresos'][$clave] ?? 0.00);
            }
            $fila['total_fila'] = round($sumFila, 2);
            $granTotalEgresos += $fila['total_fila'];
        }
        unset($fila);

        $granSaldoNeto = round($granTotalIngresos - $granTotalEgresos, 2);

        return [
            'anio' => $anio,
            'sucursal_id' => $sucursalId,
            'meses' => array_values($mesesData),
            'filas_ingresos' => $filasIngresos,
            'filas_egresos' => $filasEgresos,
            'gran_total_ingresos' => round($granTotalIngresos, 2),
            'gran_total_egresos' => round($granTotalEgresos, 2),
            'gran_saldo_neto' => $granSaldoNeto,
        ];
    }

    /**
     * Construye y retorna la hoja de cálculo de PhpSpreadsheet para el Resumen Anual de Ingresos y Gastos.
     */
    public function exportarExcelAnual(array $datos): Spreadsheet
    {
        $anio = $datos['anio'];
        $meses = $datos['meses'];
        $filasIngresos = $datos['filas_ingresos'];
        $filasEgresos = $datos['filas_egresos'];

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Resumen_Anual_{$anio}", 0, 31));
        $sheet->setShowGridLines(true);

        $spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(10);

        // Columnas fijas:
        // Columna 1: A (Conceptos)
        // Columna 2 a 13: B a M (Meses 1 a 12: ENERO a DICIEMBRE)
        // Columna 14: N (TOTALES ANUAL)
        $letraInicio = 'B';
        $letraFin = 'M';
        $letraTotales = 'N';

        // Fila 2: TÍTULO PRINCIPAL (RESUMEN INGRESOS Y GASTOS GESTION 2026)
        $sheet->mergeCells("A2:{$letraTotales}2");
        $sheet->setCellValue('A2', "RESUMEN INGRESOS Y GASTOS GESTION {$anio}");
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // =====================================================================
        // TABLA 1: INGRESOS
        // =====================================================================
        // Fila 4: Encabezados de Ingresos
        $sheet->setCellValue('A4', 'TIPO DE INGRESOS');
        $sheet->getStyle('A4')->getFont()->setBold(true);

        foreach ($meses as $idx => $mInfo) {
            $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
            $sheet->setCellValue($colLetter.'4', $mInfo['mes_nombre']);
            $sheet->getStyle($colLetter.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter.'4')->getFont()->setBold(true);
        }

        $sheet->setCellValue($letraTotales.'4', 'TOTALES ANUAL');
        $sheet->getStyle($letraTotales.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($letraTotales.'4')->getFont()->setBold(true);

        // Filas 5 en adelante: Conceptos de Ingresos
        $currentRow = 5;
        foreach ($filasIngresos as $ing) {
            $clave = $ing['clave'];
            $nombre = $ing['nombre'];

            $sheet->setCellValue('A'.$currentRow, $nombre);
            $sheet->getStyle('A'.$currentRow)->getFont()->setSize(9);

            foreach ($meses as $idx => $mInfo) {
                $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
                $val = (float) ($mInfo['valores_ingresos'][$clave] ?? 0.00);

                $sheet->setCellValue($colLetter.$currentRow, $val);
                $sheet->getStyle($colLetter.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($colLetter.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Columna TOTALES ANUAL (Fórmula horizontal)
            $sheet->setCellValue($letraTotales.$currentRow, "=SUM({$letraInicio}{$currentRow}:{$letraFin}{$currentRow})");
            $sheet->getStyle($letraTotales.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraTotales.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($letraTotales.$currentRow)->getFont()->setBold(true);

            $currentRow++;
        }

        $ultimoIngresoRow = $currentRow - 1;

        // Fila TOTALES INGRESOS
        $totalIngresosRow = $currentRow;
        $sheet->setCellValue('A'.$totalIngresosRow, 'TOTALES INGRESOS');
        $sheet->getStyle('A'.$totalIngresosRow)->getFont()->setBold(true);

        foreach ($meses as $idx => $mInfo) {
            $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
            $sheet->setCellValue($colLetter.$totalIngresosRow, "=SUM({$colLetter}5:{$colLetter}{$ultimoIngresoRow})");
            $sheet->getStyle($colLetter.$totalIngresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($colLetter.$totalIngresosRow)->getFont()->setBold(true);
            $sheet->getStyle($colLetter.$totalIngresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->setCellValue($letraTotales.$totalIngresosRow, "=SUM({$letraTotales}5:{$letraTotales}{$ultimoIngresoRow})");
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$totalIngresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bordes de la tabla de ingresos
        $sheet->getStyle("A4:{$letraTotales}{$totalIngresosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalIngresosRow}:{$letraTotales}{$totalIngresosRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalIngresosRow}:{$letraTotales}{$totalIngresosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // =====================================================================
        // TABLA 2: EGRESOS
        // =====================================================================
        $egresosStartRow = $totalIngresosRow + 3; // 2 filas de separación
        $sheet->setCellValue('A'.$egresosStartRow, 'TIPO DE EGRESOS');
        $sheet->getStyle('A'.$egresosStartRow)->getFont()->setBold(true);

        foreach ($meses as $idx => $mInfo) {
            $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
            $sheet->setCellValue($colLetter.$egresosStartRow, $mInfo['mes_nombre']);
            $sheet->getStyle($colLetter.$egresosStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($colLetter.$egresosStartRow)->getFont()->setBold(true);
        }

        $sheet->setCellValue($letraTotales.$egresosStartRow, 'TOTALES ANUAL');
        $sheet->getStyle($letraTotales.$egresosStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($letraTotales.$egresosStartRow)->getFont()->setBold(true);

        // Filas de conceptos de egresos
        $currentRow = $egresosStartRow + 1;
        $primerEgresoRow = $currentRow;
        foreach ($filasEgresos as $egr) {
            $clave = $egr['clave'];
            $nombre = $egr['nombre'];

            $sheet->setCellValue('A'.$currentRow, $nombre);
            $sheet->getStyle('A'.$currentRow)->getFont()->setSize(9);

            foreach ($meses as $idx => $mInfo) {
                $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
                $val = (float) ($mInfo['valores_egresos'][$clave] ?? 0.00);

                $sheet->setCellValue($colLetter.$currentRow, $val);
                $sheet->getStyle($colLetter.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle($colLetter.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Columna TOTALES ANUAL (Fórmula horizontal)
            $sheet->setCellValue($letraTotales.$currentRow, "=SUM({$letraInicio}{$currentRow}:{$letraFin}{$currentRow})");
            $sheet->getStyle($letraTotales.$currentRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($letraTotales.$currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle($letraTotales.$currentRow)->getFont()->setBold(true);

            $currentRow++;
        }

        $ultimoEgresoRow = $currentRow - 1;

        // Fila TOTALES EGRESOS
        $totalEgresosRow = $currentRow;
        $sheet->setCellValue('A'.$totalEgresosRow, 'TOTALES EGRESOS');
        $sheet->getStyle('A'.$totalEgresosRow)->getFont()->setBold(true);

        foreach ($meses as $idx => $mInfo) {
            $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
            $sheet->setCellValue($colLetter.$totalEgresosRow, "=SUM({$colLetter}{$primerEgresoRow}:{$colLetter}{$ultimoEgresoRow})");
            $sheet->getStyle($colLetter.$totalEgresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($colLetter.$totalEgresosRow)->getFont()->setBold(true);
            $sheet->getStyle($colLetter.$totalEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->setCellValue($letraTotales.$totalEgresosRow, "=SUM({$letraTotales}{$primerEgresoRow}:{$letraTotales}{$ultimoEgresoRow})");
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$totalEgresosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Bordes de la tabla de egresos
        $sheet->getStyle("A{$egresosStartRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalEgresosRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalEgresosRow}:{$letraTotales}{$totalEgresosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);

        // =====================================================================
        // FILA ESPECIAL: SALDO EN CAJA
        // =====================================================================
        $saldosRow = $totalEgresosRow + 2;
        $sheet->setCellValue('A'.$saldosRow, 'SALDO EN CAJA');
        $sheet->getStyle('A'.$saldosRow)->getFont()->setBold(true);

        $colorAzulSaldos = 'D9E1F2';
        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF'.$colorAzulSaldos);

        foreach ($meses as $idx => $mInfo) {
            $colLetter = Coordinate::stringFromColumnIndex(2 + $idx);
            $sheet->setCellValue($colLetter.$saldosRow, "={$colLetter}{$totalIngresosRow}-{$colLetter}{$totalEgresosRow}");
            $sheet->getStyle($colLetter.$saldosRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle($colLetter.$saldosRow)->getFont()->setBold(true);
            $sheet->getStyle($colLetter.$saldosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        $sheet->setCellValue($letraTotales.$saldosRow, "={$letraTotales}{$totalIngresosRow}-{$letraTotales}{$totalEgresosRow}");
        $sheet->getStyle($letraTotales.$saldosRow)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle($letraTotales.$saldosRow)->getFont()->setBold(true);
        $sheet->getStyle($letraTotales.$saldosRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$saldosRow}:{$letraTotales}{$saldosRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // Anchos de columna
        $sheet->getColumnDimension('A')->setWidth(38);
        for ($c = 2; $c <= 13; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setWidth(14);
        }
        $sheet->getColumnDimension($letraTotales)->setWidth(16);

        return $spreadsheet;
    }
}
