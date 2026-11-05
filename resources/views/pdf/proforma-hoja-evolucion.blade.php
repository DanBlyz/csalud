<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Evolución - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 20px 28px 20px 28px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 10.5px;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
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
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 15%;
        }
        .header-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        /* Recuadro Datos Paciente */
        .patient-box-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            margin-bottom: 0px;
        }
        .patient-box-table td {
            padding: 5px 8px;
            font-size: 11px;
            border-right: 1.5px solid #0f172a;
        }
        .patient-box-table td:last-child {
            border-right: none;
        }
        .patient-nombre {
            width: 60%;
        }
        .patient-edad {
            width: 18%;
        }
        .patient-hc {
            width: 22%;
        }
        /* Tabla Principal de Contenido */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            border-left: 1.5px solid #0f172a;
            border-right: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            margin-top: -1px;
        }
        .content-table th {
            border-top: 1.5px solid #0f172a;
            border-bottom: 1.5px solid #0f172a;
            border-right: 1.5px solid #0f172a;
            padding: 6px 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
            background-color: #f8fafc;
        }
        .content-table th:last-child {
            border-right: none;
        }
        .col-fecha {
            width: 18%;
            text-align: center;
        }
        .col-desc {
            width: 82%;
            /* text-align: center; */
        }
        .content-table td {
            border-bottom: 1px solid #94a3b8;
            border-right: 1.5px solid #0f172a;
            padding: 5px 8px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .content-table td:last-child {
            border-right: none;
        }
        .entry-fecha {
            text-align: center;
            font-weight: 600;
            color: #1e293b;
            font-size: 10px;
            white-space: nowrap;
        }
        .entry-body {
            color: #0f172a;
        }
        .entry-body p {
            margin: 0 0 4px 0;
        }
        .entry-body p:last-child {
            margin-bottom: 0;
        }
        .entry-body ul {
            list-style-type: disc !important;
            margin: 2px 0 4px 18px;
            padding: 0;
        }
        .entry-body ol {
            list-style-type: decimal !important;
            margin: 2px 0 4px 18px;
            padding: 0;
        }
        .entry-body li {
            margin-bottom: 2px;
        }
        .entry-meta {
            text-align: right;
            font-size: 8.5px;
            color: #64748b;
            margin-top: 3px;
        }
        .ruled-empty-row td {
            height: 22px;
            border-bottom: 1px solid #cbd5e1;
        }
    </style>
</head>
<body>

    <!-- Encabezado con Logo y Título -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('img/logo_clinica.png')))
                    <img src="{{ public_path('img/logo_clinica.png') }}" alt="Logo Clínica">
                @else
                    <strong style="color: #1e3a8a; font-size: 13px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>HOJA DE EVOLUCIÓN</h1>
            </td>
        </tr>
    </table>

    <!-- Fila de Identificación del Paciente -->
    <table class="patient-box-table">
        <tr>
            <td class="patient-nombre">
                <strong>NOMBRE:</strong> {{ $proforma->paciente->nombre_completo ?? 'N/D' }}
            </td>
            <td class="patient-edad">
                <strong>EDAD:</strong> {{ $proforma->edad_paciente ?? ($proforma->paciente->edad_en_fecha ?? $proforma->paciente->edad) ?? '-' }}
            </td>
            <td class="patient-hc">
                <strong>N° H.C.:</strong> {{ $proforma->paciente->numero_historia_clinica ?? '-' }}
            </td>
        </tr>
    </table>

    <!-- Tabla con Grilla y Registros -->
    <table class="content-table">
        <thead>
            <tr>
                <th class="col-fecha">FECHA Y HORA</th>
                <th class="col-desc">EVOLUCION</th>
            </tr>
        </thead>
        <tbody>
            @forelse($evoluciones as $item)
                <tr>
                    <td class="col-fecha entry-fecha">
                        {{ $item->fecha_hora ? $item->fecha_hora->format('d/m/Y') : '' }}<br>
                        <span style="font-size: 9.5px; color: #475569;">{{ $item->fecha_hora ? $item->fecha_hora->format('H:i') : '' }}</span>
                    </td>
                    <td class="col-desc">
                        <div class="entry-body">
                            {!! $item->descripcion !!}
                        </div>
                        @if($item->usuarioCreador)
                            <div class="entry-meta">
                                Registrado por: {{ $item->usuarioCreador->name }}
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
            @endforelse

            {{-- Líneas regladas para completar el formulario estilo hoja médica --}}
            {{-- @php
                $registrosCount = count($evoluciones);
                $minLineas = max(6, 26 - ($registrosCount * 4));
            @endphp
            @for($i = 0; $i < $minLineas; $i++)
                <tr class="ruled-empty-row">
                    <td class="col-fecha">&nbsp;</td>
                    <td class="col-desc">&nbsp;</td>
                </tr>
            @endfor --}}
        </tbody>
    </table>

</body>
</html>

