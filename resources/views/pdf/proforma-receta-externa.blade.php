<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Receta Médica Externa #{{ str_pad($recetaExterna->id, 5, '0', STR_PAD_LEFT) }} - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
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
            border-bottom: 2px solid #0284c7;
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
            font-size: 17px;
            font-weight: 800;
            color: #0369a1;
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
            background-color: #f0f9ff;
            border: 1px solid #7dd3fc;
            color: #0369a1;
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
            font-size: 10px;
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
            font-size: 9px;
            text-transform: uppercase;
        }
        .value-text {
            font-weight: 600;
            color: #0f172a;
        }

        /* Tabla de Medicamentos */
        .section-header {
            font-size: 11px;
            font-weight: 800;
            color: #0369a1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            padding-left: 2px;
        }
        .med-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border: 1px solid #0f172a;
        }
        .med-table th {
            background-color: #f8fafc;
            color: #0f172a;
            border: 1px solid #0f172a;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .med-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 8px;
            font-size: 10px;
            vertical-align: middle;
        }
        .med-nombre {
            font-weight: 700;
            color: #0f172a;
            font-size: 10.5px;
        }
        .med-cantidad {
            font-weight: 700;
            color: #0369a1;
            text-align: center;
            font-size: 10px;
        }
        .med-posologia {
            color: #334155;
            line-height: 1.35;
        }

        /* Observaciones */
        .obs-box {
            border: 1px dashed #94a3b8;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 25px;
            font-size: 10px;
            color: #334155;
        }
        .obs-title {
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            margin-bottom: 3px;
            font-size: 9.5px;
        }

        /* Firma y Sello */
        .signature-section {
            margin-top: 35px;
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
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $doctorNombre = $recetaExterna->nombre_medico_prescriptor;
        $edad = $proforma->edad_paciente ?? ($paciente ? $paciente->edad : null);
        $edadTexto = $edad !== null ? "{$edad} AÑOS" : 'S/E';
    @endphp

    <!-- Encabezado con Logo, Título y Folio -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Clínica Boliviana Americana">
                @else
                    <strong style="color: #0369a1; font-size: 14px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-center">
                <h1>RECETA MÉDICA (EXTERNA)</h1>
                <p>Orden para adquisición externa de medicamentos e insumos</p>
            </td>
            <td class="header-folio">
                <span class="folio-badge">COMPRA EXTERNA</span>
                <div class="folio-num">Receta Ext. N°: #{{ str_pad($recetaExterna->id, 5, '0', STR_PAD_LEFT) }}</div>
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
                <span class="value-text" style="font-size: 11px;">{{ strtoupper($paciente->nombre_completo ?? 'N/E') }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label-text">Edad:</span><br>
                <span class="value-text">{{ $edadTexto }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label-text">N° H.C.:</span><br>
                <span class="value-text" style="font-family: monospace; font-size: 10.5px;">
                    {{ $paciente->numero_historia_clinica ?? 'S/N' }}
                </span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label-text">Cédula / Documento:</span><br>
                <span class="value-text">{{ $paciente->cedula ?? 'S/D' }}</span>
                @if($paciente?->genero)
                    <span style="color: #64748b; font-size: 9px;">({{ $paciente->genero }})</span>
                @endif
            </td>
            <td>
                <span class="label-text">Fecha y Hora:</span><br>
                <span class="value-text">{{ $recetaExterna->fecha ? $recetaExterna->fecha->format('d/m/Y H:i') : now()->format('d/m/Y H:i') }}</span>
            </td>
            <td>
                <span class="label-text">Prescrito Por:</span><br>
                <span class="value-text">
                    {{ $doctorNombre }}
                </span>
            </td>
        </tr>
        @if ($recetaExterna->diagnostico)
            <tr>
                <td colspan="3">
                    <span class="label-text">Diagnóstico / Motivo:</span><br>
                    <span class="value-text">{{ $recetaExterna->diagnostico }}</span>
                </td>
            </tr>
        @endif
    </table>

    <!-- Tabla de Medicamentos Prescritos -->
    <div class="section-header">
        Medicamentos e Insumos Solicitados para Adquisición Externa
    </div>
    <table class="med-table">
        <thead>
            <tr>
                <th style="width: 6%; text-align: center;">#</th>
                <th style="width: 44%; text-align: left;">Medicamento / Insumo Solicitado</th>
                <th style="width: 15%; text-align: center;">Cantidad</th>
                <th style="width: 35%; text-align: left;">Indicaciones / Posología</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recetaExterna->detalles as $index => $det)
                <tr>
                    <td style="text-align: center; color: #64748b; font-family: monospace; font-size: 10px;">
                        {{ $index + 1 }}
                    </td>
                    <td>
                        <div class="med-nombre">
                            {{ $det->medicamento }}
                        </div>
                    </td>
                    <td class="med-cantidad">
                        {{ $det->cantidad }}
                    </td>
                    <td class="med-posologia">
                        {{ $det->indicaciones ?: 'Según indicación médica directa.' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; color: #64748b; padding: 15px;">
                        No se registraron medicamentos en esta receta externa.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Observaciones Generales -->
    @if ($recetaExterna->observaciones)
        <div class="obs-box">
            <div class="obs-title">Observaciones e Instrucciones para la Compra:</div>
            <div>{{ $recetaExterna->observaciones }}</div>
        </div>
    @endif

    <!-- Firma y Sello del Médico -->
    <div class="signature-section">
        <div class="signature-container">
            <div class="signature-line"></div>
            <div class="signature-title">Firma y Sello del Médico</div>
            <div class="signature-doctor">
                {{ $doctorNombre }}
            </div>
            @if(optional($recetaExterna->doctor)->especialidad)
                <div class="signature-esp">{{ $recetaExterna->doctor->especialidad->nombre }}</div>
            @endif
        </div>
    </div>

    <!-- Pie de Página -->
    <div class="footer-note">
        <div class="footer-left">
            Clínica Boliviana Americana • Sistema Clínico Hospitalario • Receta Externa Oficial
        </div>
        <div class="footer-right">
            Impreso el {{ $fechaEmision }}
        </div>
    </div>
</body>
</html>

