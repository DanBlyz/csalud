<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Epicrisis - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 18px 24px 18px 24px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        .header-logo {
            width: 28%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 42px;
            max-width: 180px;
            object-fit: contain;
        }
        .header-title {
            width: 72%;
            vertical-align: middle;
            text-align: center;
            padding-right: 35%;
        }
        .header-title h1 {
            font-size: 16px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        /* Tabla de Filiación y Datos de Ingreso/Egreso */
        .patient-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 3px;
        }
        .patient-table td {
            border: 1.2px solid #0f172a;
            padding: 2.5px 5px;
            font-size: 8.5px;
            vertical-align: middle;
        }
        .label {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 8.5px;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .value {
            font-weight: 600;
            color: #0f172a;
        }

        /* Secciones tipo caja con borde y líneas rayadas */
        .box-section {
            width: 100%;
            border: 1.2px solid #0f172a;
            margin-bottom: 3px;
            page-break-inside: avoid;
        }
        .box-section-header {
            font-weight: 800;
            font-size: 8.5px;
            text-transform: uppercase;
            background-color: #f8fafc;
            padding: 2px 6px;
            border-bottom: 1.2px solid #0f172a;
            display: inline-block;
        }
        .box-section-header-full {
            font-weight: 800;
            font-size: 8.5px;
            text-transform: uppercase;
            background-color: #f8fafc;
            padding: 2px 6px;
            border-bottom: 1.2px solid #0f172a;
            width: 100%;
            box-sizing: border-box;
        }
        .box-section-body {
            padding: 3px 6px;
            min-height: 28px;
            font-size: 9px;
            line-height: 1.35;
        }
        .ruled-line {
            border-bottom: 1px dotted #cbd5e1;
            height: 13px;
            width: 100%;
        }

        /* Tablas compactas en fila */
        .row-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 3px;
        }
        .row-table td {
            border: 1.2px solid #0f172a;
            padding: 3px 6px;
            font-size: 8.5px;
        }

        /* Firma */
        .signature-section {
            margin-top: 35px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-line {
            display: inline-block;
            width: 220px;
            border-top: 1.2px solid #0f172a;
            padding-top: 3px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

    @php
        $logoPath = public_path('img/logo_clinica.png');
        $paciente = $proforma->paciente;
        $epicrisis = $epicrisis ?? $proforma->epicrisis;
        $edadAlIngreso = $proforma->edad_paciente;
    @endphp

    <!-- ENCABEZADO INSTITUCIONAL -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if (file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo Clínica">
                @else
                    <div style="font-weight: bold; font-size: 11px; color: #1e3a8a;">
                        CLÍNICA BOLIVIANA AMERICANA
                    </div>
                @endif
            </td>
            <td class="header-title">
                <h1>EPICRISIS</h1>
            </td>
        </tr>
    </table>

    <!-- 1. DATOS DE IDENTIFICACIÓN Y ADMISIÓN -->
    <table class="patient-table">
        <tr>
            <td class="label" style="width: 12%;">PACIENTE</td>
            <td class="value" style="width: 50%;">{{ $paciente->nombre_completo ?: '—' }}</td>
            <td class="label" style="width: 8%;">SEXO</td>
            <td class="value" style="width: 12%; text-align: center;">{{ $paciente->genero ?: '—' }}</td>
            <td class="label" style="width: 8%;">EDAD</td>
            <td class="value" style="width: 10%; text-align: center;">{{ $edadAlIngreso !== null ? "{$edadAlIngreso} a." : '—' }}</td>
        </tr>
        <tr>
            <td class="label">CAMA</td>
            <td class="value">{{ $epicrisis?->cama ?: ($proforma->pieza ?: '—') }}</td>
            <td class="label">SERVICIO</td>
            <td class="value" colspan="1">{{ $epicrisis?->servicio ?: ($proforma->tipo_atencion === 'Internacion' ? 'Hospitalización' : 'Med. General') }}</td>
            <td class="label">N°HC.</td>
            <td class="value" style="font-family: monospace; font-weight: bold; text-align: center;">
                {{ $paciente->numero_historia_clinica }}
            </td>
        </tr>
        <tr>
            <td class="label">FECHA DE INGRESO</td>
            <td class="value">{{ $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y H:i') : '—' }}</td>
            <td class="label">FECHA DE EGRESO</td>
            <td class="value" style="text-align: center;">{{ $epicrisis?->fecha_egreso ? $epicrisis->fecha_egreso->format('d/m/Y H:i') : ($proforma->fecha_salida ? $proforma->fecha_salida->format('d/m/Y H:i') : '—') }}</td>
            <td class="label">N° TELÉFONO</td>
            <td class="value" style="text-align: center;">{{ $paciente->celular ?: '—' }}</td>
        </tr>
    </table>

    <!-- 2. CONDICIONES Y DIAGNÓSTICO DE INGRESO -->
    <table class="row-table">
        <tr>
            <td class="label" style="width: 25%;">CONDICIONES DE INGRESO</td>
            <td class="value" style="width: 75%;">
                {{ $epicrisis?->condiciones_ingreso ?: 'Paciente hemodinámicamente estable al momento de la admisión.' }}
            </td>
        </tr>
        <tr>
            <td class="label">DIAGNÓSTICO DE INGRESO</td>
            <td class="value">
                {{ $epicrisis?->diagnostico_ingreso ?: ($proforma->diagnostico ?: ($proforma->historiaClinica?->diagnostico ?: '—')) }}
            </td>
        </tr>
    </table>

    <!-- 3. CONDICIONES Y DIAGNÓSTICO DE EGRESO -->
    <table class="row-table">
        <tr>
            <td class="label" style="width: 25%;">CONDICIONES DE EGRESO</td>
            <td class="value" style="width: 75%;">
                {{ $epicrisis?->condiciones_egreso ?: 'Paciente afebril, hemodinámicamente estable, buena tolerancia oral, con evolución favorable.' }}
            </td>
        </tr>
        <tr>
            <td class="label">DIAGNÓSTICO DE EGRESO</td>
            <td class="value">
                {{ $epicrisis?->diagnostico_egreso ?: ($proforma->diagnostico ?: '—') }}
            </td>
        </tr>
    </table>

    <!-- 4. CAUSA DE EGRESO -->
    <table class="row-table">
        <tr>
            <td class="label" style="width: 25%;">CAUSA DE EGRESO</td>
            <td class="value" style="width: 75%; font-weight: bold; color: #047857;">
                {{ $epicrisis?->causa_egreso ?: 'Alta médica por mejoría clínica' }}
            </td>
        </tr>
    </table>

    <!-- 5. ESTUDIOS COMPLEMENTARIOS -->
    <div class="box-section">
        <div class="box-section-header">ESTUDIOS COMPLEMENTARIOS</div>
        <div class="box-section-body" style="min-height: 28px;">
            {{ $epicrisis?->estudios_complementarios ?: '' }}
            @if (empty($epicrisis?->estudios_complementarios))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 6. ANTECEDENTES RELEVANTES -->
    <div class="box-section">
        <div class="box-section-header">ANTECEDENTES RELEVANTES</div>
        <div class="box-section-body" style="min-height: 24px;">
            {{ $epicrisis?->antecedentes_relevantes ?: '' }}
            @if (empty($epicrisis?->antecedentes_relevantes))
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 7. HISTORIA MÉDICA ACTUAL -->
    <div class="box-section">
        <div class="box-section-header">HISTORIA MÉDICA ACTUAL</div>
        <div class="box-section-body" style="min-height: 38px;">
            {{ $epicrisis?->historia_medica_actual ?: '' }}
            @if (empty($epicrisis?->historia_medica_actual))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 8. PROCEDIMIENTOS -->
    <div class="box-section">
        <div class="box-section-header">PROCEDIMIENTOS</div>
        <div class="box-section-body" style="min-height: 24px;">
            {{ $epicrisis?->procedimientos ?: '' }}
            @if (empty($epicrisis?->procedimientos))
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 9. TRATAMIENTO REALIZADO -->
    <div class="box-section">
        <div class="box-section-header">TRATAMIENTO REALIZADO</div>
        <div class="box-section-body" style="min-height: 28px;">
            {{ $epicrisis?->tratamiento_realizado ?: '' }}
            @if (empty($epicrisis?->tratamiento_realizado))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 10. INDICACIONES DE ALTA -->
    <div class="box-section">
        <div class="box-section-header">INDICACIONES DE ALTA</div>
        <div class="box-section-body" style="min-height: 28px;">
            {{ $epicrisis?->indicaciones_alta ?: '' }}
            @if (empty($epicrisis?->indicaciones_alta))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 11. PRÓXIMA CITA -->
    <table class="row-table">
        <tr>
            <td class="label" style="width: 20%;">PRÓXIMA CITA</td>
            <td class="value" style="width: 80%;">
                {{ $epicrisis?->proxima_cita ?: 'Control médico programado según evolución en consulta externa.' }}
            </td>
        </tr>
    </table>

    <!-- 12. OBSERVACIONES -->
    <table class="row-table">
        <tr>
            <td class="label" style="width: 20%;">OBSERVACIONES</td>
            <td class="value" style="width: 80%;">
                {{ $epicrisis?->observaciones ?: 'Paciente se retira en condiciones estables con signos vitales dentro de parámetros normales.' }}
            </td>
        </tr>
    </table>

    <!-- 13. FIRMA Y SELLO -->
    <div class="signature-section">
        <div class="signature-line">
            FIRMA Y SELLO
            {{-- @if ($proforma->medicos->count() > 0)
                <div style="font-size: 8px; font-weight: normal; margin-top: 3px; color: #475569;">
                    Dr(a). {{ $proforma->medicos->first()->nombre_completo }}
                    @if ($proforma->medicos->first()->especialidad)
                        • {{ $proforma->medicos->first()->especialidad->nombre }}
                    @endif
                </div>
            @endif --}}
        </div>
    </div>

</body>
</html>

