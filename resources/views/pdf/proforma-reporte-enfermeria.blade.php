<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Enfermería - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 20px 28px 20px 28px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 10px;
            line-height: 1.35;
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
            height: 48px;
            max-width: 170px;
            object-fit: contain;
        }
        .header-title {
            width: 55%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 17px;
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
            font-size: 11px;
            font-weight: bold;
            color: #1e3a8a;
        }
        .header-hc span {
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 1px;
            font-family: monospace;
            font-size: 11.5px;
            color: #0f172a;
        }

        /* Bloque Datos del Paciente */
        .patient-box-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            border: 1.5px solid #2563eb;
            background-color: #f8fafc;
        }
        .patient-box-table td {
            padding: 5px 8px;
            font-size: 10.5px;
        }
        .row-paciente {
            border-bottom: 1px solid #cbd5e1;
        }
        .lbl {
            font-weight: bold;
            color: #1e3a8a;
            font-size: 10px;
            text-transform: uppercase;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }

        /* Tabla Principal de Contenido */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1.5px solid #2563eb;
        }
        .content-table thead {
            display: table-header-group;
        }
        .content-table tr {
            page-break-inside: avoid;
        }
        .content-table th {
            border: 1px solid #2563eb;
            padding: 6px 4px;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            background-color: #eff6ff;
            color: #1e3a8a;
            letter-spacing: 0.3px;
        }
        .content-table td {
            border: 1px solid #94a3b8;
            padding: 5px 6px;
            vertical-align: top;
            font-size: 9.5px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        .col-fecha {
            width: 11%;
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }
        .col-hora {
            width: 9%;
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            white-space: nowrap;
        }
        .col-procedimiento {
            width: 38%;
            text-align: left;
        }
        .col-sintomas {
            width: 30%;
            text-align: left;
        }
        .col-firma {
            width: 12%;
            text-align: center;
            font-size: 8.5px;
        }

        .rich-content {
            color: #0f172a;
            line-height: 1.35;
        }
        .rich-content h1,
        .rich-content h2,
        .rich-content h3,
        .rich-content h4,
        .rich-content h5,
        .rich-content h6 {
            font-size: 10.5px;
            font-weight: bold;
            margin: 2px 0;
            line-height: 1.25;
            color: #0f172a;
        }
        .rich-content p {
            margin: 0 0 3px 0;
        }
        .rich-content p:last-child {
            margin-bottom: 0;
        }
        .rich-content ul, .rich-content ol {
            margin: 2px 0 3px 14px;
            padding: 0;
        }
        .rich-content li {
            margin-bottom: 1px;
        }
        .rich-content blockquote {
            margin: 2px 0 2px 8px;
            padding-left: 6px;
            border-left: 2px solid #cbd5e1;
            color: #475569;
        }

        .user-firma-badge {
            font-weight: 600;
            color: #334155;
            font-size: 8.5px;
            line-height: 1.2;
            padding-top: 8px;
            border-top: 1px dashed #cbd5e1;
            margin-top: 4px;
        }

        .ruled-empty-row td {
            height: 22px;
            border-bottom: 1px solid #cbd5e1;
        }

        .footer-note {
            margin-top: 12px;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 4px;
            font-size: 8px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    @php
        $paciente = $proforma->paciente;
        $servicio = $proforma->tipo_atencion ?: 'INTERNACIÓN';
        $pieza = $proforma->pieza ?: '---';
        $hc = $paciente?->numero_historia_clinica ?: '---';
    @endphp

    <!-- Encabezado con Logo, Título y HC Nº -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('img/logo_clinica.png')))
                    <img src="{{ public_path('img/logo_clinica.png') }}" alt="Logo Clínica">
                @else
                    <strong style="color: #1e3a8a; font-size: 12px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>REPORTE DE ENFERMERIA</h1>
            </td>
            <td class="header-hc">
                HC Nº: <span>&nbsp;{{ $hc }}&nbsp;</span>
            </td>
        </tr>
    </table>

    <!-- Bloque de Identificación del Paciente y Ubicación -->
    <table class="patient-box-table">
        <tr class="row-paciente">
            <td colspan="3">
                <span class="lbl">Nombre del Paciente:</span>
                <span class="val" style="font-size: 11px; margin-left: 6px;">{{ strtoupper($paciente->nombre_completo ?? 'N/D') }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 50%;">
                <span class="lbl">Servicio:</span>
                <span class="val">{{ strtoupper($servicio) }}</span>
            </td>
            <td style="width: 50%;" colspan="2">
                <span class="lbl">Nº de Cama / Sala:</span>
                <span class="val">{{ $pieza }}</span>
            </td>
        </tr>
    </table>

    <!-- Tabla Principal de Registros de Enfermería -->
    <table class="content-table">
        <thead>
            <tr>
                <th class="col-fecha">FECHA</th>
                <th class="col-hora">HORA</th>
                <th class="col-procedimiento">PROCEDIMIENTO Y MEDICACIÓN</th>
                <th class="col-sintomas">DESCRIPCIÓN DE SINTOMAS Y OBSERVACIONES</th>
                <th class="col-firma">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reportes as $item)
                <tr>
                    <td class="col-fecha">
                        {{ $item->fecha_hora ? $item->fecha_hora->format('d/m/Y') : '-' }}
                    </td>
                    <td class="col-hora">
                        {{ $item->fecha_hora ? $item->fecha_hora->format('H:i') : '-' }}
                    </td>
                    <td class="col-procedimiento">
                        <div class="rich-content">
                            {!! $item->procedimiento_medicacion ?: '<span style="color:#94a3b8; font-style:italic;">Sin procedimiento registrado</span>' !!}
                        </div>
                    </td>
                    <td class="col-sintomas">
                        <div class="rich-content">
                            {!! $item->observaciones ?: '<span style="color:#94a3b8; font-style:italic;">Sin observaciones registradas</span>' !!}
                        </div>
                    </td>
                    <td class="col-firma">
                        <div class="user-firma-badge">
                            {{ $item->usuarioCreador->name ?? 'Enfermería' }}
                        </div>
                    </td>
                </tr>
            @empty
                @for($i = 0; $i < 18; $i++)
                    <tr class="ruled-empty-row">
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        <div class="footer-left">Clínica Boliviana Americana &bull; Proforma #{{ $proforma->id }}</div>
        <div class="footer-right">Impreso: {{ $fechaEmision }}</div>
    </div>

</body>
</html>
