<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Control de Líquidos - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 14px 20px 14px 20px;
            size: letter landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8px;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 4px;
        }
        .header-logo {
            width: 20%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 32px;
            max-width: 130px;
            object-fit: contain;
        }
        .header-title {
            width: 60%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-hc {
            width: 20%;
            vertical-align: middle;
            text-align: right;
            font-size: 9px;
            font-weight: 700;
        }

        .patient-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #0f172a;
            margin-bottom: 6px;
        }
        .patient-table td {
            border: 1px solid #0f172a;
            padding: 2px 4px;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 7px;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }

        .balance-section {
            width: 100%;
            margin-bottom: 8px;
        }
        .balance-grid {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
        }
        .balance-grid th, .balance-grid td {
            border: 1px solid #0f172a;
            padding: 2px 3px;
            font-size: 7.5px;
        }
        .head-group {
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            font-size: 8px;
            text-transform: uppercase;
        }
        .subhead {
            background-color: #f8fafc;
            font-weight: 700;
            text-align: center;
            font-size: 7.5px;
        }
        .item-label {
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5px;
            background-color: #ffffff;
        }
        .cell-data {
            height: 14px;
            text-align: center;
        }
        .shift-bar {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #0f172a;
            border-top: none;
            background-color: #f8fafc;
        }
        .shift-bar td {
            width: 33.33%;
            border: 1px solid #0f172a;
            padding: 3px;
            text-align: center;
            font-weight: 800;
            font-size: 7.5px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $historia = $proforma->historiaClinica;
        $edad = $proforma->edad_paciente ?? ($paciente ? $paciente->edad : null);
        $edadTexto = $edad !== null ? "{$edad} años" : '---';
        $pieza = $proforma->pieza ?? $proforma->cirugias->first()?->cama ?? $proforma->epicrisis?->cama ?? '---';
        $servicioNombre = $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion ?: 'SALA DE INTERNACIÓN / ENFERMERÍA');
        $fechaIngreso = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : ($proforma->created_at ? $proforma->created_at->format('d/m/Y') : now()->format('d/m/Y'));
    @endphp

    <!-- Cabecera -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Clínica Boliviana Americana">
                @else
                    <strong style="color: #1e3a8a; font-size: 11px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>HOJA DE CONTROL DE LÍQUIDOS</h1>
            </td>
            <td class="header-hc">
                <span class="lbl">N° H.CL:</span>
                <span class="val" style="font-size: 10px;">{{ $paciente->numero_historia_clinica ?? $proforma->id }}</span>
            </td>
        </tr>
    </table>

    <!-- Ficha Paciente -->
    <table class="patient-table">
        <tr>
            <td style="width: 33%;">
                <span class="lbl">APELLIDO PATERNO:</span>
                <span class="val">{{ $paciente->apellido_paterno ?? '---' }}</span>
            </td>
            <td style="width: 33%;">
                <span class="lbl">APELLIDO MATERNO:</span>
                <span class="val">{{ $paciente->apellido_materno ?? '---' }}</span>
            </td>
            <td style="width: 34%;">
                <span class="lbl">NOMBRE:</span>
                <span class="val">{{ $paciente->nombres ?? $paciente->nombre_completo ?? '---' }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 33%;">
                <span class="lbl">FECHA DE INGRESO:</span>
                <span class="val">{{ $fechaIngreso }}</span>
            </td>
            <td style="width: 33%;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 0; width: 50%;">
                            <span class="lbl">SEXO:</span>
                            <span class="val">{{ $paciente->genero ?? '---' }}</span>
                        </td>
                        <td style="border: none; padding: 0; width: 50%;">
                            <span class="lbl">EDAD:</span>
                            <span class="val">{{ $edadTexto }}</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 34%;">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 0; width: 50%;">
                            <span class="lbl">SALA:</span>
                            <span class="val">{{ $proforma->cirugias->first()?->sala ?? $pieza }}</span>
                        </td>
                        <td style="border: none; padding: 0; width: 50%;">
                            <span class="lbl">CAMA:</span>
                            <span class="val">{{ $proforma->cirugias->first()?->cama ?? $proforma->epicrisis?->cama ?? $pieza }}</span>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                <span class="lbl">UNIDAD Y SERVICIO:</span>
                <span class="val">{{ $servicioNombre }}</span>
            </td>
        </tr>
    </table>

    <!-- Dos ciclos de balance (Día 1 y Día 2) -->
    @for ($dia = 1; $dia <= 2; $dia++)
        <div class="balance-section">
            <table class="balance-grid">
                <thead>
                    <tr>
                        <th rowspan="2" style="width: 7%;" class="head-group">FECHA</th>
                        <th colspan="4" style="width: 35%;" class="head-group">INGRESOS</th>
                        <th colspan="4" style="width: 38%;" class="head-group">EGRESOS</th>
                        <th rowspan="2" colspan="2" style="width: 20%;" class="head-group">CONTROL DE PESO Y BALANCE</th>
                    </tr>
                    <tr>
                        <th style="width: 14%;" class="subhead">CONCEPTO</th>
                        <th style="width: 7%;" class="subhead">08-14</th>
                        <th style="width: 7%;" class="subhead">14-20</th>
                        <th style="width: 7%;" class="subhead">20-08</th>

                        <th style="width: 17%;" class="subhead">CONCEPTO</th>
                        <th style="width: 7%;" class="subhead">08-14</th>
                        <th style="width: 7%;" class="subhead">14-20</th>
                        <th style="width: 7%;" class="subhead">20-08</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Fila 1 -->
                    <tr>
                        <td rowspan="8" style="text-align: center; vertical-align: middle; font-weight: 700;">
                            {{ $dia === 1 ? now()->format('d/m/y') : '' }}
                        </td>
                        <td class="item-label">SUEROS</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td class="item-label">ORINA</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td style="width: 10%; font-weight: 700; background-color: #f8fafc;">PESO INICIAL:</td>
                        <td style="width: 10%; text-align: center;">{{ $dia === 1 && $historia && $historia->peso ? $historia->peso . ' kg' : '' }}</td>
                    </tr>
                    <!-- Fila 2 -->
                    <tr>
                        <td class="item-label">PLASMA</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td class="item-label">DEPOSICIONES</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td style="font-weight: 700; background-color: #f8fafc;">PESO FINAL:</td>
                        <td style="text-align: center;"></td>
                    </tr>
                    <!-- Fila 3 -->
                    <tr>
                        <td class="item-label">SANGRE</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td class="item-label">VÓMITOS Y/O SNG</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td style="font-weight: 700; background-color: #f8fafc;">PESO:</td>
                        <td style="text-align: center;"></td>
                    </tr>
                    <!-- Fila 4 -->
                    <tr>
                        <td class="item-label">LÍQUIDO POR V.O</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td class="item-label">SANGRADO</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>

                        <td colspan="2" rowspan="5" style="vertical-align: top; background-color: #ffffff; padding: 4px;">
                            <div style="font-weight: 700; font-size: 7px; color: #475569; text-transform: uppercase;">ACUMULADO EN 24 HRS.:</div>
                            <div style="height: 38px;"></div>
                            <div style="border-top: 1px dashed #cbd5e1; padding-top: 3px; font-weight: 700; font-size: 7.5px;">
                                {{-- BALANCE TOTAL: _________________ --}}
                            </div>
                        </td>
                    </tr>
                    <!-- Fila 5 -->
                    <tr>
                        <td class="item-label" style="font-weight: 800; background-color: #f1f5f9;">TOTAL INGRESOS</td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>

                        <td class="item-label">DRENAJES</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                    </tr>
                    <!-- Fila 6 -->
                    <tr>
                        <td colspan="4" style="background-color: #fafafa;"></td>

                        <td class="item-label">P. ANOMALÍAS</td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                        <td class="cell-data"></td>
                    </tr>
                    <!-- Fila 7 -->
                    <tr>
                        <td colspan="4" style="background-color: #fafafa;"></td>

                        <td class="item-label" style="font-weight: 800; background-color: #f1f5f9;">TOTAL EGRESOS</td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>
                        <td class="cell-data" style="background-color: #f1f5f9;"></td>
                    </tr>
                    <!-- Fila 8 -->
                    <tr>
                        <td colspan="4" style="background-color: #fafafa;"></td>

                        <td class="item-label" style="font-weight: 800; background-color: #e2e8f0;">BALANCE</td>
                        <td class="cell-data" style="background-color: #e2e8f0;"></td>
                        <td class="cell-data" style="background-color: #e2e8f0;"></td>
                        <td class="cell-data" style="background-color: #e2e8f0;"></td>
                    </tr>
                </tbody>
            </table>
            <!-- Firmas por Turno -->
            <table class="shift-bar">
                <tr>
                    <td>TURNO MATUTINO</td>
                    <td>TURNO VESPERTINO</td>
                    <td>TURNO NOCTURNO  </td>
                </tr>
            </table>
        </div>
    @endfor
</body>
</html>

