<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo de Caja #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}</title>
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
            border-bottom: 2px solid #059669;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .clinic-title {
            font-size: 18px;
            font-weight: bold;
            color: #047857;
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
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #047857;
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
            border-left: 3px solid #059669;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #475569;
            width: 18%;
        }
        .info-value {
            color: #0f172a;
            width: 32%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #d1fae5;
            color: #065f46;
            font-size: 10px;
            text-transform: uppercase;
            padding: 7px 8px;
            text-align: left;
            border: 1px solid #a7f3d0;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .totals-table {
            width: 48%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
        }
        .total-row {
            background-color: #f0fdf4;
            border-top: 2px solid #059669;
            font-size: 13px !important;
            font-weight: bold;
            color: #047857;
        }
        .signatures {
            margin-top: 50px;
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
        .stamp-box {
            border: 2px dashed #059669;
            border-radius: 8px;
            padding: 10px 14px;
            text-align: center;
            display: inline-block;
            background-color: #f0fdf4;
            margin-top: 10px;
        }
        .stamp-text {
            font-size: 14px;
            font-weight: 900;
            letter-spacing: 1px;
            color: #047857;
            text-transform: uppercase;
        }
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
                    <strong>Comprobante Oficial de Ingreso de Caja</strong>
                </div>
            </td>
            <td class="doc-title-box" style="width: 40%; vertical-align: top;">
                <div class="doc-badge">RECIBO DE CAJA</div>
                <div class="doc-number">COMPROBANTE #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 3px;">
                    Fecha y Hora de Impresión: {{ $fechaEmision }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Datos de la Liquidación -->
    <div class="section-title">1. Datos del Paciente y Cuenta</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Paciente:</td>
            <td class="info-value font-bold">{{ $proforma->paciente->nombre_completo ?? 'N/D' }}</td>
            <td class="info-label">C.I. / Documento:</td>
            <td class="info-value">{{ $proforma->paciente->cedula ?? 'N/D' }}</td>
        </tr>
        <tr>
            <td class="info-label">Proforma Ref.:</td>
            <td class="info-value font-bold">#{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }} ({{ $proforma->tipo_atencion }})</td>
            <td class="info-label">Fecha de Ingreso:</td>
            <td class="info-value">{{ $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y H:i') : 'N/D' }}</td>
        </tr>
        <tr>
            <td class="info-label">Sucursal:</td>
            <td class="info-value">{{ $proforma->sucursal->nombre ?? 'Sede Central' }}</td>
            <td class="info-label">Fecha de Cierre:</td>
            <td class="info-value font-bold">{{ $proforma->fecha_salida ? $proforma->fecha_salida->format('d/m/Y H:i') : 'En curso' }}</td>
        </tr>
    </table>

    <!-- Detalle de Pagos Aplicados -->
    <div class="section-title">2. Desglose de Transacciones y Métodos de Pago</div>
    @if($pagos->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 18%;">Fecha / Hora</th>
                    <th style="width: 22%;">Método de Pago</th>
                    <th style="width: 25%;">Nro. Referencia / Comprobante</th>
                    <th style="width: 20%;">Cajero Receptor</th>
                    <th style="width: 15%; text-align: right;">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pagos as $pago)
                    <tr>
                        <td>{{ $pago->created_at ? $pago->created_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            <strong>{{ $pago->tipo_pago }}</strong>
                        </td>
                        <td>
                            {{ $pago->numero_referencia ?: ($pago->tipo_pago === 'Efectivo' ? 'Pago en mostrador' : 'S/R') }}
                        </td>
                        <td>{{ $pago->user->name ?? 'Cajero' }}</td>
                        <td class="text-right font-bold font-mono">Bs. {{ number_format($pago->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #64748b; font-style: italic; margin: 4px 8px;">No se registran pagos para esta proforma.</p>
    @endif

    <!-- Totales y Resumen Contable -->
    <table style="width: 100%; margin-top: 10px;">
        <tr>
            <td style="width: 50%; vertical-align: top;">
                @if($proforma->saldoPendiente() <= 0 && $proforma->costo_total > 0)
                    <div class="stamp-box">
                        <div class="stamp-text">&#10004; CUENTA CANCELADA EN SU TOTALIDAD</div>
                        <div style="font-size: 9.5px; color: #065f46; margin-top: 2px;">Comprobante de paz y salvo administrativo</div>
                    </div>
                @else
                    <div style="border: 2px dashed #f59e0b; border-radius: 8px; padding: 10px 14px; text-align: center; display: inline-block; background-color: #fffbeb;">
                        <div style="font-size: 13px; font-weight: 900; color: #b45309; text-transform: uppercase;">ABONO PARCIAL REGISTRADO</div>
                        <div style="font-size: 9.5px; color: #92400e; margin-top: 2px;">Saldo pendiente por liquidar en caja</div>
                    </div>
                @endif
            </td>
            <td style="width: 50%; vertical-align: top;">
                <table class="totals-table" style="width: 100%;">
                    <tr>
                        <td style="color: #475569;">Costo Total Proforma:</td>
                        <td class="text-right font-mono">Bs. {{ number_format($proforma->costo_total, 2) }}</td>
                    </tr>
                    <tr class="total-row">
                        <td>TOTAL ABONADO / RECIBIDO:</td>
                        <td class="text-right font-mono">Bs. {{ number_format($proforma->totalPagado(), 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: {{ $proforma->saldoPendiente() > 0 ? '#e11d48' : '#16a34a' }}; font-weight: bold;">Saldo Pendiente:</td>
                        <td class="text-right font-mono font-bold" style="color: {{ $proforma->saldoPendiente() > 0 ? '#e11d48' : '#16a34a' }};">
                            Bs. {{ number_format($proforma->saldoPendiente(), 2) }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Firmas -->
    <table class="signatures">
        <tr>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Firma y Sello de Cajero / Recaudaciones</strong><br>
                    {{ $pagos->last()->user->name ?? 'Cajero Responsable' }}
                </div>
            </td>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Firma del Paciente / Pagador</strong><br>
                    C.I.: {{ $proforma->paciente->cedula ?? '................................' }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
