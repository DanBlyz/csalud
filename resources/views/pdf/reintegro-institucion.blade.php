<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Reintegro por Institución - {{ $reintegro->codigo }}</title>
    <style>
        @page {
            margin: 22px 28px 25px 28px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .header-logo {
            width: 24%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 40px;
            max-width: 170px;
            object-fit: contain;
        }
        .header-title {
            width: 54%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 11px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 12px;
            font-weight: 800;
            color: #0f172a;
            margin: 3px 0 0 0;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }
        .header-box-num {
            width: 22%;
            vertical-align: top;
            text-align: right;
        }
        .box-num-container {
            display: inline-block;
            border: 1.5px solid #0f172a;
            width: 125px;
            text-align: center;
        }
        .box-num-header {
            font-weight: 800;
            font-size: 7.5px;
            background: #f1f5f9;
            border-bottom: 1.2px solid #0f172a;
            padding: 2px 0;
            text-transform: uppercase;
        }
        .box-num-value {
            font-weight: 800;
            font-size: 11px;
            font-family: monospace;
            padding: 3px 0;
            color: #0f172a;
        }

        /* Tabla de Datos Generales */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 12px;
        }
        .meta-table td {
            border: 1px solid #0f172a;
            padding: 4px 6px;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .meta-label {
            font-weight: 800;
            text-transform: uppercase;
            background-color: #f1f5f9;
            color: #1e293b;
            width: 18%;
        }
        .meta-val {
            font-weight: 600;
            color: #0f172a;
            width: 32%;
        }

        /* Títulos de Sección */
        .section-title {
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            background-color: #1e3a8a;
            color: #ffffff;
            padding: 4px 8px;
            margin-top: 8px;
            margin-bottom: 0;
            border: 1.2px solid #0f172a;
            border-bottom: none;
            letter-spacing: 0.3px;
        }

        /* Tablas de Detalle */
        .data-table {
            width: 100%;
            table-layout: fixed;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            border: 1px solid #0f172a;
            padding: 4px 5px;
            text-align: left;
        }
        .data-table td {
            border: 1px solid #0f172a;
            padding: 4px 5px;
            font-size: 8.2px;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .text-center {
            text-align: center !important;
        }
        .text-right {
            text-align: right !important;
        }
        .font-bold {
            font-weight: 800;
        }
        .font-mono {
            font-family: monospace;
        }
        .row-total td {
            background-color: #f8fafc;
            font-weight: 800;
            font-size: 8.5px;
        }

        /* Sección de Firmas al Final */
        .signatures-wrapper {
            width: 100%;
            margin-top: 48px;
            page-break-inside: avoid;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signatures-table td {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 0 25px;
        }
        .signature-line {
            border-top: 1.4px solid #0f172a;
            width: 85%;
            margin: 0 auto 4px auto;
            padding-top: 5px;
        }
        .signature-role {
            font-size: 8.5px;
            font-weight: 800;
            text-transform: uppercase;
            color: #0f172a;
        }
        .signature-name {
            font-size: 8px;
            color: #334155;
            margin-top: 2px;
        }
        .footer-note {
            margin-top: 16px;
            font-size: 7.5px;
            color: #64748b;
            text-align: right;
        }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @php
                    $logoPath = public_path('images/logo.png');
                @endphp
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo Clínica">
                @else
                    <strong style="font-size: 11px; color: #1e3a8a;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>CLÍNICA BOLIVIANA AMERICANA</h1>
                <h2>ACTA DE REINTEGRO DE MEDICAMENTOS POR INSTITUCIÓN</h2>
            </td>
            <td class="header-box-num">
                <div class="box-num-container">
                    <div class="box-num-header">CÓDIGO DE ACTA</div>
                    <div class="box-num-value">{{ $reintegro->codigo }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Datos Generales del Reintegro --}}
    <table class="meta-table">
        <tr>
            <td class="meta-label">INSTITUCIÓN / CONVENIO:</td>
            <td class="meta-val">{{ $reintegro->institucion?->nombre ?? 'S/I' }}</td>
            <td class="meta-label">FECHA DE REINTEGRO:</td>
            <td class="meta-val">{{ $reintegro->fecha_reintegro?->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">ENTREGADO POR:</td>
            <td class="meta-val">
                {{ $reintegro->persona_entrega }}
                @if ($reintegro->ci_persona_entrega)
                    (C.I.: {{ $reintegro->ci_persona_entrega }})
                @endif
            </td>
            <td class="meta-label">RECEPCIONADO POR:</td>
            <td class="meta-val">
                {{ $reintegro->usuarioCreador?->nombre_completo ?? ($reintegro->usuarioCreador?->name ?? 'Encargada de Farmacia') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">SUCURSAL / DESTINO:</td>
            <td class="meta-val">
                {{ $reintegro->sucursal?->nombre ?? '-' }}
                @if ($reintegro->seccionDestino)
                    — {{ $reintegro->seccionDestino->nombre }}
                @endif
            </td>
            <td class="meta-label">MONTO DESCONTADO:</td>
            <td class="meta-val">Bs. {{ number_format((float) $reintegro->monto_total_descontado, 2) }} ({{ $reintegro->total_unidades }} unds.)</td>
        </tr>
        @if ($reintegro->observaciones)
            <tr>
                <td class="meta-label">OBSERVACIONES:</td>
                <td class="meta-val" colspan="3">{{ $reintegro->observaciones }}</td>
            </tr>
        @endif
    </table>

    {{-- Tabla 1: Medicamentos Reintegrados (Nuevos Lotes) --}}
    <div class="section-title">1. MEDICAMENTOS E INSUMOS REINTEGRADOS (INGRESO DE NUEVOS LOTES A FARMACIA)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 34%;">MEDICAMENTO / INSUMO</th>
                <th style="width: 16%;">UNIDAD / MARCA</th>
                <th style="width: 15%;" class="text-center">NUEVO LOTE</th>
                <th style="width: 12%;" class="text-center">VENCIMIENTO</th>
                <th style="width: 9%;" class="text-center">USADO INST.</th>
                <th style="width: 9%;" class="text-center">REINTEGRADO</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reintegro->detalles as $idx => $det)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="font-bold">{{ $det->producto?->nombre ?? '-' }}</td>
                    <td>
                        {{ $det->producto?->unidad_medida ?? 'Unidad' }}
                        @if ($det->producto?->marca)
                            / {{ $det->producto->marca->nombre }}
                        @endif
                    </td>
                    <td class="text-center font-mono font-bold">{{ $det->codigo_lote }}</td>
                    <td class="text-center">{{ $det->fecha_vencimiento ? $det->fecha_vencimiento->format('d/m/Y') : 'S/F' }}</td>
                    <td class="text-center">{{ $det->cantidad_usada_institucion }}</td>
                    <td class="text-center font-bold">{{ $det->cantidad_reintegrada }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="6" class="text-right">TOTAL UNIDADES FÍSICAS REINTEGRADAS AL INVENTARIO:</td>
                <td class="text-center">{{ $reintegro->total_unidades }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tabla 2: Pacientes y Proformas de las cuales se descontaron los medicamentos usados --}}
    <div class="section-title">2. DETALLE DE PACIENTES Y PROFORMAS DESCONTADAS (POR ORDEN DE ANTIGÜEDAD)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">#</th>
                <th style="width: 12%;" class="text-center">PROFORMA / FECHA</th>
                <th style="width: 26%;">PACIENTE ATENDIDO</th>
                <th style="width: 24%;">MEDICAMENTO / INSUMO QUITADO</th>
                <th style="width: 10%;" class="text-center">ORIGEN</th>
                <th style="width: 8%;" class="text-center">CANT.</th>
                <th style="width: 8%;" class="text-right">P. UNIT.</th>
                <th style="width: 8%;" class="text-right">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reintegro->aplicaciones as $idx => $app)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">
                        <span class="font-bold">#{{ $app->proforma_id }}</span><br>
                        <span style="font-size: 7.2px; color: #475569;">
                            {{ $app->proforma?->fecha_ingreso ? $app->proforma->fecha_ingreso->format('d/m/Y') : '-' }}
                        </span>
                    </td>
                    <td>
                        <span class="font-bold">{{ $app->paciente?->nombre_completo ?? 'Paciente' }}</span><br>
                        <span style="font-size: 7.2px; color: #475569;">
                            C.I.: {{ $app->paciente?->cedula ?? 'S/C' }}
                            @if ($app->paciente?->numero_historia_clinica)
                                • HC: {{ $app->paciente->numero_historia_clinica }}
                            @endif
                        </span>
                    </td>
                    <td>{{ $app->producto?->nombre ?? '-' }}</td>
                    <td class="text-center">{{ $app->tipo_origen }}</td>
                    <td class="text-center font-bold">-{{ $app->cantidad_descontada }}</td>
                    <td class="text-right">{{ number_format((float) $app->precio_unitario, 2) }}</td>
                    <td class="text-right font-bold">{{ number_format((float) $app->subtotal_descontado, 2) }}</td>
                </tr>
            @endforeach
            <tr class="row-total">
                <td colspan="5" class="text-right">TOTAL DESCONTADO EN PROFORMAS DE PACIENTES:</td>
                <td class="text-center">-{{ $reintegro->aplicaciones->sum('cantidad_descontada') }}</td>
                <td colspan="2" class="text-right">Bs. {{ number_format((float) $reintegro->monto_total_descontado, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Firmas al pie del documento: Izquierda Encargada (Firma y Sello), Derecha Persona que Entrega --}}
    <div class="signatures-wrapper">
        <table class="signatures-table">
            <tr>
                <td>
                    <div class="signature-line">
                        <div class="signature-role">FIRMA Y SELLO — ENCARGADA DE FARMACIA</div>
                        <div class="signature-name">
                            {{ $reintegro->usuarioCreador?->nombre_completo ?? ($reintegro->usuarioCreador?->name ?? 'Responsable de Recepción') }}
                        </div>
                    </div>
                </td>
                <td>
                    <div class="signature-line">
                        <div class="signature-role">FIRMA — ENTREGADO POR (INSTITUCIÓN)</div>
                        <div class="signature-name">
                            {{ $reintegro->persona_entrega }}
                            @if ($reintegro->ci_persona_entrega)
                                — C.I.: {{ $reintegro->ci_persona_entrega }}
                            @endif
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="footer-note">
        Impreso el {{ $fechaEmision }} • Sistema Clínico y Farmacia
    </div>
</body>
</html>

