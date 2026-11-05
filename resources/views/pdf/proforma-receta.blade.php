<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica #{{ str_pad($receta->id, 5, '0', STR_PAD_LEFT) }} - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 25px 32px 30px 32px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 8px;
        }
        .header-logo {
            width: 30%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 48px;
            max-width: 170px;
            object-fit: contain;
        }
        .header-center {
            width: 40%;
            vertical-align: middle;
            text-align: center;
        }
        .header-center h1 {
            font-size: 19px;
            font-weight: 800;
            color: #0f766e;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }
        .header-center p {
            margin: 2px 0 0 0;
            font-size: 9.5px;
            color: #64748b;
        }
        .header-folio {
            width: 30%;
            vertical-align: middle;
            text-align: right;
            font-size: 10px;
        }
        .folio-badge {
            background-color: #f0fdfa;
            border: 1px solid #99f6e4;
            color: #0f766e;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 3px;
        }
        .folio-num {
            font-size: 13px;
            font-weight: bold;
            color: #0f172a;
        }

        /* Recuadro de Datos del Paciente */
        .patient-box-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f172a;
            margin-bottom: 12px;
            background-color: #ffffff;
        }
        .patient-box-table td {
            padding: 5px 8px;
            font-size: 10.5px;
            border-right: 1px solid #cbd5e1;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        .patient-box-table tr:last-child td {
            border-bottom: none;
        }
        .patient-box-table td:last-child {
            border-right: none;
        }
        .label-text {
            font-weight: 700;
            color: #334155;
            font-size: 9.5px;
            text-transform: uppercase;
        }
        .value-text {
            font-weight: 600;
            color: #0f172a;
        }

        /* Tabla de Medicamentos */
        .section-header {
            background-color: #0f766e;
            color: #ffffff;
            padding: 5px 10px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0px;
            border-top-left-radius: 4px;
            border-top-right-radius: 4px;
        }
        .med-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #0f766e;
            margin-bottom: 14px;
        }
        .med-table th {
            background-color: #f0fdfa;
            color: #134e4a;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 8px;
            border-bottom: 1.5px solid #0f766e;
            border-right: 1px solid #ccfbf1;
        }
        .med-table th:last-child {
            border-right: none;
        }
        .med-table td {
            padding: 7px 8px;
            border-bottom: 1px solid #e2e8f0;
            border-right: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 10.5px;
        }
        .med-table td:last-child {
            border-right: none;
        }
        .med-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .med-nombre {
            font-weight: bold;
            color: #0f172a;
            font-size: 11px;
        }
        .med-sub {
            font-size: 9px;
            color: #64748b;
            margin-top: 1px;
        }
        .med-cantidad {
            text-align: center;
            font-family: monospace;
            font-weight: bold;
            font-size: 12px;
            color: #0f766e;
        }
        .med-posologia {
            color: #1e293b;
            line-height: 1.35;
        }

        /* Observaciones Generales */
        .obs-box {
            padding: 8px 10px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #0f766e;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 10px;
            color: #334155;
        }
        .obs-title {
            font-weight: bold;
            color: #0f766e;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 2px;
        }

        /* Sección de Firma */
        .signature-section {
            margin-top: 55px;
            text-align: center;
            width: 100%;
        }
        .signature-container {
            display: inline-block;
            width: 270px;
            margin: 0 auto;
            text-align: center;
        }
        .signature-line {
            width: 100%;
            border-top: 1.5px solid #0f172a;
            margin-bottom: 6px;
        }
        .signature-title {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .signature-doctor {
            font-size: 10px;
            color: #334155;
            margin-top: 2px;
            font-weight: 600;
        }
        .signature-esp {
            font-size: 9px;
            color: #64748b;
        }

        /* Footer */
        .footer-note {
            margin-top: 40px;
            border-top: 1px dotted #cbd5e1;
            padding-top: 5px;
            font-size: 8px;
            color: #94a3b8;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            text-align: left;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
        }
    </style>
</head>
<body>

    <!-- Encabezado con Logo, Título y Folio -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if(file_exists(public_path('img/logo_clinica.png')))
                    <img src="{{ public_path('img/logo_clinica.png') }}" alt="Logo Clínica">
                @else
                    <strong style="color: #0f766e; font-size: 14px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-center">
                <h1>RECETA MÉDICA</h1>
                <p>{{ $proforma->sucursal->nombre ?? 'Sede Central' }} • Servicio de Admisión y Hospitalización</p>
            </td>
            <td class="header-folio">
                <span class="folio-badge">{{ $receta->activo ? 'RECETA VIGENTE' : 'RECETA HISTÓRICA' }}</span>
                <div class="folio-num">Receta N°: #{{ str_pad($receta->id, 5, '0', STR_PAD_LEFT) }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 2px;">
                    Proforma #{{ str_pad($proforma->id, 5, '0', STR_PAD_LEFT) }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Datos Generales del Paciente y Prescriptor -->
    <table class="patient-box-table">
        <tr>
            <td style="width: 50%;">
                <span class="label-text">Paciente:</span><br>
                <span class="value-text" style="font-size: 11.5px;">{{ strtoupper($proforma->paciente->nombre_completo ?? 'N/E') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label-text">Edad:</span><br>
                <span class="value-text">
                    {{ $proforma->edad_paciente !== null ? $proforma->edad_paciente.' AÑOS' : ($proforma->paciente->edadEnFecha($receta->created_at) !== null ? $proforma->paciente->edadEnFecha($receta->created_at).' AÑOS' : 'S/E') }}
                </span>
            </td>
            <td style="width: 25%;">
                <span class="label-text">N° H.C.:</span><br>
                <span class="value-text" style="font-family: monospace; font-size: 11px;">
                    {{ $proforma->paciente->numero_historia_clinica ?? 'S/N' }}
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label-text">Cédula / Documento:</span><br>
                <span class="value-text">{{ $proforma->paciente->cedula ?? 'S/D' }}</span>
                @if($proforma->paciente->genero)
                    <span style="color: #64748b; font-size: 9.5px;">({{ $proforma->paciente->genero }})</span>
                @endif
            </td>
            <td>
                <span class="label-text">Fecha y Hora:</span><br>
                <span class="value-text">{{ $receta->created_at ? $receta->created_at->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
            </td>
            <td>
                <span class="label-text">Prescrito Por:</span><br>
                <span class="value-text">
                    Dr(a). {{ $receta->doctor->nombre_completo ?? $receta->doctor->name ?? 'Médico Tratante' }}
                </span>
            </td>
        </tr>
    </table>

    <!-- Tabla de Medicamentos Prescritos -->
    <div class="section-header">
        Fármacos y Esquema Terapéutico Prescrito
    </div>
    <table class="med-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">#</th>
                <th style="width: 42%; text-align: left;">Medicamento / Fármaco</th>
                <th style="width: 13%; text-align: center;">Cantidad</th>
                <th style="width: 40%; text-align: left;">Posología e Indicaciones (Dosis / Horario / Vía)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($receta->detalles as $index => $det)
                <tr>
                    <td style="text-align: center; color: #64748b; font-family: monospace; font-size: 10px;">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        <div class="med-nombre">
                            {{ $det->producto->nombre ?? 'Medicamento no catalogado' }}
                        </div>
                        <div class="med-sub">
                            {{ $det->producto->unidad_medida ?? 'Presentación estándar' }}
                            @if(optional($det->producto)->marca)
                                • Laboratorio: {{ $det->producto->marca->nombre }}
                            @endif
                        </div>
                    </td>
                    <td class="med-cantidad">
                        {{ $det->cantidad }}
                        <span style="font-size: 9px; font-weight: normal; color: #64748b; display: block;">unidades</span>
                    </td>
                    <td class="med-posologia">
                        {{ $det->indicaciones ?: 'Según indicación médica directa.' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 15px;">
                        No se registraron medicamentos en esta receta médica.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Observaciones Generales de la Receta -->
    @if ($receta->observaciones)
        <div class="obs-box">
            <div class="obs-title">Observaciones y Recomendaciones Generales:</div>
            <div>{{ $receta->observaciones }}</div>
        </div>
    @endif

    <!-- Firma y Sello del Médico -->
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-line"></div>
            <div class="signature-title">Firma y Sello del medico</div>
            <div class="signature-doctor">
                Dr(a). {{ $receta->doctor->nombre_completo ?? $receta->doctor->name ?? 'Médico Tratante' }}
            </div>
            @if(optional($receta->doctor)->especialidad)
                <div class="signature-esp">{{ $receta->doctor->especialidad->nombre }}</div>
            @endif
        </div>
    </div>

    <!-- Pie de Página -->
    <div class="footer-note">
        <div class="footer-left">
            Clínica Boliviana Americana • Sistema Clínico Hospitalario • Documento Médico Oficial
        </div>
        <div class="footer-right">
            Impreso el {{ $fechaEmision }}
        </div>
    </div>

</body>
</html>

