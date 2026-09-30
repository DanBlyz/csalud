<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Kardex y Movimientos de Inventario</title>
    <style>
        @page {
            margin: 24px 28px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }
        .clinic-title {
            font-size: 16px;
            font-weight: bold;
            color: #0369a1;
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
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            display: inline-block;
        }
        .doc-number {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 3px;
        }
        
        /* Cajas de Filtros y Parámetros */
        .params-table {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 14px;
            font-size: 9px;
        }
        .params-table td {
            padding: 4px 8px;
        }
        .param-label {
            font-weight: bold;
            color: #475569;
        }
        .param-value {
            color: #0f172a;
        }

        /* Bloque por Producto */
        .product-section {
            margin-bottom: 16px;
            page-break-inside: avoid;
        }
        .product-header-table {
            width: 100%;
            background-color: #0f172a;
            color: #ffffff;
            padding: 5px 8px;
            border-radius: 4px 4px 0 0;
            font-size: 10px;
        }
        .product-name {
            font-size: 11px;
            font-weight: bold;
            color: #38bdf8;
        }
        .product-meta {
            font-size: 8.5px;
            color: #94a3b8;
        }

        /* Tabla de Movimientos */
        .mov-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
            margin-bottom: 4px;
        }
        .mov-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 4px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8.5px;
            text-transform: uppercase;
        }
        .mov-table td {
            padding: 3.5px 6px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .mov-table tr:nth-child(even) td {
            background-color: #fcfdfe;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .text-success { color: #15803d; }
        .text-danger { color: #b91c1c; }
        .text-muted { color: #64748b; }

        /* Fila de Saldo Inicial */
        .row-saldo-inicial {
            background-color: #eff6ff !important;
            font-weight: bold;
            color: #1e40af;
        }

        /* Footer de Producto / Resumen */
        .product-summary-table {
            width: 100%;
            border: 1px solid #cbd5e1;
            border-top: none;
            background-color: #f8fafc;
            border-radius: 0 0 4px 4px;
            font-size: 9px;
            margin-bottom: 8px;
        }
        .product-summary-table td {
            padding: 4px 8px;
        }
        .summary-metric {
            display: inline-block;
            margin-right: 15px;
        }

        /* Resumen General Final */
        .grand-summary-box {
            background-color: #f8fafc;
            border: 2px solid #0284c7;
            border-radius: 6px;
            padding: 10px;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .grand-summary-title {
            font-size: 11px;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
            border-bottom: 1px solid #bae6fd;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .grand-summary-table {
            width: 100%;
            font-size: 9.5px;
        }
        .grand-summary-table td {
            padding: 4px 6px;
        }

        /* Pie de página */
        .footer-table {
            width: 100%;
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Encabezado Principal -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="clinic-title">CENTRO MÉDICO DE SALUD</div>
                <div class="clinic-subtitle">
                    {{ $sucursal->nombre ?? 'Todas las Sucursales' }} 
                    @if($sucursal && $sucursal->ciudad) - {{ $sucursal->ciudad }} @endif
                </div>
                <div class="clinic-subtitle">
                    Dirección: {{ $sucursal->direccion ?? 'Sede Principal' }} 
                    @if($sucursal && $sucursal->telefono) | Tel: {{ $sucursal->telefono }} @endif
                </div>
            </td>
            <td style="width: 40%; vertical-align: top;" class="doc-title-box">
                <span class="doc-badge">REPORTE ADMINISTRATIVO</span>
                <div class="doc-number">KARDEX DE INVENTARIO</div>
                <div class="clinic-subtitle" style="margin-top: 2px;">
                    Emisión: {{ $fechaEmision }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Parámetros del Reporte -->
    <table class="params-table">
        <tr>
            <td style="width: 35%;">
                <span class="param-label">Rango Evaluado:</span> 
                <span class="param-value">{{ $fechaInicioFormato }} al {{ $fechaFinFormato }}</span>
            </td>
            <td style="width: 35%;">
                <span class="param-label">Alcance de Catálogo:</span> 
                <span class="param-value">
                    @if($productoFiltro)
                        {{ $productoFiltro->nombre }} ({{ $productoFiltro->marca?->nombre ?? 'Sin Marca' }})
                    @else
                        Todos los Fármacos e Insumos ({{ $reporte['resumen_general']['total_productos'] }} con actividad)
                    @endif
                </span>
            </td>
            <td style="width: 30%;">
                <span class="param-label">Generado por:</span> 
                <span class="param-value">{{ $usuarioEmisor }}</span>
            </td>
        </tr>
    </table>

    @if(empty($reporte['items']))
        <div style="padding: 25px; text-align: center; color: #64748b; background-color: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px;">
            No se registraron movimientos ni saldos para el período y criterios seleccionados.
        </div>
    @else
        <!-- Listado por Producto -->
        @foreach($reporte['items'] as $item)
            <div class="product-section">
                <!-- Barra de Título del Producto -->
                <table class="product-header-table">
                    <tr>
                        <td style="width: 65%;">
                            <span class="product-name">{{ $item['producto_nombre'] }}</span>
                            <span class="product-meta"> | Marca: {{ $item['marca_nombre'] }} | Presentación: {{ $item['unidad_medida'] }}</span>
                        </td>
                        <td style="width: 35%; text-align: right;">
                            <span class="product-meta">
                                Stock Mínimo: {{ $item['stock_minimo'] }} | 
                                P. Compra: Bs. {{ number_format($item['ultimo_precio_compra'], 2) }} | 
                                P. Venta: Bs. {{ number_format($item['ultimo_precio_venta'], 2) }}
                            </span>
                        </td>
                    </tr>
                </table>

                <!-- Detalle de Movimientos -->
                <table class="mov-table">
                    <thead>
                        <tr>
                            <th style="width: 14%;">Fecha/Hora</th>
                            <th style="width: 15%;">Lote / Vto.</th>
                            <th style="width: 22%;">Concepto / Movimiento</th>
                            <th style="width: 25%;">Referencia / Proforma</th>
                            <th style="width: 8%; text-align: right;">Entrada</th>
                            <th style="width: 8%; text-align: right;">Salida</th>
                            <th style="width: 8%; text-align: right;">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Fila de Saldo Inicial a la Fecha de Inicio -->
                        <tr class="row-saldo-inicial">
                            <td>{{ $fechaInicioFormato }} 00:00</td>
                            <td class="text-center">-</td>
                            <td colspan="2">
                                <strong>SALDO INICIAL AL CORTE</strong>
                                <span class="text-muted" style="font-size: 8px;">(Cálculo acumulado previo a la fecha inicial)</span>
                            </td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right font-bold">{{ number_format($item['saldo_inicial']) }}</td>
                        </tr>

                        @if(empty($item['movimientos']))
                            <tr>
                                <td colspan="7" class="text-center text-muted" style="padding: 6px;">
                                    Sin movimientos en el rango de fechas seleccionado.
                                </td>
                            </tr>
                        @else
                            @foreach($item['movimientos'] as $mov)
                                <tr>
                                    <td>{{ $mov['fecha'] }}</td>
                                    <td>
                                        <strong>{{ $mov['lote_codigo'] }}</strong>
                                        @if($mov['lote_vencimiento'])
                                            <div style="font-size: 7.5px; color: #64748b;">Vto: {{ $mov['lote_vencimiento'] }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($mov['es_entrada'])
                                            <span class="text-success font-bold">+ {{ $mov['tipo_movimiento'] }}</span>
                                        @else
                                            <span class="text-danger font-bold">- {{ $mov['tipo_movimiento'] }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $mov['referencia'] }}</td>
                                    <td class="text-right font-bold text-success">
                                        {{ $mov['cantidad_entrada'] > 0 ? number_format($mov['cantidad_entrada']) : '-' }}
                                    </td>
                                    <td class="text-right font-bold text-danger">
                                        {{ $mov['cantidad_salida'] > 0 ? number_format($mov['cantidad_salida']) : '-' }}
                                    </td>
                                    <td class="text-right font-bold">
                                        {{ number_format($mov['saldo_acumulado']) }}
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>

                <!-- Resumen y Valorización del Producto -->
                <table class="product-summary-table">
                    <tr>
                        <td style="width: 30%;">
                            <span class="param-label">Entradas Período:</span> 
                            <strong class="text-success">{{ number_format($item['total_entradas']) }}</strong> | 
                            <span class="param-label">Salidas:</span> 
                            <strong class="text-danger">{{ number_format($item['total_salidas']) }}</strong>
                        </td>
                        <td style="width: 25%;">
                            <span class="param-label">Stock Final al {{ $fechaFinFormato }}:</span> 
                            <strong style="color: #0369a1; font-size: 10px;">{{ number_format($item['saldo_final']) }} {{ $item['unidad_medida'] }}</strong>
                        </td>
                        <td style="width: 23%; text-align: right;">
                            <span class="param-label">Val. Compra:</span> 
                            <strong>Bs. {{ number_format($item['valor_total_compra'], 2) }}</strong>
                        </td>
                        <td style="width: 22%; text-align: right;">
                            <span class="param-label">Val. Venta:</span> 
                            <strong style="color: #0f172a;">Bs. {{ number_format($item['valor_total_venta'], 2) }}</strong>
                        </td>
                    </tr>
                </table>
            </div>
        @endforeach

        <!-- Gran Resumen Consolidado General -->
        <div class="grand-summary-box">
            <div class="grand-summary-title">Resumen Consolidado del Reporte</div>
            <table class="grand-summary-table">
                <tr>
                    <td style="width: 25%;">
                        <div class="param-label">Productos Evaluados:</div>
                        <div class="font-bold" style="font-size: 11px;">{{ $reporte['resumen_general']['total_productos'] }} items</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="param-label">Total Entradas / Salidas:</div>
                        <div>
                            <span class="text-success font-bold">+{{ number_format($reporte['resumen_general']['gran_total_entradas']) }}</span> / 
                            <span class="text-danger font-bold">-{{ number_format($reporte['resumen_general']['gran_total_salidas']) }}</span>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="param-label">Stock Total al Corte:</div>
                        <div class="font-bold" style="font-size: 11px; color: #0284c7;">
                            {{ number_format($reporte['resumen_general']['gran_total_saldo_final']) }} unidades
                        </div>
                    </td>
                    <td style="width: 25%; text-align: right;">
                        <div class="param-label">Margen Bruto Teórico:</div>
                        <div class="font-bold" style="font-size: 11px; color: #16a34a;">
                            Bs. {{ number_format($reporte['resumen_general']['margen_bruto_potencial'], 2) }}
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"></td>
                    <td style="text-align: right; border-top: 1px solid #cbd5e1; padding-top: 6px;">
                        <span class="param-label">Valorización Total Compra:</span>
                        <div class="font-bold" style="font-size: 12px; color: #334155;">
                            Bs. {{ number_format($reporte['resumen_general']['gran_total_valor_compra'], 2) }}
                        </div>
                    </td>
                    <td style="text-align: right; border-top: 1px solid #cbd5e1; padding-top: 6px;">
                        <span class="param-label">Valorización Total Venta:</span>
                        <div class="font-bold" style="font-size: 12px; color: #0f172a;">
                            Bs. {{ number_format($reporte['resumen_general']['gran_total_valor_venta'], 2) }}
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    @endif

    <!-- Firma y Pie de Documento -->
    <table class="footer-table">
        <tr>
            <td style="width: 50%;">
                Documento administrativo generado electrónicamente por el Sistema de Gestión Clínica y Farmacia.
            </td>
            <td style="width: 50%; text-align: right;">
                Válido para auditorías de inventario y fiscalización interna.
            </td>
        </tr>
    </table>

</body>
</html>
