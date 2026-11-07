<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tarjeta Única de Tratamiento - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 22px 30px 22px 30px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8px;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .slip-container {
            width: 100%;
            height: 485px;
            box-sizing: border-box;
        }
        .cut-line {
            border-top: 1px dashed #94a3b8;
            margin: 6px 0;
            text-align: center;
            position: relative;
        }
        .cut-line span {
            position: relative;
            top: -6px;
            background-color: #ffffff;
            padding: 0 8px;
            color: #64748b;
            font-size: 7px;
            font-weight: 600;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 3px;
        }
        .header-logo {
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 28px;
            max-width: 130px;
            object-fit: contain;
        }
        .header-title {
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 20%;
        }
        .header-title h1 {
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 3px;
        }
        .meta-table td {
            border: 1px solid #0f172a;
            padding: 2px 4px;
            vertical-align: middle;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 7px;
            margin-right: 2px;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
        }
        .grid-table th {
            border: 1px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7px;
            padding: 2px 2px;
            text-align: center;
            text-transform: uppercase;
        }
        .grid-table td {
            border: 1px solid #334155;
            height: 18px;
            text-align: center;
            font-size: 7.5px;
            padding: 1px 2px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $cirugia = $proforma->cirugias->first();
        $edad = $proforma->edad_paciente ?? ($paciente ? $paciente->edad : null);
        $edadTexto = $edad !== null ? "{$edad}a" : '---';
        $sala = $cirugia?->sala ?: '---';
        $cama = $proforma->pieza ?? $cirugia?->cama ?? $proforma->epicrisis?->cama ?? '---';
        $hc = $paciente?->numero_historia_clinica ?: '---';

        // Medicamentos activos prescritos
        $detallesReceta = $proforma->recetaActiva?->detalles ?? collect();
    @endphp

    <!-- PÁGINA 1: TARJETA ÚNICA DE TRATAMIENTO (ANVERSO: 2 TALONES) -->
    @for ($slip = 1; $slip <= 2; $slip++)
        <div class="slip-container">
            <!-- Encabezado -->
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
                        <h1>TARJETA ÚNICA DE TRATAMIENTO</h1>
                    </td>
                </tr>
            </table>

            <!-- Metadatos de Filiación del Paciente -->
            <table class="meta-table">
                <tr>
                    <td style="width: 42%;">
                        <span class="lbl">NOMBRE:</span>
                        <span class="val">{{ $paciente->nombre_completo ?? '---' }}</span>
                    </td>
                    <td style="width: 12%; text-align: center;">
                        <span class="lbl">EDAD:</span>
                        <span class="val">{{ $edadTexto }}</span>
                    </td>
                    <td style="width: 14%; text-align: center;">
                        <span class="lbl">SALA:</span>
                        <span class="val">{{ $sala }}</span>
                    </td>
                    <td style="width: 14%; text-align: center;">
                        <span class="lbl">CAMA:</span>
                        <span class="val">{{ $cama }}</span>
                    </td>
                    <td style="width: 18%; text-align: center;">
                        <span class="lbl">N° HC:</span>
                        <span class="val">{{ $hc }}</span>
                    </td>
                </tr>
            </table>

            <!-- Grilla de Tratamiento Farmacológico -->
            <table class="grid-table">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 9%;">FECHA</th>
                        <th rowspan="2" style="width: 7%;">HORA</th>
                        <th rowspan="2" style="width: 32%;">MEDICAMENTOS</th>
                        <th rowspan="2" style="width: 9%;">DOSIS</th>
                        <th rowspan="2" style="width: 6%;">VIA</th>
                        <th colspan="3" style="width: 15%;">FECHA/FIRMA</th>
                        <th rowspan="2" style="width: 5%;">D/C</th>
                        <th rowspan="2" style="width: 17%;">OBSERVACIONES</th>
                    </tr>
                    <tr>
                        <th style="width: 5%;">TM</th>
                        <th style="width: 5%;">TT</th>
                        <th style="width: 5%;">TN</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalFilasTratamiento = 17;
                    @endphp
                    @for ($i = 0; $i < $totalFilasTratamiento; $i++)
                        @php
                            $det = $detallesReceta->get($i);
                        @endphp
                        <tr>
                            {{-- <td>
                                {{ $det ? now()->format('d/m/Y') : '' }}
                            </td>
                            <td></td>
                            <td style="text-align: left; padding-left: 4px; font-weight: {{ $det ? '700' : 'normal' }};">
                                {{ $det ? ($det->producto?->nombre ?? 'Medicamento') : '' }}
                            </td>
                            <td>
                                {{ $det ? ($det->indicaciones ?: "Cant: {$det->cantidad}") : '' }}
                            </td> --}}
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        @if ($slip === 1)
            <div class="cut-line">
                <span>✂ CORTE AQUÍ (TALÓN DUPLICADO DE TRATAMIENTO)</span>
            </div>
        @endif
    @endfor

    <!-- PÁGINA 2: SOLUCIONES PARENTERALES (REVERSO: 2 TALONES) -->
    <div class="page-break"></div>

    @for ($slip = 1; $slip <= 2; $slip++)
        <div class="slip-container">
            <table class="grid-table" style="margin-top: 4px;">
                <thead>
                    <tr>
                        <th colspan="8" style="background-color: #f1f5f9; font-size: 10px; font-weight: 800; color: #1e3a8a; padding: 4px; text-align: center; letter-spacing: 1px;">
                            SOLUCIONES PARENTERALES
                        </th>
                    </tr>
                    <tr>
                        <th style="width: 11%;">FECHA</th>
                        <th style="width: 8%;">HORA</th>
                        <th style="width: 35%;">SOLUCION</th>
                        <th style="width: 5%;">I</th>
                        <th style="width: 5%;">C</th>
                        <th style="width: 5%;">DC</th>
                        <th style="width: 16%;">SELLO Y FIRMA</th>
                        <th style="width: 15%;">OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < 18; $i++)
                        <tr>
                            <td style="height: 20px;"></td>
                            <td></td>
                            <td style="text-align: left; padding-left: 4px;"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        @if ($slip === 1)
            <div class="cut-line">
                <span>✂ CORTE AQUÍ (TALÓN DUPLICADO DE SOLUCIONES PARENTERALES)</span>
            </div>
        @endif
    @endfor
</body>
</html>

