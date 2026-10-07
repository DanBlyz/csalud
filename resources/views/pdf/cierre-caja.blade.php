<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Cierre de Caja #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}</title>
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
            width: 20%;
        }
        .info-value {
            color: #0f172a;
            width: 30%;
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
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        .total-row {
            background-color: #f0fdf4;
            border-top: 2px solid #059669;
            font-size: 12px !important;
            font-weight: bold;
            color: #047857;
        }
        .diff-box {
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 12px;
            margin-top: 10px;
        }
        .diff-ok {
            border-color: #10b981;
            background-color: #ecfdf5;
            color: #065f46;
        }
        .diff-sobrante {
            border-color: #3b82f6;
            background-color: #eff6ff;
            color: #1e40af;
        }
        .diff-faltante {
            border-color: #ef4444;
            background-color: #fef2f2;
            color: #991b1b;
        }
        .signatures {
            margin-top: 45px;
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
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="clinic-title">Centro de Salud</div>
                <div class="clinic-subtitle">
                    <strong>Sucursal:</strong> {{ $caja->sucursal->nombre ?? 'Principal' }}<br>
                    <strong>Dirección:</strong> {{ $caja->sucursal->direccion ?? 'Av. Principal #123' }} &bull; 
                    <strong>Teléfono:</strong> {{ $caja->sucursal->telefono ?? 'S/N' }}<br>
                    <strong>Acta Oficial de Cierre y Arqueo de Caja</strong>
                </div>
            </td>
            <td class="doc-title-box" style="width: 40%; vertical-align: top;">
                <div class="doc-badge">ARQUEO DE CAJA</div>
                <div class="doc-number">TURNO #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 3px;">
                    Emisión: {{ $fechaEmision }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Datos del Turno -->
    <div class="section-title">1. Datos Generales de la Sesión</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Cajero Responsable:</td>
            <td class="info-value font-bold">{{ $caja->user->name ?? 'N/D' }}</td>
            <td class="info-label">Estado de Caja:</td>
            <td class="info-value font-bold" style="color: {{ $caja->estado === 'Abierta' ? '#10b981' : '#64748b' }};">
                {{ $caja->estado }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Fecha / Hora Apertura:</td>
            <td class="info-value">{{ $caja->fecha_apertura ? $caja->fecha_apertura->format('d/m/Y H:i') : 'N/D' }}</td>
            <td class="info-label">Fecha / Hora Cierre:</td>
            <td class="info-value">{{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'En curso' }}</td>
        </tr>
        <tr>
            <td class="info-label">Fondo Inicial de Apertura:</td>
            <td class="info-value font-bold font-mono">Bs. {{ number_format($caja->monto_apertura, 2) }}</td>
            <td class="info-label">Sucursal:</td>
            <td class="info-value">{{ $caja->sucursal->nombre ?? 'Central' }}</td>
        </tr>
    </table>

    <!-- Resumen del Arqueo y Balance -->
    <div class="section-title">2. Balance Contable y Arqueo de Caja</div>
    <table class="totals-table">
        <tr>
            <td style="width: 50%; font-weight: bold; background-color: #f8fafc;">Concepto</td>
            <td style="width: 25%; font-weight: bold; text-align: right; background-color: #f8fafc;">Esperado (Sistema)</td>
            <td style="width: 25%; font-weight: bold; text-align: right; background-color: #f8fafc;">Declarado (Físico)</td>
        </tr>
        <tr>
            <td>(+) Fondo de Apertura (Cambio)</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->monto_apertura, 2) }}</td>
            <td class="text-right font-mono">-</td>
        </tr>
        <tr>
            <td>(+) Ingresos en Efectivo (Proformas + Extras)</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->total_ingresos_efectivo ?? $caja->totalIngresosEfectivo(), 2) }}</td>
            <td class="text-right font-mono">-</td>
        </tr>
        <tr>
            <td>(-) Egresos / Salidas de Caja</td>
            <td class="text-right font-mono" style="color: #ef4444;">- Bs. {{ number_format($caja->total_egresos_efectivo ?? $caja->totalEgresosEfectivo(), 2) }}</td>
            <td class="text-right font-mono">-</td>
        </tr>
        <tr class="total-row">
            <td><strong>TOTAL EFECTIVO EN GAVETA</strong></td>
            <td class="text-right font-mono font-bold">Bs. {{ number_format($caja->saldo_esperado_efectivo ?? $caja->saldoEsperadoEfectivo(), 2) }}</td>
            <td class="text-right font-mono font-bold">Bs. {{ number_format($caja->monto_cierre_efectivo ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Ingresos / Saldo Código QR</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->saldo_esperado_qr ?? $caja->saldoEsperadoQr(), 2) }}</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->monto_cierre_qr ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>Ingresos / Saldo Transferencia Bancaria</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->saldo_esperado_transferencia ?? $caja->saldoEsperadoTransferencia(), 2) }}</td>
            <td class="text-right font-mono">Bs. {{ number_format($caja->monto_cierre_transferencia ?? 0, 2) }}</td>
        </tr>
        <tr class="total-row" style="background-color: #f1f5f9; border-top: 1px solid #cbd5e1; color: #0f172a;">
            <td><strong>TOTAL CONSOLIDADO (EFECTIVO + QR + TRANSFERENCIA)</strong></td>
            <td class="text-right font-mono font-bold">Bs. {{ number_format(($caja->saldo_esperado_efectivo ?? $caja->saldoEsperadoEfectivo()) + ($caja->saldo_esperado_qr ?? $caja->saldoEsperadoQr()) + ($caja->saldo_esperado_transferencia ?? $caja->saldoEsperadoTransferencia()), 2) }}</td>
            <td class="text-right font-mono font-bold">Bs. {{ number_format(($caja->monto_cierre_efectivo ?? 0) + ($caja->monto_cierre_qr ?? 0) + ($caja->monto_cierre_transferencia ?? 0), 2) }}</td>
        </tr>
    </table>

    <!-- Resultado de la Discrepancia por Canal y Consolidado -->
    @php
        $difEf = $caja->diferenciaEfectivoCalculada();
        $difQr = $caja->diferenciaQrCalculada();
        $difTr = $caja->diferenciaTransferenciaCalculada();
        $difTotal = $difEf + $difQr + $difTr;
    @endphp
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tr>
            <td style="width: 32%; vertical-align: top; padding-right: 5px;">
                <div class="diff-box {{ round($difEf, 2) == 0 ? 'diff-ok' : ($difEf > 0 ? 'diff-sobrante' : 'diff-faltante') }}" style="margin-top: 0;">
                    <strong style="display: block; font-size: 10px; text-transform: uppercase;">1. Efectivo en Gaveta:</strong>
                    @if(round($difEf, 2) == 0)
                        <span>Cuadrado (Bs. 0.00)</span>
                    @elseif($difEf > 0)
                        <span>Sobrante: +Bs. {{ number_format($difEf, 2) }}</span>
                    @else
                        <span>Faltante: -Bs. {{ number_format(abs($difEf), 2) }}</span>
                    @endif
                </div>
            </td>
            <td style="width: 32%; vertical-align: top; padding-right: 5px; padding-left: 5px;">
                <div class="diff-box {{ round($difQr, 2) == 0 ? 'diff-ok' : ($difQr > 0 ? 'diff-sobrante' : 'diff-faltante') }}" style="margin-top: 0;">
                    <strong style="display: block; font-size: 10px; text-transform: uppercase;">2. Pagos Código QR:</strong>
                    @if(round($difQr, 2) == 0)
                        <span>Cuadrado (Bs. 0.00)</span>
                    @elseif($difQr > 0)
                        <span>Sobrante: +Bs. {{ number_format($difQr, 2) }}</span>
                    @else
                        <span>Faltante: -Bs. {{ number_format(abs($difQr), 2) }}</span>
                    @endif
                </div>
            </td>
            <td style="width: 36%; vertical-align: top; padding-left: 5px;">
                <div class="diff-box {{ round($difTr, 2) == 0 ? 'diff-ok' : ($difTr > 0 ? 'diff-sobrante' : 'diff-faltante') }}" style="margin-top: 0;">
                    <strong style="display: block; font-size: 10px; text-transform: uppercase;">3. Transferencias Bancarias:</strong>
                    @if(round($difTr, 2) == 0)
                        <span>Cuadrado (Bs. 0.00)</span>
                    @elseif($difTr > 0)
                        <span>Sobrante: +Bs. {{ number_format($difTr, 2) }}</span>
                    @else
                        <span>Faltante: -Bs. {{ number_format(abs($difTr), 2) }}</span>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <div class="diff-box {{ round($difTotal, 2) == 0 ? 'diff-ok' : ($difTotal > 0 ? 'diff-sobrante' : 'diff-faltante') }}" style="margin-top: 8px;">
        <strong>Balance General del Turno: </strong>
        @if(round($difTotal, 2) == 0)
            <span>CUADRADO TOTAL (Diferencia neta: Bs. 0.00)</span>
        @elseif($difTotal > 0)
            <span>SOBRANTE GLOBAL: + Bs. {{ number_format($difTotal, 2) }}</span>
        @else
            <span>FALTANTE GLOBAL: - Bs. {{ number_format(abs($difTotal), 2) }}</span>
        @endif
        @if($caja->observaciones_cierre)
            <div style="margin-top: 4px; font-size: 10px; font-style: italic;">
                <strong>Observaciones de Cierre:</strong> {{ $caja->observaciones_cierre }}
            </div>
        @endif
    </div>

    <!-- Detalle de Movimientos del Turno -->
    <div class="section-title">3. Detalle de Transacciones Registradas ({{ $caja->pagos->count() }} movimientos)</div>
    @if($caja->pagos->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 14%;">Hora</th>
                    <th style="width: 18%;">Tipo / Categoría</th>
                    <th style="width: 32%;">Concepto / Detalle</th>
                    <th style="width: 16%;">Método / Ref.</th>
                    <th style="width: 20%; text-align: right;">Monto (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($caja->pagos as $p)
                    <tr>
                        <td>{{ $p->created_at ? $p->created_at->format('H:i') : '-' }}</td>
                        <td>
                            <strong>{{ $p->tipo_movimiento }}</strong><br>
                            <span style="font-size: 9px; color: #64748b;">{{ $p->categoria }}</span>
                        </td>
                        <td>{{ $p->concepto }}</td>
                        <td>
                            {{ $p->tipo_pago }}
                            @if($p->numero_referencia)
                                <br><span style="font-size: 9px; color: #64748b;">Ref: {{ $p->numero_referencia }}</span>
                            @endif
                        </td>
                        <td class="text-right font-mono font-bold" style="color: {{ $p->tipo_movimiento === 'Egreso Caja' ? '#ef4444' : '#059669' }};">
                            {{ $p->tipo_movimiento === 'Egreso Caja' ? '-' : '+' }} Bs. {{ number_format($p->monto, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="font-size: 10px; color: #64748b; font-style: italic;">No se registraron movimientos en este turno.</p>
    @endif

    <!-- Firmas de Conformidad -->
    <table class="signatures">
        <tr>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>{{ $caja->user->name ?? 'Cajero' }}</strong><br>
                    Firma y Sello de Cajero Responsable
                </div>
            </td>
            <td class="signature-box">
                <br><br><br>
                <div class="signature-line">
                    <strong>Administración / Control Interno</strong><br>
                    Firma de Recepción y Conformidad
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
