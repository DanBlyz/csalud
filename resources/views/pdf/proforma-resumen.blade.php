<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Resumen de Proforma #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 28px 32px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .clinic-title {
            font-size: 18px;
            font-weight: bold;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .clinic-subtitle {
            font-size: 10px;
            color: #64748b;
        }
        .doc-title-box {
            text-align: right;
        }
        .doc-badge {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            color: #0369a1;
            padding: 4px 10px;
            font-weight: bold;
            font-size: 11px;
            border-radius: 4px;
            display: inline-block;
        }
        .doc-number {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 4px;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            color: #0f172a;
            background-color: #f1f5f9;
            padding: 5px 8px;
            border-left: 3px solid #0284c7;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .info-table td {
            padding: 3.5px 6px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
        }
        .info-value {
            color: #1e293b;
            width: 32%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #334155;
            font-weight: bold;
            text-align: left;
            padding: 5px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 1.5px solid #94a3b8;
            font-size: 10px;
            text-transform: uppercase;
        }
        .data-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .font-mono { font-family: Courier, monospace; }
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .totals-table td {
            padding: 4px 8px;
            font-size: 11px;
        }
        .total-row {
            background-color: #f8fafc;
            border-top: 2px solid #0284c7;
            font-size: 13px !important;
            font-weight: bold;
            color: #0369a1;
        }
        .signatures {
            margin-top: 40px;
            width: 100%;
        }
        .signature-box {
            text-align: center;
            width: 45%;
        }
        .signature-line {
            border-top: 1px solid #64748b;
            width: 80%;
            margin: 0 auto;
            padding-top: 4px;
            font-size: 10px;
            color: #475569;
        }
        .status-pill {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pagada { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
        .status-proceso { background-color: #e0f2fe; color: #0369a1; border: 1px solid #7dd3fc; }
        .status-confirmada { background-color: #fef9c3; color: #854d0e; border: 1px solid #fde047; }
        .status-borrador { background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="clinic-title">Centro de Salud</div>
                <div class="clinic-subtitle">
                    <strong>Sucursal:</strong> {{ $proforma->sucursal->nombre ?? 'Principal' }}<br>
                    <strong>Dirección:</strong> {{ $proforma->sucursal->direccion ?? 'Av. Principal #123' }} &bull; 
                    <strong>Teléfono:</strong> {{ $proforma->sucursal->telefono ?? 'S/N' }}<br>
                    <strong>Sistema Integral de Gestión Clínica y Hospitalaria</strong>
                </div>
            </td>
            <td class="doc-title-box" style="width: 40%; vertical-align: top;">
                <div class="doc-badge">RESUMEN DE CUENTA CLÍNICO</div>
                <div class="doc-number">PROFORMA #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 3px;">
                    Emisión: {{ $fechaEmision }} &bull; Estado: 
                    @if($proforma->estado === 'Pagada')
                        <span class="status-pill status-pagada">PAGADA</span>
                    @elseif($proforma->estado === 'En Proceso')
                        <span class="status-pill status-proceso">EN PROCESO</span>
                    @elseif($proforma->estado === 'Confirmada')
                        <span class="status-pill status-confirmada">CONFIRMADA</span>
                    @else
                        <span class="status-pill status-borrador">{{ strtoupper($proforma->estado) }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <!-- 1. Información del Paciente y Admisión (Mantenida idéntica al original) -->
    <div class="section-title">1. Información del Paciente y Admisión</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Paciente:</td>
            <td class="info-value font-bold">{{ $proforma->paciente->nombre_completo ?? 'N/D' }}</td>
            <td class="info-label">C.I. / Documento:</td>
            <td class="info-value">{{ $proforma->paciente->cedula ?? 'N/D' }}</td>
        </tr>
        <tr>
            <td class="info-label">Fecha Nac. / Edad:</td>
            <td class="info-value">{{ $proforma->paciente->fecha_nacimiento ? $proforma->paciente->fecha_nacimiento->format('d/m/Y') : 'N/D' }} ({{ $proforma->paciente->edad ?? '-' }} años)</td>
            <td class="info-label">Convenio / Seguro:</td>
            <td class="info-value font-bold">{{ $proforma->paciente->institucion->nombre ?? 'Particular (Sin convenio)' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tipo de Atención:</td>
            <td class="info-value font-bold">{{ $proforma->tipo_atencion }} @if($proforma->pieza) (Pieza/Cama: {{ $proforma->pieza }}) @endif</td>
            <td class="info-label">Teléfono / Celular:</td>
            <td class="info-value">{{ $proforma->paciente->celular ?? 'N/D' }}</td>
        </tr>
        <tr>
            <td class="info-label">Fecha Ingreso:</td>
            <td class="info-value">{{ $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y H:i') : 'N/D' }}</td>
            <td class="info-label">Fecha Salida / Alta:</td>
            <td class="info-value font-bold">{{ $proforma->fecha_salida ? $proforma->fecha_salida->format('d/m/Y H:i') : 'En Atención / Pendiente' }}</td>
        </tr>
        <tr>
            <td class="info-label">Motivo Consulta:</td>
            <td class="info-value" colspan="3">{{ $proforma->motivo_consulta ?: 'No registrado' }}</td>
        </tr>
        <tr>
            <td class="info-label">Diagnóstico:</td>
            <td class="info-value" colspan="3"><strong>{{ $proforma->diagnostico ?: 'Pendiente de diagnóstico definitivo' }}</strong></td>
        </tr>
    </table>

    <!-- 2. Servicios Médicos Realizados (Mantenida idéntica al original) -->
    <div class="section-title">2. Servicios Médicos y Procedimientos Realizados</div>
    @if($proforma->servicios->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 45%;">Servicio / Procedimiento</th>
                    <th style="width: 20%;">Categoría</th>
                    <th style="width: 15%;">Observaciones</th>
                    <th style="width: 15%; text-align: right;">Costo Final</th>
                </tr>
            </thead>
            <tbody>
                @foreach($proforma->servicios as $index => $servicio)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="font-bold">{{ $servicio->servicio->nombre ?? 'Servicio no especificado' }}</td>
                        <td>{{ $servicio->servicio->categoria->nombre ?? '-' }}</td>
                        <td style="font-size: 9.5px; color: #64748b;">{{ $servicio->observaciones ?: '-' }}</td>
                        <td class="text-right font-bold font-mono">Bs. {{ number_format($servicio->costo_final, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #64748b; font-style: italic; margin: 4px 8px;">No se registraron servicios adicionales.</p>
    @endif

    <!-- 3. Medicamentos e Insumos Despachados por Farmacia (Resumen del Costo Final) -->
    <div class="section-title">3. Medicamentos e Insumos Despachados por Farmacia</div>
    @php
        $costoFinalFarmacia = (float) $proforma->totalDespachosFarmacia();
        $totalItemsFarmacia = $proforma->movimientosInventario->count();
    @endphp
    @if($totalItemsFarmacia > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 55%;">Concepto</th>
                    <th style="width: 20%; text-align: center;">Total Despachos</th>
                    <th style="width: 20%; text-align: right;">Costo Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">Despacho Consolidado de Medicamentos, Fármacos e Insumos Clínicos</td>
                    <td class="text-center font-bold">{{ $totalItemsFarmacia }} {{ $totalItemsFarmacia === 1 ? 'ítem entregado' : 'ítems entregados' }}</td>
                    <td class="text-right font-bold font-mono">Bs. {{ number_format($costoFinalFarmacia, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #64748b; font-style: italic; margin: 4px 8px;">No se registraron despachos de farmacia.</p>
    @endif

    <!-- 4. Consumos e Insumos Extras Hospitalarios (Resumen del Costo Final) -->
    @php
        $costoFinalExtras = (float) $proforma->consumosExtras->sum(fn($c) => $c->cantidad * $c->precio_unitario);
        $totalItemsExtras = $proforma->consumosExtras->count();
    @endphp
    @if($totalItemsExtras > 0)
        <div class="section-title">4. Consumos e Insumos Extras Hospitalarios</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 5%; text-align: center;">#</th>
                    <th style="width: 55%;">Concepto</th>
                    <th style="width: 20%; text-align: center;">Total Registros</th>
                    <th style="width: 20%; text-align: right;">Costo Final</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td class="font-bold">Consumos e Insumos Extras de Piso / Hospitalización</td>
                    <td class="text-center font-bold">{{ $totalItemsExtras }} {{ $totalItemsExtras === 1 ? 'registro' : 'registros' }}</td>
                    <td class="text-right font-bold font-mono">Bs. {{ number_format($costoFinalExtras, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endif

    <!-- Resumen Financiero y Totales (Mantenido idéntico al original) -->
    <table class="totals-table">
        <tr>
            <td style="color: #475569;">Subtotal Servicios:</td>
            <td class="text-right font-mono">Bs. {{ number_format($proforma->servicios->sum('costo_final'), 2) }}</td>
        </tr>
        <tr>
            <td style="color: #475569;">Subtotal Farmacia / Despachos:</td>
            <td class="text-right font-mono">Bs. {{ number_format($costoFinalFarmacia, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #475569;">Subtotal Consumos Extras:</td>
            <td class="text-right font-mono">Bs. {{ number_format($costoFinalExtras, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td>TOTAL CUENTA:</td>
            <td class="text-right font-mono">Bs. {{ number_format($proforma->costo_total, 2) }}</td>
        </tr>
        <tr>
            <td style="color: #16a34a; font-weight: bold;">Total Pagado / Adelantos:</td>
            <td class="text-right font-mono" style="color: #16a34a; font-weight: bold;">Bs. {{ number_format($proforma->totalPagado(), 2) }}</td>
        </tr>
        <tr>
            <td style="color: #e11d48; font-weight: bold;">Saldo Pendiente:</td>
            <td class="text-right font-mono font-bold" style="color: #e11d48;">Bs. {{ number_format($proforma->saldoPendiente(), 2) }}</td>
        </tr>
    </table>

    <!-- Bloque de Firmas -->
    <table class="signatures">
        <tr>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Firma / Sello de Administración</strong><br>
                    Centro de Salud
                </div>
            </td>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Firma del Paciente o Tutor</strong><br>
                    C.I.: {{ $proforma->paciente->cedula ?? '................................' }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
