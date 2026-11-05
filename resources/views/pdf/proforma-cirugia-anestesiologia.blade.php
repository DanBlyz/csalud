<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargo Quirófano - Anestesiología - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 18px 24px 18px 24px;
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
            margin-bottom: 4px;
        }
        .header-logo {
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 38px;
            max-width: 160px;
            object-fit: contain;
        }
        .header-title {
            width: 60%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 14px;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 12px;
            font-weight: 700;
            color: #1e3a8a;
            margin: 2px 0 0 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-box-num {
            width: 15%;
            vertical-align: top;
            text-align: right;
        }
        .box-num-container {
            display: inline-block;
            border: 1.5px solid #0f172a;
            width: 70px;
            text-align: center;
        }
        .box-num-header {
            font-weight: 800;
            font-size: 9px;
            background: #f1f5f9;
            border-bottom: 1.2px solid #0f172a;
            padding: 1px 0;
        }
        .box-num-value {
            font-weight: 700;
            font-size: 10px;
            padding: 3px 0;
            min-height: 14px;
            color: #1e293b;
        }

        /* Tabla de Filiación y Datos de Quirófano */
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 4px;
        }
        .grid-table td {
            border: 1.2px solid #0f172a;
            padding: 2px 4px;
            font-size: 8px;
            vertical-align: middle;
        }
        .label {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8px;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }
        .checkbox-box {
            display: inline-block;
            width: 9px;
            height: 9px;
            border: 1.2px solid #0f172a;
            text-align: center;
            line-height: 8px;
            font-size: 7px;
            font-weight: bold;
            margin-left: 2px;
            margin-right: 4px;
        }

        /* Tabla de Medicamentos e Insumos Adjuntados */
        .med-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-top: 3px;
        }
        .med-table th {
            border: 1.2px solid #0f172a;
            background-color: #f1f5f9;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5px;
            padding: 3px 4px;
            text-align: center;
        }
        .med-table td {
            border: 1px solid #0f172a;
            padding: 2.5px 4px;
            font-size: 8px;
            vertical-align: middle;
        }
        .med-name {
            font-weight: 600;
            color: #1e293b;
        }
        .med-qty {
            text-align: center;
            font-weight: bold;
            font-family: monospace;
        }
        .med-cost {
            text-align: right;
            font-family: monospace;
        }

        /* Footer y Firmas */
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
        }
        .signature-line {
            width: 60%;
            margin: 0 auto;
            border-top: 1.2px solid #0f172a;
            text-align: center;
            padding-top: 2px;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
        }
    </style>
