<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historia Clínica - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 18px 24px 18px 24px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 9.5px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }
        .header-logo {
            width: 28%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 44px;
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

        /* Tablas y Cajas Clínicas */
        .clinical-box {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 4px;
        }
        .clinical-box th, .clinical-box td {
            border: 1.2px solid #0f172a;
            padding: 3px 5px;
            font-size: 9px;
            vertical-align: middle;
        }
        .box-header {
            background-color: #f8fafc;
            font-weight: 800;
            font-size: 9.5px;
            text-transform: uppercase;
            text-align: center;
            padding: 3px 5px;
            border-bottom: 1.2px solid #0f172a;
        }
        .box-header-inline {
            font-weight: 800;
            font-size: 9px;
            text-transform: uppercase;
            background-color: #f8fafc;
            width: 15%;
            text-align: center;
        }
        .label-cell {
            font-weight: 700;
            font-size: 8.5px;
            text-transform: uppercase;
            background-color: #f1f5f9;
            color: #334155;
            text-align: center;
        }
        .value-cell {
            font-size: 9px;
            color: #0f172a;
            font-weight: 600;
            text-align: center;
        }
        .text-section-box {
            width: 100%;
            border: 1.2px solid #0f172a;
            margin-bottom: 4px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f8fafc;
            border-bottom: 1.2px solid #0f172a;
            padding: 3px 6px;
            font-size: 9.5px;
            font-weight: 800;
            text-transform: uppercase;
            text-align: center;
            letter-spacing: 0.3px;
        }
        .section-body {
            padding: 4px 6px;
            min-height: 38px;
            font-size: 9.5px;
            line-height: 1.4;
        }
        .ruled-line {
            border-bottom: 1px dotted #cbd5e1;
            height: 14px;
            width: 100%;
        }

        /* Ginecológicos */
        .gineco-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 4px;
        }
        .gineco-table th, .gineco-table td {
            border: 1.2px solid #0f172a;
            padding: 3px 4px;
            text-align: center;
            font-size: 8.5px;
        }

        /* Signos Vitales */
        .vitals-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 1.2px solid #0f172a;
        }
        .vitals-table th {
            border-right: 1.2px solid #0f172a;
            border-bottom: 1.2px solid #0f172a;
            padding: 2.5px 3px;
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            background-color: #f8fafc;
            text-align: center;
        }
        .vitals-table td {
            border-right: 1.2px solid #0f172a;
            padding: 3px;
            text-align: center;
            font-size: 9px;
            font-weight: 600;
            font-family: monospace;
        }
        .vitals-table th:last-child, .vitals-table td:last-child {
            border-right: none;
        }

        /* Firma */
        .signature-section {
            margin-top: 20px;
            text-align: center;
            page-break-inside: avoid;
        }
        .signature-line {
            display: inline-block;
            width: 220px;
            border-top: 1.2px solid #0f172a;
            padding-top: 3px;
            font-size: 9.5px;
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
        $historia = $historia ?? $proforma->historiaClinica;
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
                <h1>HISTORIA CLÍNICA</h1>
            </td>
        </tr>
    </table>

    <!-- 1. FILIACIÓN -->
    <table class="clinical-box">
        <tr>
            <td colspan="6" class="box-header">FILIACIÓN</td>
        </tr>
        <tr>
            <td class="label-cell" style="width: 20%;">APELLIDO PATERNO</td>
            <td class="label-cell" style="width: 20%;">APELLIDO MATERNO</td>
            <td class="label-cell" style="width: 25%;">NOMBRES</td>
            <td class="label-cell" style="width: 10%;">EDAD</td>
            <td class="label-cell" style="width: 15%;">OCUPACIÓN</td>
            <td class="label-cell" style="width: 10%;">FECHA</td>
        </tr>
        <tr>
            <td class="value-cell">{{ $paciente->apellido_paterno ?: '—' }}</td>
            <td class="value-cell">{{ $paciente->apellido_materno ?: '—' }}</td>
            <td class="value-cell">{{ $paciente->nombres ?: '—' }}</td>
            <td class="value-cell">{{ $edadAlIngreso !== null ? "{$edadAlIngreso} años" : '—' }}</td>
            <td class="value-cell">{{ $paciente->ocupacion ?: '—' }}</td>
            <td class="value-cell">{{ $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : now()->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="label-cell">FECHA - NACIMIENTO</td>
            <td class="label-cell">ESTADO CIVIL</td>
            <td class="label-cell">PROCEDENCIA</td>
            <td class="label-cell">SEXO</td>
            <td colspan="2" class="label-cell">RESIDENCIA</td>
        </tr>
        <tr>
            <td class="value-cell">{{ $paciente->fecha_nacimiento ? $paciente->fecha_nacimiento->format('d/m/Y') : '—' }}</td>
            <td class="value-cell">{{ $paciente->estado_civil ?: '—' }}</td>
            <td class="value-cell">{{ $historia?->procedencia ?: '—' }}</td>
            <td class="value-cell">{{ $paciente->genero ?: '—' }}</td>
            <td colspan="2" class="value-cell">{{ $historia?->residencia ?: ($paciente->direccion ?: '—') }}</td>
        </tr>
        <tr>
            <td colspan="2" class="label-cell" style="text-align: left; padding-left: 8px;">FUENTE DE INFORMACIÓN:</td>
            <td colspan="4" class="value-cell" style="text-align: left; padding-left: 8px;">
                {{ $historia?->fuente_informacion ?: 'PACIENTE' }}
            </td>
        </tr>
    </table>

    <!-- 2. MOTIVO DE CONSULTA -->
    <div class="text-section-box">
        <div class="section-title">MOTIVO DE CONSULTA</div>
        <div class="section-body">
            {{ $historia?->motivo_consulta ?: ($proforma->motivo_consulta ?: '') }}
            @if (empty($historia?->motivo_consulta) && empty($proforma->motivo_consulta))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 3. HISTORIA DE LA ENFERMEDAD ACTUAL -->
    <div class="text-section-box">
        <div class="section-title">HISTORIA DE LA ENFERMEDAD ACTUAL</div>
        <div class="section-body" style="min-height: 48px;">
            {{ $historia?->enfermedad_actual ?: '' }}
            @if (empty($historia?->enfermedad_actual))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 4. ANTECEDENTES PERSONALES PATOLOGICOS -->
    <div class="text-section-box">
        <div class="section-title">ANTECEDENTES PERSONALES PATOLOGICOS</div>
        <div class="section-body">
            {{ $historia?->antecedentes_patologicos ?: ($paciente->antecedentes_alergias ? 'Alergias: '.$paciente->antecedentes_alergias : '') }}
            @if (empty($historia?->antecedentes_patologicos) && empty($paciente->antecedentes_alergias))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 5. ANTECEDENTES PERSONALES NO PATOLOGICOS -->
    <div class="text-section-box">
        <div class="section-title">ANTECEDENTES PERSONALES NO PATOLOGICOS</div>
        <div class="section-body">
            {{ $historia?->antecedentes_no_patologicos ?: '' }}
            @if (empty($historia?->antecedentes_no_patologicos))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 6. ANTECEDENTES GINECOLÓGICOS -->
    <table class="gineco-table">
        <tr>
            <th style="width: 26%; font-weight: 800; background-color: #f8fafc;">ANTECEDENTES GINECOLÓGICOS</th>
            <th style="width: 11%;">MENARCA</th>
            <th style="width: 11%;">FUM</th>
            <th style="width: 7%;">G</th>
            <th style="width: 7%;">P</th>
            <th style="width: 7%;">C</th>
            <th style="width: 7%;">A</th>
            <th style="width: 12%;">PAP</th>
            <th style="width: 12%;">OTROS</th>
        </tr>
        <tr>
            <td style="font-weight: bold; background-color: #f8fafc; color: #475569;">FÓRMULA / REGISTRO</td>
            <td>{{ $historia?->menarca ?: '—' }}</td>
            <td>{{ $historia?->fum ?: '—' }}</td>
            <td>{{ $historia?->gestas ?: '—' }}</td>
            <td>{{ $historia?->partos ?: '—' }}</td>
            <td>{{ $historia?->cesareas ?: '—' }}</td>
            <td>{{ $historia?->abortos ?: '—' }}</td>
            <td>{{ $historia?->pap ?: '—' }}</td>
            <td>{{ $historia?->gineco_otros ?: '—' }}</td>
        </tr>
    </table>

    <!-- 7. EXAMEN FÍSICO -->
    <div class="text-section-box">
        <div class="section-title">EXAMEN FÍSICO</div>
        <!-- Fila de Signos Vitales -->
        <table class="vitals-table">
            <tr>
                <th style="width: 13%;">PA(mmHg)</th>
                <th style="width: 12%;">FC(lpm)</th>
                <th style="width: 12%;">FR(rpm)</th>
                <th style="width: 12%;">T(C°)</th>
                <th style="width: 13%;">SatO2%</th>
                <th style="width: 12%;">P(kg)</th>
                <th style="width: 12%;">T(cm)</th>
                <th style="width: 14%;">IMC</th>
            </tr>
            <tr>
                <td>{{ $historia?->pa ?: '—' }}</td>
                <td>{{ $historia?->fc ?: '—' }}</td>
                <td>{{ $historia?->fr ?: '—' }}</td>
                <td>{{ $historia?->temperatura ?: '—' }}</td>
                <td>{{ $historia?->sato2 ?: '—' }}</td>
                <td>{{ $historia?->peso ?: '—' }}</td>
                <td>{{ $historia?->talla ?: '—' }}</td>
                <td style="font-weight: bold; color: #0f766e;">{{ $historia?->imc ?: '—' }}</td>
            </tr>
        </table>
        <!-- Descripción Examen Físico -->
        <div class="section-body" style="min-height: 55px;">
            {{ $historia?->examen_fisico ?: '' }}
            @if (empty($historia?->examen_fisico))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 8. DIAGNÓSTICO -->
    <div class="text-section-box">
        <div class="section-title" style="text-align: left; padding-left: 8px;">DIAGNÓSTICO</div>
        <div class="section-body">
            {{ $historia?->diagnostico ?: ($proforma->diagnostico ?: '') }}
            @if (empty($historia?->diagnostico) && empty($proforma->diagnostico))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 9. CONDUCTA -->
    <div class="text-section-box">
        <div class="section-title" style="text-align: left; padding-left: 8px;">CONDUCTA</div>
        <div class="section-body">
            {{ $historia?->conducta ?: '' }}
            @if (empty($historia?->conducta))
                <div class="ruled-line"></div>
                <div class="ruled-line"></div>
            @endif
        </div>
    </div>

    <!-- 10. FIRMA Y SELLO -->
    <div class="signature-section">
        <div class="signature-line">
            FIRMA Y SELLO
            {{-- @if ($proforma->medicos->count() > 0)
                <div style="font-size: 8.5px; font-weight: normal; margin-top: 3px; color: #475569;">
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

