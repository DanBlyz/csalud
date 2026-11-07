<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Signos Vitales - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 20px 28px 20px 28px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8.5px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-logo {
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 38px;
            max-width: 150px;
            object-fit: contain;
        }
        .header-title {
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 20%;
        }
        .header-title h1 {
            font-size: 15px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .patient-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 8px;
        }
        .patient-box td {
            border: 1px solid #334155;
            padding: 2.5px 5px;
            font-size: 8.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 8px;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }

        .vital-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
        }
        .vital-table th {
            border: 1.2px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 8.5px;
            padding: 4px 2px;
            text-align: center;
            text-transform: uppercase;
        }
        .vital-table td {
            border: 1px solid #334155;
            height: 19px;
            text-align: center;
            font-size: 8.5px;
            padding: 1px 2px;
        }
        .title-row th {
            background-color: #ffffff;
            font-size: 11px;
            letter-spacing: 1px;
            padding: 4px;
            border-bottom: 1.5px solid #0f172a;
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
        $fechaRegistro = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : ($proforma->created_at ? $proforma->created_at->format('d/m/Y') : now()->format('d/m/Y'));
        $horaRegistro = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('H:i') : ($proforma->created_at ? $proforma->created_at->format('H:i') : now()->format('H:i'));
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
                <h1>Control de Signos Vitales</h1>
            </td>
        </tr>
    </table>

    <!-- Ficha Paciente -->
    <table class="patient-box">
        <tr>
            <td style="width: 55%;">
                <span class="lbl">PACIENTE:</span>
                <span class="val">{{ $paciente->nombre_completo ?? '---' }}</span>
            </td>
            <td style="width: 25%;">
                <span class="lbl">N° H.C.:</span>
                <span class="val">{{ $paciente->numero_historia_clinica ?? $proforma->id }}</span>
            </td>
            <td style="width: 20%;">
                <span class="lbl">EDAD:</span>
                <span class="val">{{ $edadTexto }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 55%;">
                <span class="lbl">SERVICIO:</span>
                <span class="val">{{ $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion ?: 'HOSPITALIZACIÓN') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="lbl">SALA / CAMA:</span>
                <span class="val">{{ $pieza }}</span>
            </td>
            <td style="width: 20%;">
                <span class="lbl">SEXO:</span>
                <span class="val">{{ $paciente->genero ?? '---' }}</span>
            </td>
        </tr>
    </table>

    <!-- Planilla de Registro -->
    <table class="vital-table">
        <thead>
            <tr class="title-row">
                <th colspan="7">CONTROL DE SIGNOS VITALES</th>
            </tr>
            <tr>
                <th style="width: 15%;">FECHA</th>
                <th style="width: 11%;">HORA</th>
                <th style="width: 15%;">PA (mmHg)</th>
                <th style="width: 14%;">FC (lpm)</th>
                <th style="width: 14%;">FR (rpm)</th>
                <th style="width: 15%;">T (ºC)</th>
                <th style="width: 16%;">SatO2 (%)</th>
            </tr>
        </thead>
        <tbody>
            @if ($historia && ($historia->pa || $historia->fc || $historia->fr || $historia->temperatura || $historia->sato2))
                <tr>
                    <td>{{ $fechaRegistro }}</td>
                    <td>{{ $horaRegistro }}</td>
                    <td style="font-weight: 700;">{{ $historia->pa ?? '' }}</td>
                    <td style="font-weight: 700;">{{ $historia->fc ?? '' }}</td>
                    <td style="font-weight: 700;">{{ $historia->fr ?? '' }}</td>
                    <td style="font-weight: 700;">{{ $historia->temperatura ? $historia->temperatura . '°' : '' }}</td>
                    <td style="font-weight: 700;">{{ $historia->sato2 ? $historia->sato2 . '%' : '' }}</td>
                </tr>
                @for ($i = 0; $i < 33; $i++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @else
                @for ($i = 0; $i < 34; $i++)
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            @endif
        </tbody>
    </table>
</body>
</html>

