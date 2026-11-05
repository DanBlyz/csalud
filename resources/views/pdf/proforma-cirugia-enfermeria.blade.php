<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargo Quirófano - Enfermería - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
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
            color: #0f766e;
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

        /* Tabla de Insumos y Materiales Adjuntados */
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

        /* Bloque de Equipos Especiales */
        .equipos-box {
            border: 1.2px solid #0f172a;
            margin-top: 6px;
            padding: 5px 7px;
        }
        .equipos-title {
            font-weight: 800;
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 4px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 2px;
        }
        .dotted-line {
            display: inline-block;
            border-bottom: 1px dotted #0f172a;
            padding-bottom: 1px;
            padding-left: 3px;
            font-weight: bold;
            color: #0f172a;
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
                <h2>HOJA DE ENFERMERIA</h2>
            </td>
            <td class="header-box-num">
                <div class="box-num-container">
                    <div class="box-num-header">N°</div>
                    <div class="box-num-value">{{ $cirugia->numero ?: str_pad($cirugia->id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Fila Resumen de Paciente -->
    <table style="width: 100%; border-collapse: collapse; border: 1.2px solid #0f172a; margin-bottom: 4px; font-size: 8px;">
        <tr>
            <td style="padding: 3px 5px; font-weight: 800; background: #f8fafc; width: 14%;">PACIENTE:</td>
            <td style="padding: 3px 5px; font-weight: bold; width: 44%;">{{ $proforma->paciente->nombre_completo }}</td>
            <td style="padding: 3px 5px; font-weight: 800; background: #f8fafc; width: 8%;">EDAD:</td>
            <td style="padding: 3px 5px; font-weight: bold; width: 10%;">{{ $proforma->edad_paciente !== null ? "{$proforma->edad_paciente} a." : 'N/E' }}</td>
            <td style="padding: 3px 5px; font-weight: 800; background: #f8fafc; width: 10%;">N° H.C.:</td>
            <td style="padding: 3px 5px; font-weight: bold; font-family: monospace; width: 14%;">{{ $proforma->paciente->numero_historia_clinica }}</td>
        </tr>
    </table>

    <!-- Título Sección: MATERIALES E INSUMOS -->
    <div style="font-weight: 800; text-align: center; font-size: 8.5px; margin: 4px 0 2px 0; letter-spacing: 0.5px; text-transform: uppercase;">
        MATERIALES E INSUMOS QUIRÚRGICOS (ENFERMERÍA)
    </div>

    @php
        $detallesEnf = $cirugia->receta 
            ? $cirugia->receta->detalles->where('area_quirurgica', 'Enfermería')->values() 
            : collect();
        $totalEnfermeria = 0;
    @endphp

    <table class="med-table">
        <thead>
            <tr>
                <th style="width: 5%;">N°</th>
                <th style="width: 45%; text-align: left;">MATERIAL / INSUMO</th>
                <th style="width: 20%; text-align: left;">SECCIÓN ORIGEN</th>
                <th style="width: 10%;">CANTIDAD</th>
                <th style="width: 10%; text-align: right;">P. UNIT (Bs.)</th>
                <th style="width: 10%; text-align: right;">SUBTOTAL (Bs.)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($detallesEnf as $index => $det)
                @php
                    $costoUnit = (float) ($det->costo_unitario ?? 0);
                    $subtotalItem = $costoUnit * $det->cantidad;
                    $totalEnfermeria += $subtotalItem;
                @endphp
                <tr>
                    <td style="text-align: center; font-family: monospace;">{{ $index + 1 }}</td>
                    <td class="med-name">
                        {{ $det->producto->nombre ?? 'Insumo no especificado' }}
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
                        No se registraron materiales ni insumos de enfermería en este descargo quirúrgico.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if ($detallesEnf->isNotEmpty())
            <tfoot>
                <tr style="background-color: #f8fafc; font-weight: 800;">
                    <td colspan="5" style="text-align: right; text-transform: uppercase; padding: 3px 5px;">TOTAL MATERIALES E INSUMOS (Bs.):</td>
                    <td class="med-cost" style="font-size: 8.5px; color: #0f172a; font-weight: 800;">{{ number_format($totalEnfermeria, 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- EQUIPOS ESPECIALES / SOLICITUDES -->
    <div class="equipos-box">
        <div class="equipos-title">EQUIPOS ESPECIALES / SOLICITUDES:</div>
        <table style="width: 100%; border-collapse: collapse; font-size: 7.5px;">
            <tr>
                <td style="width: 28%; padding: 2px 0;">
                    TORRE DE LAPAROSCOPIA <span class="checkbox-box">{{ $cirugia->torre_laparoscopia ? 'X' : '' }}</span>
                </td>
                <td style="width: 32%; padding: 2px 0;">
                    MAQUINA ANESTESIOLOGIA <span class="checkbox-box">{{ $cirugia->maquina_anestesiologia ? 'X' : '' }}</span>
                </td>
                <td style="width: 25%; padding: 2px 0;">
                    OXIGENO CENTRAL <span class="checkbox-box">{{ $cirugia->oxigeno_central ? 'X' : '' }}</span>
                </td>
                <td style="width: 15%; padding: 2px 0;">
                    OTROS: <span class="dotted-line" style="min-width: 60px;">{{ $cirugia->otros_equipos }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 0;">
                    BOMBA DE INFUSIÓN <span class="checkbox-box">{{ $cirugia->bomba_infusion ? 'X' : '' }}</span>
                </td>
                <td style="padding: 2px 0;">
                    MONITOR MULTIPARAMETRICO <span class="checkbox-box">{{ $cirugia->monitor_multiparametrico ? 'X' : '' }}</span>
                </td>
                <td colspan="2" style="padding: 2px 0;">
                    ELECTROBISTURI <span class="checkbox-box">{{ $cirugia->electrobisturi ? 'X' : '' }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding: 2px 0;">
                    MAQUINA DE ASPIRACIÓN <span class="checkbox-box">{{ $cirugia->maquina_aspiracion ? 'X' : '' }}</span>
                </td>
                <td style="padding: 2px 0;">
                    ECOGRAFO <span class="checkbox-box">{{ $cirugia->ecografo ? 'X' : '' }}</span>
                </td>
                <td colspan="2" style="padding: 2px 0;">
                    PATOLOGIA: <span class="dotted-line" style="min-width: 130px;">{{ $cirugia->patologia }}</span>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 2px 0;">
                    USO MATERIAL - IMPORTADORA <span class="checkbox-box">{{ $cirugia->material_importadora ? 'X' : '' }}</span>
                    <span class="dotted-line" style="min-width: 160px;">{{ $cirugia->material_importadora }}</span>
                </td>
                <td colspan="2" style="padding: 2px 0;">
                    INTENSIFICADOR DE IMÁGENES <span class="checkbox-box">{{ $cirugia->intensificador_imagenes ? 'X' : '' }}</span>
                    &nbsp;&nbsp;RAYOS X <span class="checkbox-box">{{ $cirugia->rayos_x ? 'X' : '' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Pie de Firmas -->
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
