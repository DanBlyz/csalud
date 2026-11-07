<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gráfica de Signos Vitales - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 40px 16px 40px 16px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .header-logo {
            width: 30%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 30px;
            max-width: 140px;
            object-fit: contain;
        }
        .header-title {
            width: 70%;
            vertical-align: middle;
            text-align: center;
            padding-right: 25%;
        }
        .header-title h1 {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }

        .patient-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 3px;
        }
        .patient-box td {
            border: 1px solid #0f172a;
            padding: 2px 4px;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            font-size: 11px;
            margin-right: 3px;
        }
        .val {
            font-weight: 700;
            color: #0f172a;
            font-size: 11px;
        }

        .chart-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
        }
        .chart-table th, .chart-table td {
            border: 0.8px solid #64748b;
            padding: 1px 0px;
            text-align: center;
            font-size: 11px;
        }
        .chart-table th {
            font-weight: 800;
        }

        .th-header {
            background-color: #ffffff;
            color: #1e293b;
            font-size: 7.5px;
            font-weight: 800;
            height: 14px;
        }
        .th-shift {
            background-color: #609cb8;
            color: #ffffff;
            font-size: 7px;
            font-weight: 800;
            height: 12px;
        }

        .lateral-temp {
            background-color: #d25b43;
            color: #ffffff;
            font-weight: 900;
            font-size: 7.5px;
            letter-spacing: 2px;
            width: 48px;
            writing-mode: vertical-rl;
            text-orientation: upright;
            vertical-align: middle;
            border-right: 1.2px solid #0f172a !important;
        }
        .lateral-pulso {
            background-color: #3b8ea5;
            color: #ffffff;
            font-weight: 900;
            font-size: 7.5px;
            letter-spacing: 2px;
            width: 25px;
            writing-mode: vertical-rl;
            text-orientation: upright;
            vertical-align: middle;
            border-right: 1.2px solid #0f172a !important;
        }
        .lateral-resp {
            background-color: #8c8672;
            color: #ffffff;
            font-weight: 900;
            font-size: 6.5px;
            letter-spacing: 1px;
            width: 16px;
            writing-mode: vertical-rl;
            text-orientation: upright;
            vertical-align: middle;
            border-right: 1.2px solid #0f172a !important;
        }

        .scale-col {
            width: 22px;
            font-weight: 800;
            font-size: 7px;
            background-color: #f8fafc;
            border-right: 1.2px solid #0f172a !important;
        }
        .chart-cell {
            height: 11px;
            border: 0.6px dotted #94a3b8;
        }
        .chart-cell-solid {
            height: 11px;
            border: 0.8px solid #64748b;
            width: 15px;
        }

        .bottom-label {
            font-weight: 800;
            font-size: 6.5px;
            text-align: left !important;
            padding-left: 4px !important;
            background-color: #f8fafc;
            color: #0f172a;
            height: 13px;
            border-right: 1.2px solid #0f172a !important;
        }
        .bottom-cell {
            height: 13px;
            border: 0.8px solid #64748b;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $cirugia = $proforma->cirugias->first();
        $historia = $proforma->historiaClinica;
        $fechaIngreso = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : ($proforma->created_at ? $proforma->created_at->format('d/m/Y') : '---');
        $cama = $proforma->pieza ?? $cirugia?->cama ?? $proforma->epicrisis?->cama ?? '---';
        $sala = $cirugia?->sala ?: ($proforma->servicios->first()?->servicio?->nombre ?? 'HOSPITALIZACIÓN');
        $hc = $paciente?->numero_historia_clinica ?: '---';
    @endphp

    <!-- Cabecera Institucional -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Clínica Boliviana Americana">
                @else
                    <strong style="color: #1e3a8a; font-size: 10px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>SIGNOS VITALES</h1>
            </td>
        </tr>
    </table>

    <!-- Ficha de Filiación del Paciente -->
    <table class="patient-box">
        <tr>
            <td style="width: 26%;">
                <span class="lbl">Apellido Paterno:</span>
                <span class="val">{{ $paciente?->apellido_paterno ?: '---' }}</span>
            </td>
            <td style="width: 26%;">
                <span class="lbl">Apellido Materno:</span>
                <span class="val">{{ $paciente?->apellido_materno ?: '---' }}</span>
            </td>
            <td style="width: 26%;">
                <span class="lbl">Nombre(s):</span>
                <span class="val">{{ $paciente?->nombres ?: '---' }}</span>
            </td>
            <td style="width: 22%;">
                <span class="lbl">No. Historia Clínica:</span>
                <span class="val">{{ $hc }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 26%;">
                <span class="lbl">Fecha de Ingreso:</span>
                <span class="val">{{ $fechaIngreso }}</span>
            </td>
            <td style="width: 26%;">
                <span class="lbl">No. de Cama:</span>
                <span class="val">{{ $cama }}</span>
            </td>
            <td colspan="2">
                <span class="lbl">Departamento / Sala:</span>
                <span class="val">{{ $sala }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla Principal de la Gráfica de Signos Vitales -->
    <table class="chart-table">
        <thead>
            <!-- Fila de Fechas (7 Días) -->
            <tr>
                <th colspan="2" class="th-header" style="border-right: 1.2px solid #0f172a; width: 72px;">FECHA</th>
                @for ($d = 1; $d <= 7; $d++)
                    <th colspan="3" class="th-header" style="border-right: 1px solid #0f172a;"></th>
                @endfor
            </tr>
            <!-- Fila de Frecuencia / Turnos (M, T, N) -->
            <tr>
                <th colspan="2" class="th-shift" style="border-right: 1.2px solid #0f172a; background-color: #4784a3; width: 72px">FRECUENCIA</th>
                @for ($d = 1; $d <= 7; $d++)
                    <th class="th-shift" style="width: 4.2%;">M</th>
                    <th class="th-shift" style="width: 4.2%;">T</th>
                    <th class="th-shift" style="width: 4.2%; border-right: 1px solid #0f172a;">N</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <!-- ================= TEMPERATURA ================= -->
            @php
                $tempScales = [
                    ['val' => '42', 'sub' => true],
                    ['val' => '41', 'sub' => true],
                    ['val' => '40', 'sub' => true],
                    ['val' => '39', 'sub' => true],
                    ['val' => '38', 'sub' => true],
                    ['val' => '37', 'sub' => true],
                    ['val' => '36', 'sub' => true],
                    ['val' => '35', 'sub' => false],
                ];
                // Total rows: 8 main + 7 sub = 15 rows
                $totalTempRows = 15;
            @endphp
            @for ($t = 0; $t < count($tempScales); $t++)
                <!-- Fila Principal con Número de Grado -->
                <tr>
                    @if ($t === 0)
                        <td rowspan="{{ $totalTempRows }}" class="lateral-temp">T <br> E <br> M <br> P <br> E <br> R <br> A <br> T <br> U <br> R <br> A</td>
                    @endif
                    <td class="scale-col">{{ $tempScales[$t]['val'] }}</td>
                    @for ($c = 1; $c <= 21; $c++)
                        <td class="chart-cell-solid" style="{{ $c % 3 === 0 ? 'border-right: 1px solid #0f172a;' : '' }}"></td>
                    @endfor
                </tr>
                <!-- Sub-Fila Pautada Intermedia -->
                @if ($tempScales[$t]['sub'])
                    <tr>
                        <td class="scale-col" style="height: 10px; border-bottom: 0.6px dotted #cbd5e1;"></td>
                        @for ($c = 1; $c <= 21; $c++)
                            <td class="chart-cell" style="{{ $c % 3 === 0 ? 'border-right: 1px solid #0f172a;' : '' }}"></td>
                        @endfor
                    </tr>
                @endif
            @endfor

            <!-- ================= PULSO ================= -->
            @php
                $pulsoScales = ['160', '150', '140', '130', '120', '110', '100', '90', '80', '70', '60'];
            @endphp
            @for ($p = 0; $p < count($pulsoScales); $p++)
                <tr>
                    @if ($p === 0)
                        <td rowspan="{{ count($pulsoScales) }}" class="lateral-pulso">P <br> U <br> L <br> S <br> O</td>
                    @endif
                    <td class="scale-col">{{ $pulsoScales[$p] }}</td>
                    @for ($c = 1; $c <= 21; $c++)
                        <td class="chart-cell-solid" style="{{ $c % 3 === 0 ? 'border-right: 1px solid #0f172a;' : '' }}"></td>
                    @endfor
                </tr>
            @endfor

            <!-- ================= RESPIRACIÓN ================= -->
            @php
                $respScales = ['70', '60', '50', '40', '30', '20', '10'];
            @endphp
            @for ($r = 0; $r < count($respScales); $r++)
                <tr>
                    @if ($r === 0)
                        <td rowspan="{{ count($respScales) }}" class="lateral-resp">R <br> E <br> S <br> P <br> I <br> R <br> A <br> C <br> I <br> Ó <br> N</td>
                    @endif
                    <td class="scale-col">{{ $respScales[$r] }}</td>
                    @for ($c = 1; $c <= 21; $c++)
                        <td class="chart-cell-solid" style="{{ $c % 3 === 0 ? 'border-right: 1px solid #0f172a;' : '' }}"></td>
                    @endfor
                </tr>
            @endfor

            <!-- ================= FILAS DE CONTROL DIARIO ================= -->
            @php
                $bottomMetrics = [
                    'PRESIÓN ARTERIAL',
                    'HECES',
                    'ORINA',
                    'DIETA',
                    'PESO',
                    'TALLA',
                    'SAT O2'
                ];
            @endphp
            @foreach ($bottomMetrics as $metric)
                <tr>
                    <td colspan="2" class="bottom-label">{{ $metric }}</td>
                    @for ($c = 1; $c <= 21; $c++)
                        <td class="bottom-cell" style="{{ $c % 3 === 0 ? 'border-right: 1px solid #0f172a;' : '' }}"></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

