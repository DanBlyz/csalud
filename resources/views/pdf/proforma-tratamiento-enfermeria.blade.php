<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro Tratamiento de Enfermería - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 50px 30px 50px 30px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
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
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 36px;
            max-width: 150px;
            object-fit: contain;
        }
        .header-title {
            width: 50%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-hc {
            width: 25%;
            vertical-align: middle;
            text-align: right;
            font-size: 11px;
            font-weight: 800;
        }

        .patient-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 5px;
        }
        .patient-box td {
            border: 1px solid #cbd5e1;
            padding: 2.5px 4px;
            vertical-align: middle;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            font-size: 11px;
            margin-right: 3px;
        }
        .val-box {
            display: inline-block;
            border: 1px solid #64748b;
            border-radius: 4px;
            padding: 1.5px 6px;
            font-weight: 700;
            color: #0f172a;
            background-color: #f8fafc;
            min-width: 55px;
        }

        .grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 6px;
        }
        .grid-table th {
            border: 1px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7.5px;
            padding: 2px 2px;
            text-align: center;
            text-transform: uppercase;
        }
        .grid-table td {
            border: 1px solid #334155;
            height: 14px;
            text-align: center;
            font-size: 11px;
            padding: 1px 2px;
        }
        .lbl-row {
            font-weight: 800;
            text-align: left !important;
            padding-left: 4px !important;
            background-color: #f8fafc;
            color: #1e293b;
        }

        .page-break {
            page-break-before: always;
        }

        .venoclisis-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 6px;
        }
        .venoclisis-table th {
            border: 1px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7.5px;
            padding: 2.5px 2px;
            text-align: center;
        }
        .venoclisis-table td {
            border: 1px solid #334155;
            height: 18px;
            text-align: center;
            font-size: 7.5px;
        }

        .bottom-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        .obs-box {
            width: 72%;
            border: 1.2px solid #0f172a;
            padding: 4px 6px;
            vertical-align: top;
        }
        .ruled-line {
            border-bottom: 1px solid #94a3b8;
            height: 15px;
            width: 100%;
        }
        .stamp-box {
            width: 28%;
            border: 1.2px solid #0f172a;
            vertical-align: bottom;
            text-align: center;
            padding: 4px 6px 6px 6px;
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
        $cama = $proforma->pieza ?? $cirugia?->cama ?? $proforma->epicrisis?->cama ?? '---';
        $sala = $cirugia?->sala ?: '---';
        $hc = $paciente?->numero_historia_clinica ?: '---';

        // Medicamentos prescritos
        $detallesReceta = $proforma->recetaActiva?->detalles ?? collect();
    @endphp

    <!-- ========================================== -->
    <!-- PÁGINA 1: REGISTRO TRATAMIENTO (ANVERSO)   -->
    <!-- ========================================== -->

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
                <h1>REGISTRO TRATAMIENTO DE ENFERMERIA</h1>
            </td>
            <td class="header-hc">
                HC. N°: <span style="text-decoration: underline; font-weight: 800;">{{ $hc }}</span>
            </td>
        </tr>
    </table>

    <!-- Ficha de Filiación del Paciente -->
    <table class="patient-box">
        <tr>
            <td style="width: 35%;">
                <span class="lbl">Apellido Paterno: {{ $paciente?->apellido_paterno ?: '---' }}</span>
            </td>
            <td style="width: 35%;">
                <span class="lbl">Apellido Materno: {{ $paciente?->apellido_materno ?: '---' }}</span>
            </td>
            <td style="width: 30%;">
                <span class="lbl">Ap. Del Esposo: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
            </td>
        </tr>
        <tr>
            <td style="width: 35%;">
                <span class="lbl">Nombre: {{ $paciente?->nombres ?: '---' }}</span>
            </td>
            <td style="width: 35%;">
                <span class="lbl">Edad: {{ $edadTexto }}</span>
                &nbsp;&nbsp;
                <span class="lbl">Sexo: {{ $paciente?->genero ?: '---' }}</span>
            </td>
            <td style="width: 30%;">
                <span class="lbl">No. de Cama: {{ $cama }}</span>
                &nbsp;&nbsp;
                <span class="lbl">Sala: {{ $sala }}</span>
            </td>
        </tr>
    </table>

    <!-- Grilla de Tratamiento (6 Bloques de Medicamentos) -->
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 22%; line-height: 1.1;">FECHAS<br>FIRMAS</th>
                <th style="width: 8%;">Hrs.</th>
                @for ($col = 1; $col <= 7; $col++)
                    <th style="width: 10%;"></th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @for ($b = 0; $b < 9; $b++)
                @php
                    $det = $detallesReceta->get($b);
                @endphp
                <tr>
                    <td class="lbl-row">
                        <strong>MEDICAMENTO</strong>
                        {{-- @if ($det)
                            <span style="font-weight: 700; color: #1e3a8a;">: {{ $det->producto?->nombre }}</span>
                        @endif --}}
                    </td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td class="lbl-row">
                        <strong>DOSIS</strong>
                        {{-- @if ($det)
                            <span>: {{ $det->indicaciones ?: "Cant: {$det->cantidad}" }}</span>
                        @endif --}}
                    </td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td class="lbl-row"><strong>VIA</strong></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- ========================================== -->
    <!-- PÁGINA 2: CONTINUACIÓN Y VENOCLISIS       -->
    <!-- ========================================== -->
    <div class="page-break"></div>

    <!-- Grilla de Tratamiento (Continuación: Bloques 7, 8, 9) -->
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width: 22%; line-height: 1.1;">FECHAS<br>FIRMAS</th>
                <th style="width: 8%;">Hrs.</th>
                @for ($col = 1; $col <= 7; $col++)
                    <th style="width: 10%;"></th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @for ($b = 6; $b < 9; $b++)
                @php
                    $det = $detallesReceta->get($b);
                @endphp
                <tr>
                    <td class="lbl-row">
                        <strong>MEDICAMENTO</strong>
                        @if ($det)
                            <span style="font-weight: 700; color: #1e3a8a;">: {{ $det->producto?->nombre }}</span>
                        @endif
                    </td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td class="lbl-row">
                        <strong>DOSIS</strong>
                        @if ($det)
                            <span>: {{ $det->indicaciones ?: "Cant: {$det->cantidad}" }}</span>
                        @endif
                    </td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td class="lbl-row"><strong>VIA</strong></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    @for ($c = 1; $c <= 7; $c++) <td></td> @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Sección Venoclisis -->
    <table class="venoclisis-table">
        <thead>
            <tr>
                <th colspan="6" style="background-color: #f1f5f9; font-size: 11px; font-weight: 800; color: #1e3a8a; padding: 4px; text-align: center; letter-spacing: 2px;">
                    VENOCLISIS
                </th>
            </tr>
            <tr>
                <th style="width: 11%;">FECHA</th>
                <th style="width: 27%;">Solución</th>
                <th style="width: 12%;">Cantidad</th>
                <th style="width: 25%;">Agregados</th>
                <th style="width: 12%;">Hrs. Inicio</th>
                <th style="width: 13%;">Nombre Enf.</th>
            </tr>
        </thead>
        <tbody>
            @for ($v = 0; $v < 7; $v++)
                <tr>
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

    <!-- Sección Inferior: Observaciones y Firma/Sello -->
    <table class="bottom-table">
        <tr>
            <td class="obs-box">
                <div style="font-weight: 800; font-size: 8px; text-transform: uppercase; margin-bottom: 2px; color: #1e293b;">
                    OBSERVACIONES
                </div>
                @for ($l = 1; $l <= 6; $l++)
                    <div class="ruled-line"></div>
                @endfor
            </td>
            <td style="width: 3%;"></td>
            <td class="stamp-box">
                <div style="height: 75px;"></div>
                <div style="border-top: 1px solid #334155; margin: 0 10px 4px 10px;"></div>
                <span style="font-weight: 800; font-size: 7.5px; text-transform: uppercase; color: #1e293b;">
                    FIRMA Y SELLO
                </span>
            </td>
        </tr>
    </table>
</body>
</html>

