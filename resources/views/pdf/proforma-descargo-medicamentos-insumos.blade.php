<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargo de Insumos y Medicamentos - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 16px 22px 16px 22px;
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

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-logo {
            width: 30%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 34px;
            max-width: 150px;
            object-fit: contain;
        }
        .header-title {
            width: 70%;
            vertical-align: middle;
            text-align: center;
            padding-right: 25%;
        }
        .header-title h1 {
            font-size: 14.5px;
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
            margin-bottom: 6px;
        }
        .patient-box td {
            border: 1px solid #0f172a;
            padding: 2.5px 5px;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            font-size: 7px;
            margin-right: 3px;
        }
        .val {
            font-weight: 700;
            color: #0f172a;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
        }
        .data-table th {
            border: 1px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7.5px;
            padding: 3.5px 2px;
            text-align: center;
            text-transform: uppercase;
        }
        .data-table td {
            border: 1px solid #334155;
            height: 22px;
            text-align: center;
            font-size: 7.5px;
            padding: 1px 3px;
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
        $sala = $cirugia?->sala ?: ($proforma->servicios->first()?->servicio?->nombre ?? 'HOSPITALIZACIÓN');
        $hc = $paciente?->numero_historia_clinica ?: '---';
        $medicoTratante = $proforma->medicos->pluck('nombre_completo')->filter()->join(', ') ?: ($cirugia?->cirujano?->nombre_completo ?? 'No asignado');

        // Consumos de insumos y medicamentos extras cargados en piso
        $consumosExtras = $proforma->consumosExtras ?? collect();
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
                <h1>DESCARGO DE INSUMOS Y MEDICAMENTOS</h1>
            </td>
        </tr>
    </table>

    <!-- Ficha del Paciente y Médico Tratante -->
    <table class="patient-box">
        <tr>
            <td style="width: 50%;">
                <span class="lbl">NOMBRE:</span>
                <span class="val">{{ $paciente?->nombre_completo ?: '---' }}</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="lbl">EDAD:</span>
                <span class="val">{{ $edadTexto }}</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="lbl">N° H.C.:</span>
                <span class="val">{{ $hc }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <span class="lbl">MEDICO TRATANTE:</span>
                <span class="val">{{ $medicoTratante }}</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="lbl">SALA:</span>
                <span class="val">{{ $sala }}</span>
            </td>
            <td style="width: 25%; text-align: center;">
                <span class="lbl">CAMA:</span>
                <span class="val">{{ $cama }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla de Descargo de Medicamentos e Insumos -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 9%;">FECHA</th>
                <th style="width: 7%;">HORA</th>
                <th style="width: 42%;">MEDICAMENTOS E INSUMOS</th>
                <th style="width: 7%;">CANT.</th>
                <th style="width: 11%;">PRECIO</th>
                <th style="width: 13%;">SOLICITANTE</th>
                <th style="width: 11%;">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalFilas = 29;
            @endphp
            @for ($i = 0; $i < $totalFilas; $i++)
                @php
                    $consumo = $consumosExtras->get($i);
                @endphp
                <tr>
                    <td>{{ $consumo ? ($consumo->fecha_consumo ? $consumo->fecha_consumo->format('d/m/Y') : $consumo->created_at->format('d/m/Y')) : '' }}</td>
                    <td>{{ $consumo ? ($consumo->fecha_consumo ? $consumo->fecha_consumo->format('H:i') : $consumo->created_at->format('H:i')) : '' }}</td>
                    <td style="text-align: left; padding-left: 5px; font-weight: {{ $consumo ? '700' : 'normal' }};">
                        {{ $consumo ? ($consumo->producto?->nombre ?? 'Insumo') : '' }}
                    </td>
                    <td>{{ $consumo ? $consumo->cantidad : '' }}</td>
                    <td>{{ $consumo ? number_format($consumo->precio_total, 2) : '' }}</td>
                    <td>{{ $consumo ? ($consumo->user?->name ?? 'Enfermería') : '' }}</td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>