</head>
<body>

    <!-- Membrete Oficial -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @php
                    $logoPath = public_path('img/logo_clinica.png');
                @endphp
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo Clínica">
                @else
                    <span style="font-weight: 800; font-size: 11px; color: #1e3a8a;">CLÍNICA BOLIVIANA AMERICANA</span>
                @endif
            </td>
            <td class="header-title">
                <h1>DESCARGO DE QUIROFANO</h1>
                <h2>HOJA DE ANESTESIOLOGIA</h2>
            </td>
            <td class="header-box-num">
                <div class="box-num-container">
                    <div class="box-num-header">N°</div>
                    <div class="box-num-value">{{ $cirugia->numero ?: str_pad($cirugia->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Régimen y Fecha -->
    <table class="grid-table">
        <tr>
            <td style="width: 20%;">
                <span class="label">INSTITUCIONAL:</span>
                <span class="checkbox-box">{{ $cirugia->es_institucional ? 'X' : '' }}</span>
            </td>
            <td style="width: 55%;">
                <span class="label">CONVENIO:</span>
                <span class="checkbox-box">{{ $cirugia->es_convenio ? 'X' : '' }}</span>
                <span class="val">{{ $cirugia->convenio_nombre ?: ($cirugia->es_convenio ? ($proforma->paciente->institucion->nombre ?? '') : '') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">FECHA:</span>
                <span class="val">{{ $cirugia->fecha?->format('d/m/Y') ?: now()->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>

    <!-- Filiación y Tiempos Quirúrgicos -->
    <table class="grid-table">
        <tr>
            <td style="width: 55%;" colspan="2">
                <span class="label">NOMBRE DEL PACIENTE:</span>
                <span class="val" style="font-size: 8.5px;">{{ $proforma->paciente->nombre_completo }}</span>
            </td>
            <td style="width: 15%;">
                <span class="label">EDAD:</span>
                <span class="val">{{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} a." : 'N/E' }}</span>
            </td>
            <td style="width: 30%;">
                <span class="label">ESPECIALIDAD:</span>
                <span class="val">{{ $cirugia->especialidad ?: 'Cirugía General' }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 30%;">
                <span class="label">N° H.C.:</span>
                <span class="val font-mono" style="font-weight: 800;">{{ $proforma->paciente->numero_historia_clinica }}</span>
            </td>
            <td style="width: 35%;" colspan="2">
                <span class="label">HORA INGRESO - QUIRÓFANO:</span>
                <span class="val">{{ $cirugia->hora_ingreso_quirofano ?: '--:--' }}</span>
            </td>
            <td style="width: 35%;">
                <span class="label">HORA SALIDA - QUIRÓFANO:</span>
                <span class="val">{{ $cirugia->hora_salida_quirofano ?: '--:--' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">SALA:</span>
                <span class="val">{{ $cirugia->sala ?: 'Quirófano' }}</span>
            </td>
            <td colspan="2">
                <span class="label">HORA INICIO - ANESTESIA:</span>
                <span class="val">{{ $cirugia->hora_inicio_anestesia ?: '--:--' }}</span>
            </td>
            <td>
                <span class="label">HORA FINALIZACIÓN - ANESTESIA:</span>
                <span class="val">{{ $cirugia->hora_fin_anestesia ?: '--:--' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">CAMA:</span>
                <span class="val">{{ $cirugia->cama ?: ($proforma->pieza ?: 'Recuperación') }}</span>
            </td>
            <td colspan="2">
                <span class="label">HORA INICIO - CIRUGÍA:</span>
                <span class="val">{{ $cirugia->hora_inicio_cirugia ?: '--:--' }}</span>
            </td>
            <td>
                <span class="label">HORA FINALIZACIÓN - CIRUGÍA:</span>
                <span class="val">{{ $cirugia->hora_fin_cirugia ?: '--:--' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">DIAGNÓSTICO DE OPERACIÓN:</span>
                <span class="val">{{ $cirugia->diagnostico_operacion ?: ($proforma->diagnostico ?: 'N/E') }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">CIRUGÍA EFECTUADA:</span>
                <span class="val" style="font-weight: 800;">{{ $cirugia->cirugia_efectuada ?: 'Procedimiento quirúrgico sin registrar' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                <span class="label">TIPO DE CIRUGÍA:</span>
                &nbsp;&nbsp;
                AMBULATORIA <span class="checkbox-box">{{ $cirugia->tipo_cirugia === 'Ambulatoria' ? 'X' : '' }}</span>
                &nbsp;&nbsp;
                MENOR <span class="checkbox-box">{{ $cirugia->tipo_cirugia === 'Menor' ? 'X' : '' }}</span>
                &nbsp;&nbsp;
                MEDIA <span class="checkbox-box">{{ $cirugia->tipo_cirugia === 'Media' ? 'X' : '' }}</span>
                &nbsp;&nbsp;
                MAYOR <span class="checkbox-box">{{ $cirugia->tipo_cirugia === 'Mayor' ? 'X' : '' }}</span>
                &nbsp;&nbsp;
                ESPECIAL <span class="checkbox-box">{{ $cirugia->tipo_cirugia === 'Especial' ? 'X' : '' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">TIPO DE ANESTESIA:</span>
                <span class="val">{{ $cirugia->tipo_anestesia ?: 'General Balanceada' }}</span>
            </td>
            <td colspan="2">
                <span class="label">ANALGESIA:</span>
                <span class="val">{{ $cirugia->analgesia ?: 'N/E' }}</span>
            </td>
        </tr>
    </table>

    <!-- Personal que Interviene -->
    <table class="grid-table">
        <tr>
            <td rowspan="3" style="width: 18%; background: #f8fafc;" class="label" align="center">
                PERSONAL QUE<br>INTERVIENE:
            </td>
            <td style="width: 41%;">
                <span class="label">CIRUJANO:</span>
                <span class="val">{{ $cirugia->cirujano?->nombre_completo ?? 'No asignado' }}</span>
            </td>
            <td style="width: 41%;">
                <span class="label">1ER AYUDANTE:</span>
                <span class="val">{{ $cirugia->primerAyudante?->nombre_completo ?? '----------------' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">ANESTESIÓLOGO:</span>
                <span class="val">{{ $cirugia->anestesiologo?->nombre_completo ?? 'No asignado' }}</span>
            </td>
            <td>
                <span class="label">2DO AYUDANTE:</span>
                <span class="val">{{ $cirugia->segundoAyudante?->nombre_completo ?? '----------------' }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">INSTRUMENTADOR:</span>
                <span class="val">{{ $cirugia->instrumentador?->nombre_completo ?? '----------------' }}</span>
            </td>
            <td>
                <span class="label">CIRCULANTE:</span>
                <span class="val">{{ $cirugia->circulante?->nombre_completo ?? '----------------' }}</span>
            </td>
        </tr>
    </table>

    <!-- Título Sección: MEDICAMENTOS E INSUMOS -->
    <div style="font-weight: 800; text-align: center; font-size: 8.5px; margin: 4px 0 2px 0; letter-spacing: 0.5px; text-transform: uppercase;">
        MEDICAMENTOS E INSUMOS (ANESTESIOLOGÍA)
    </div>

    @php
        $detallesAnestesia = $cirugia->receta 
            ? $cirugia->receta->detalles->where('area_quirurgica', 'Anestesiología')->values() 
            : collect();
        $totalAnestesia = 0;
    @endphp

    <table class="med-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 45%; text-align: left;">FÁRMACO / INSUMO</th>
                <th style="width: 20%; text-align: left;">SECCIÓN ORIGEN</th>
                <th style="width: 10%;">CANTIDAD</th>
                <th style="width: 10%; text-align: right;">P. UNIT (Bs.)</th>
                <th style="width: 10%; text-align: right;">SUBTOTAL (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detallesAnestesia as $index => $det)
                @php
                    $costoUnit = (float) ($det->costo_unitario ?? 0);
                    $subtotalItem = $costoUnit * $det->cantidad;
                    $totalAnestesia += $subtotalItem;
                @endphp
                <tr>
                    <td style="text-align: center; font-family: monospace;">{{ $index + 1 }}</td>
                    <td class="med-name">
                        {{ $det->producto->nombre ?? 'Producto no especificado' }}
                        @if (! empty($det->indicaciones))
                            <span style="font-size: 7px; color: #64748b; font-weight: normal; display: block;">{{ $det->indicaciones }}</span>
                        @endif
                    </td>
                    <td style="color: #475569;">{{ $det->seccion?->nombre ?? 'Farmacia / Almacén' }}</td>
                    <td class="med-qty">{{ $det->cantidad }}</td>
                    <td class="med-cost">{{ number_format($costoUnit, 2) }}</td>
                    <td class="med-cost" style="font-weight: bold;">{{ number_format($subtotalItem, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 10px; color: #64748b; font-style: italic;">
                        No se registraron medicamentos ni insumos de anestesiología en este descargo quirúrgico.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($detallesAnestesia->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: 800;">
                    <td colspan="5" style="text-align: right; text-transform: uppercase; padding: 3px 5px;">TOTAL FÁRMACOS E INSUMOS (Bs.):</td>
                    <td class="med-cost" style="font-size: 8.5px; color: #0f172a; font-weight: 800;">{{ number_format($totalAnestesia, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Pie de Firma -->
    <table class="footer-table">
        <tr>
            <td style="width: 35%; text-align: center;">
                <div class="signature-line">CIRUJANO (A)</div>
                <div style="font-size: 7.5px; color: #475569; margin-top: 1px;">{{ $cirugia->cirujano?->nombre_completo }}</div>
            </td>
            <td style="width: 30%; text-align: center;">
                <div class="signature-line">ANESTESIOLOGO (A)</div>
                <div style="font-size: 7.5px; color: #475569; margin-top: 1px;">{{ $cirugia->anestesiologo?->nombre_completo }}</div>
            </td>
            <td style="width: 35%; text-align: center;">
                <div class="signature-line">CIRCULANTE</div>
                <div style="font-size: 7.5px; color: #475569; margin-top: 1px;">{{ $cirugia->circulante?->nombre_completo }}</div>
            </td>
        </tr>
    </table>

</body>
</html>
