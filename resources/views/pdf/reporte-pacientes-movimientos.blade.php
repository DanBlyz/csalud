<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos y Salidas de Pacientes</title>
    <style>
        @page {
            margin: 20px 24px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }
        .clinic-title {
            font-size: 16px;
            font-weight: bold;
            color: #1d4ed8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-subtitle {
            font-size: 9px;
            color: #64748b;
        }
        .doc-title-box {
            text-align: right;
        }
        .doc-badge {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1d4ed8;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            display: inline-block;
            text-transform: uppercase;
        }
        .doc-number {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        .kpi-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 6px 0;
            margin-bottom: 10px;
        }
        .kpi-cell {
            padding: 6px 8px;
            border-radius: 6px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            text-align: center;
        }
        .kpi-title {
            font-size: 8px;
            text-transform: uppercase;
            font-weight: bold;
            color: #64748b;
            letter-spacing: 0.4px;
        }
        .kpi-value {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
            font-family: monospace;
        }
        .filter-summary {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 5px 8px;
            font-size: 9px;
            margin-bottom: 10px;
            color: #334155;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 14px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-size: 8.5px;
            text-transform: uppercase;
            padding: 6px 5px;
            text-align: left;
            border: 1px solid #1e3a8a;
            letter-spacing: 0.3px;
        }
        .data-table td {
            padding: 5px 5px;
            border: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .font-mono {
            font-family: monospace;
        }
        .badge-room {
            background-color: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 8.5px;
            display: inline-block;
        }
        .badge-inst {
            background-color: #e0e7ff;
            border: 1px solid #c7d2fe;
            color: #3730a3;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-active {
            color: #047857;
            font-weight: bold;
        }
        .badge-discharged {
            color: #475569;
        }
        .signatures {
            margin-top: 30px;
            width: 100%;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #64748b;
            width: 75%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 9px;
            color: #475569;
        }
        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #94a3b8;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- Header Oficial -->
    <table class="header-table">
        <tr>
            <td style="width: 55%; vertical-align: top;">
                <div class="clinic-title">Centro de Salud</div>
                <div class="clinic-subtitle">
                    <strong>Sucursal:</strong> {{ $sucursal->nombre ?? 'Todas las Sucursales' }}<br>
                    <strong>Dirección:</strong> {{ $sucursal->direccion ?? 'Av. Principal #123' }} &bull; 
                    <strong>Teléfono:</strong> {{ $sucursal->telefono ?? 'S/N' }}<br>
                    <strong>Sistema Integral de Gestión Hospitalaria (CSALUD)</strong>
                </div>
            </td>
            <td class="doc-title-box" style="width: 45%; vertical-align: top;">
                <div class="doc-badge">REPORTE CLÍNICO-ADMINISTRATIVO</div>
                <div class="doc-number">INGRESOS, SALIDAS Y OCUPACIÓN DE HABITACIONES</div>
                <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">
                    Período: <strong>{{ $fechaInicioFormato }}</strong> al <strong>{{ $fechaFinFormato }}</strong> &bull; Emisión: {{ $fechaEmision }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Barra de Criterios y Filtros Aplicados -->
    <div class="filter-summary">
        <strong>Filtros aplicados:</strong>
        &bull; <strong>Institución:</strong> {{ $reporte['institucion_nombre'] }}
        &bull; <strong>Sucursal:</strong> {{ $reporte['sucursal_nombre'] }}
        &bull; <strong>Criterio de Búsqueda:</strong> 
        @if($reporte['tipo_filtro'] === 'ingresos')
            Solo Ingresos en el período
        @elseif($reporte['tipo_filtro'] === 'salidas')
            Solo Altas / Salidas en el período
        @elseif($reporte['tipo_filtro'] === 'internados')
            Pacientes Actualmente Internados
        @else
            Todos (Ingresos, Altas y Estancias activas)
        @endif
        @if(!empty($reporte['tipo_atencion']))
            &bull; <strong>Tipo de Atención:</strong> {{ $reporte['tipo_atencion'] }}
        @endif
        &bull; <strong>Usuario Emisor:</strong> {{ $usuarioEmisor }}
    </div>

    <!-- Tarjetas de Resumen KPI -->
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell" style="width: 17%;">
                <div class="kpi-title">Total Registros</div>
                <div class="kpi-value">{{ $reporte['totales']['total_registros'] }}</div>
            </td>
            <td class="kpi-cell" style="width: 17%; border-left: 3px solid #10b981;">
                <div class="kpi-title">Ingresos Período</div>
                <div class="kpi-value" style="color: #047857;">{{ $reporte['totales']['ingresos_periodo'] }}</div>
            </td>
            <td class="kpi-cell" style="width: 17%; border-left: 3px solid #6366f1;">
                <div class="kpi-title">Altas / Salidas</div>
                <div class="kpi-value" style="color: #4338ca;">{{ $reporte['totales']['salidas_periodo'] }}</div>
            </td>
            <td class="kpi-cell" style="width: 17%; border-left: 3px solid #f59e0b;">
                <div class="kpi-title">Internados Activos</div>
                <div class="kpi-value" style="color: #b45309;">{{ $reporte['totales']['internados_activos'] }}</div>
            </td>
            <td class="kpi-cell" style="width: 16%;">
                <div class="kpi-title">Hospitalarios</div>
                <div class="kpi-value">{{ $reporte['totales']['hospitalarios'] }}</div>
            </td>
            <td class="kpi-cell" style="width: 16%;">
                <div class="kpi-title">Ambulatorios</div>
                <div class="kpi-value">{{ $reporte['totales']['ambulatorios'] }}</div>
            </td>
        </tr>
    </table>

    <!-- Tabla Detallada de Pacientes -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 6%;">Proforma</th>
                <th style="width: 18%;">Paciente / C.I.</th>
                <th style="width: 12%;">Institución / Convenio</th>
                <th style="width: 11%;">Pieza / Habitación</th>
                <th style="width: 8%;">Atención</th>
                <th style="width: 11%;">Fecha Ingreso</th>
                <th style="width: 11%;">Fecha Salida</th>
                <th style="width: 15%;">Diagnóstico / Motivo</th>
                <th style="width: 8%; text-align: center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reporte['items'] as $item)
                <tr>
                    <td class="font-mono font-bold text-center">
                        #{{ str_pad($item['proforma_id'], 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td>
                        <strong>{{ $item['paciente_nombre'] }}</strong><br>
                        <span style="font-size: 8px; color: #64748b;">
                            C.I.: {{ $item['paciente_cedula'] }}
                            @if($item['paciente_edad']) &bull; {{ $item['paciente_edad'] }} años @endif
                        </span>
                    </td>
                    <td>
                        <span class="badge-inst">{{ $item['institucion_nombre'] }}</span>
                    </td>
                    <td>
                        @if($item['pieza'] && $item['pieza'] !== 'Ambulatorio' && $item['pieza'] !== 'Sin asignar')
                            <span class="badge-room">{{ $item['pieza'] }}</span>
                        @else
                            <span style="color: #64748b; font-size: 8.5px;">{{ $item['pieza'] }}</span>
                        @endif
                    </td>
                    <td>
                        <span style="font-size: 8.5px; font-weight: bold; color: {{ $item['tipo_atencion'] === 'Internacion' ? '#b45309' : '#047857' }};">
                            {{ $item['tipo_atencion'] }}
                        </span>
                    </td>
                    <td class="font-mono" style="font-size: 8.5px;">
                        {{ $item['fecha_ingreso'] }}
                    </td>
                    <td class="font-mono" style="font-size: 8.5px;">
                        @if($item['fecha_salida'])
                            <span class="badge-discharged">{{ $item['fecha_salida'] }}</span><br>
                            <span style="font-size: 7.5px; color: #64748b;">({{ $item['dias_estancia'] }} día{{ $item['dias_estancia'] > 1 ? 's' : '' }})</span>
                        @else
                            <span class="badge-active">&#9679; En Curso</span>
                        @endif
                    </td>
                    <td style="font-size: 8.5px;">
                        <strong>{{ $item['diagnostico'] ?: 'Sin diagnóstico' }}</strong>
                        @if($item['motivo_consulta'])
                            <br><span style="font-size: 7.5px; color: #64748b;">{{ \Illuminate\Support\Str::limit($item['motivo_consulta'], 45) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span style="font-size: 8px; font-weight: bold; padding: 1px 4px; border-radius: 3px; background-color: {{ $item['estado'] === 'Pagada' ? '#ecfdf5' : '#fffbeb' }}; color: {{ $item['estado'] === 'Pagada' ? '#047857' : '#b45309' }};">
                            {{ $item['estado'] }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align: center; padding: 20px; color: #64748b; font-style: italic;">
                        No se registraron movimientos de pacientes para los criterios y rango de fechas seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Bloque de Firmas -->
    <table class="signatures">
        <tr>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Admisión y Registros Médicos</strong><br>
                    Responsable de Turno / Caja
                </div>
            </td>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Dirección Médica / Administración</strong><br>
                    Revisión y Conformidad
                </div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Documento generado automáticamente por CSALUD &bull; Fecha: {{ $fechaEmision }} &bull; Usuario: {{ $usuarioEmisor }}
    </div>

</body>
</html>
