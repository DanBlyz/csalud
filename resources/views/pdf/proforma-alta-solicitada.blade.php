<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Alta Solicitada - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 28px 36px 28px 36px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-logo {
            width: 45%;
            vertical-align: top;
            text-align: left;
        }
        .header-logo img {
            height: 48px;
            max-width: 200px;
            object-fit: contain;
        }
        .header-meta {
            width: 55%;
            vertical-align: top;
            text-align: right;
            font-size: 10px;
            color: #475569;
        }
        .header-meta strong {
            color: #0f172a;
        }

        .doc-title {
            text-align: center;
            margin: 15px 0 25px 0;
        }
        .doc-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 1px;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-title .doc-subtitle {
            font-size: 11px;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 2px;
        }

        .content-box {
            margin-bottom: 18px;
            text-align: justify;
        }
        .patient-lead {
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 14px;
        }
        .highlight-text {
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1px dotted #64748b;
            padding: 0 2px;
        }

        .diagnostic-section {
            background-color: #f8fafc;
            border-left: 3px solid #0284c7;
            padding: 8px 14px;
            margin-bottom: 18px;
            border-radius: 4px;
        }
        .diagnostic-title {
            font-weight: bold;
            font-size: 11px;
            color: #0369a1;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .diagnostic-list {
            margin: 0;
            padding-left: 18px;
            font-size: 11px;
            color: #1e293b;
        }
        .diagnostic-list li {
            margin-bottom: 3px;
        }

        .legal-paragraph {
            margin-bottom: 14px;
            font-size: 11px;
            line-height: 1.65;
            color: #1e293b;
        }
        .legal-paragraph strong {
            color: #0f172a;
            text-transform: uppercase;
        }

        /* Firmas */
        .signatures-section {
            margin-top: 55px;
            width: 100%;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        .signature-box {
            width: 48%;
            vertical-align: top;
            padding: 0 10px;
        }
        .signature-line {
            border-top: 1.5px solid #0f172a;
            margin-bottom: 8px;
            width: 85%;
            margin-left: auto;
            margin-right: auto;
        }
        .signature-label {
            font-weight: bold;
            font-size: 10.5px;
            color: #0f172a;
            text-align: center;
            margin-bottom: 4px;
        }
        .signature-data {
            font-size: 10px;
            color: #334155;
            text-align: center;
            line-height: 1.4;
        }

        .footer-notice {
            margin-top: 40px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 8.5px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $paciente = $proforma->paciente;
        $servicio = $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion === 'Internacion' ? 'UNIDAD DE INTERNACIÓN' : 'SALA DE HOSPITALIZACIÓN');
        $pieza = $proforma->pieza ?? $proforma->cirugias->first()?->cama ?? $proforma->epicrisis?->cama ?? null;
        $camaSala = $pieza ? 'EN ' . strtoupper($pieza) : 'EN SALA DE ATENCIÓN';
        $diagnosticos = [];
        if ($proforma->diagnostico) {
            $diagnosticos = array_filter(array_map('trim', explode("\n", $proforma->diagnostico)));
        } elseif ($proforma->historiaClinica && $proforma->historiaClinica->diagnostico) {
            $diagnosticos = array_filter(array_map('trim', explode("\n", $proforma->historiaClinica->diagnostico)));
        }
    @endphp

    <!-- Header Institucional -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if (file_exists($logoPath))
                    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" alt="Logo Clínica">
                @else
                    <div style="font-weight: 800; font-size: 13px; color: #1e3a8a;">
                        CLÍNICA BOLIVIANA AMERICANA<br>
                        <span style="font-size: 9px; color: #64748b; font-weight: normal;">ESPECIALIDADES MÉDICAS</span>
                    </div>
                @endif
            </td>
            <td class="header-meta">
                <div><strong>FECHA:</strong> {{ $fechaEmision ?? now()->format('d/m/Y') }}</div>
                <div><strong>HORA:</strong> {{ $horaEmision ?? now()->format('H:i') }}</div>
                <div><strong>N° HISTORIA CLÍNICA:</strong> {{ $paciente->numero_historia_clinica ?? '---' }}</div>
                <div><strong>N° PROFORMA:</strong> #{{ $proforma->id }}</div>
                <div><strong>SEDE:</strong> {{ $proforma->sucursal->nombre ?? 'Sede Central' }}</div>
            </td>
        </tr>
    </table>

    <!-- Título Principal -->
    <div class="doc-title">
        <h1>ALTA SOLICITADA</h1>
        <div class="doc-subtitle">Formulario Oficial de Alta Voluntaria y Deslinde de Responsabilidad</div>
    </div>

    <!-- Cuerpo del Documento -->
    <div class="content-box">
        <p class="patient-lead">
            Yo, <span class="highlight-text">{{ strtoupper($paciente->nombre_completo) }}</span> con documento de identidad <span class="highlight-text">{{ $paciente->cedula ?? '---' }}</span> quien se encuentra ingresado en el servicio de <span class="highlight-text">{{ strtoupper($servicio) }}</span> {{ strtoupper($camaSala) }}.
        </p>

        <!-- Diagnósticos Clínicos -->
        <div class="diagnostic-section">
            <div class="diagnostic-title">Con los diagnósticos:</div>
            @if (count($diagnosticos) > 0)
                <ul class="diagnostic-list">
                    @foreach ($diagnosticos as $diag)
                        <li>{{ strtoupper($diag) }}</li>
                    @endforeach
                </ul>
            @else
                <div style="font-style: italic; color: #64748b; font-size: 10px;">
                    - SEGÚN VALORACIÓN MÉDICA CONSIGNADA EN HISTORIA CLÍNICA
                </div>
            @endif
        </div>

        <p class="legal-paragraph">
            <strong>EXPONGO:</strong> Que, por motivos estrictamente personales, es mi deseo y voluntad solicitar el <strong>ALTA VOLUNTARIA</strong>, interrumpiendo mi estancia y el tratamiento en el presente centro médico a partir de la fecha de firma de este documento.
        </p>

        <p class="legal-paragraph">
            <strong>DECLARO QUE:</strong> He sido debidamente informado por el médico tratante sobre mi estado de salud actual. Comprendo plenamente la alta probabilidad de riesgos, consecuencias, complicaciones y probable fallecimiento del paciente al realizar la interrupción del tratamiento y el traslado prematuro.
        </p>

        <p class="legal-paragraph">
            Eximo de toda responsabilidad legal, civil o penal al personal médico, de enfermería y administrativo de la institución <strong>Clínica Boliviana Americana</strong> por cualquier deterioro en la salud del paciente derivado de esta decisión. Me hago cargo de forma total y absoluta del cuidado, traslado y posterior asistencia médica que el paciente requiera desde este momento.
        </p>

        <p class="legal-paragraph">
            Por lo tanto, firmo la presente solicitud ratificando mi decisión y asumiendo las consecuencias de la misma.
        </p>
    </div>

    <!-- Firmas -->
    <div class="signatures-section">
        <table class="signatures-table">
            <tr>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Firma de paciente:</div>
                    <div class="signature-data">
                        <strong>Nombre:</strong> {{ strtoupper($paciente->nombre_completo) }}<br>
                        <strong>Carnet:</strong> {{ $paciente->cedula ?? '---' }}
                    </div>
                </td>
                <td class="signature-box">
                    <div class="signature-line"></div>
                    <div class="signature-label">Firma de familiar / representante legal:</div>
                    <div class="signature-data">
                        <strong>Nombre:</strong> __________________________________________<br>
                        <strong>Carnet:</strong> __________________________________________
                        @if ($paciente->contacto_emergencia_nombre)
                            <div style="font-size: 8.5px; color: #64748b; margin-top: 3px;">
                                Acompañante registrado: {{ $paciente->contacto_emergencia_nombre }} (Tel: {{ $paciente->contacto_emergencia_telefono ?? '---' }})
                            </div>
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Pie de Página -->
    <div class="footer-notice">
        Documento médico-legal original. Clínica Boliviana Americana • {{ $proforma->sucursal->nombre ?? '' }} • Impreso el {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>

