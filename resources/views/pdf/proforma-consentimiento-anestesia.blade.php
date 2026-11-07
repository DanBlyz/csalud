<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento Informado - Anestesia Raquídea - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 50px 40px 50px 40px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8.5px;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .header-logo {
            width: 30%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 38px;
            max-width: 160px;
            object-fit: contain;
        }
        .header-title {
            width: 70%;
            vertical-align: middle;
            text-align: center;
            padding-right: 35%;
        }
        .header-title h1 {
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
            line-height: 1.2;
        }
        .header-title h2 {
            font-size: 12px;
            font-weight: 800;
            color: #0284c7;
            letter-spacing: 0.5px;
            margin: 2px 0 0 0;
            text-transform: uppercase;
        }

        .section-title {
            font-weight: 800;
            font-size: 11px;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 7px;
            margin-bottom: 2px;
        }
        .lead-paragraph {
            text-align: justify;
            margin-bottom: 5px;
            color: #1e293b;
            font-size: 11px;
        }
        .bullet-list {
            margin: 3px 0 6px 14px;
            padding: 0;
            font-size: 11px;
        }
        .bullet-list li {
            margin-bottom: 2.5px;
            text-align: justify;
        }
        .highlight {
            font-weight: 700;
            color: #0f172a;
            text-decoration: underline;
        }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .signatures-table td {
            padding: 2.5px 4px;
            vertical-align: bottom;
            font-size: 11px;
        }
        .dotted-line {
            border-bottom: 1px dotted #475569;
            display: inline-block;
            min-width: 160px;
        }
        .date-box {
            text-align: right;
            margin-top: 10px;
            margin-bottom: 14px;
            font-size: 8.5px;
            font-weight: 600;
        }
        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $edad = $proforma->edad_paciente ?? ($paciente ? $paciente->edad : null);
        $edadTexto = $edad !== null ? "{$edad}" : '____';
        $cirugia = $proforma->cirugias->first();
        $procedimientoNombre = $cirugia?->cirugia_efectuada ?? $cirugia?->diagnostico_operacion ?? $proforma->servicios->first()?->servicio?->nombre ?? $proforma->diagnostico ?? 'CIRUGÍA PROGRAMADA';
        $servicioNombre = $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion ?: 'CIRUGÍA / QUIRÓFANO');
        $ciudad = $proforma->sucursal->ciudad ?? 'La Paz';
        $familiarNombre = $paciente->contacto_emergencia_nombre ?? '';
        $familiarCi = '';
    @endphp

    <!-- ==================== PÁGINA 1: INFORMACIÓN Y ACEPTACIÓN ==================== -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if ($logoBase64)
                    <img src="{{ $logoBase64 }}" alt="Clínica Boliviana Americana">
                @else
                    <strong style="color: #1e3a8a; font-size: 11px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                @endif
            </td>
            <td class="header-title">
                <h1>CONSENTIMIENTO INFORMADO</h1>
                <h2>ANESTESIA RAQUIDEA</h2>
            </td>
        </tr>
    </table>

    <p class="lead-paragraph">
        <strong>SEÑOR PACIENTE:</strong> Por favor lea atentamente el presente documento y consulte con el medico Anestesiólogo acerca de cualquier duda que pueda tener, para luego firmar libremente y sin presiones este documento, otorgándonos su consentimiento para proceder o no al acto anestésico.
    </p>

    <div class="section-title">CONSIDERACIONES GENERALES:</div>
    <p class="lead-paragraph">
        {{-- Usted ha sido programado para ser sometido a la cirugía: <span class="highlight">{{ strtoupper($procedimientoNombre) }}</span> --}}
        Usted ha sido programado para ser sometido a la cirugía: <span class="highlight">_______________________</span>
    </p>
    <p class="lead-paragraph">
        La anestesia raquídea, o espinal, es un tipo de anestesia regional que implica inyectar un medicamento anestésico en el líquido cefalorraquídeo que rodea la médula espinal, provocando un bloqueo inmediato de la sensación y el movimiento en la parte inferior del cuerpo para procedimientos como cirugías abdominales bajas, de cadera o de piernas. A diferencia de la anestesia epidural, se administra directamente en el espacio que contiene el líquido espinal y produce un efecto limitado en el tiempo, lo que la hace útil para cirugías de menor duración y para lograr una recuperación más rápida con menos náuseas y vómitos posoperatorios.
    </p>

    <div class="section-title">COMPLICACIONES:</div>
    <p class="lead-paragraph">
        A pesar de que este tipo de Anestesia será realizada por profesional (es) especializado (s) no está libre de complicaciones, Aunque su aparición es poco frecuente, usted debe conocerlas.
    </p>
    <p class="lead-paragraph">
        Si usted es portador de alguna enfermedad no controlada o tratada (diabetes, hipertensión, enfermedad cardiaca, pulmonar u otras) asociada a su enfermedad quirúrgica, el riesgo de complicaciones por la administración de Anestésicos es mayor, por tanto, estas enfermedades deben ser tratadas o por lo menos comunicadas antes de la intervención.
    </p>

    <ul class="bullet-list">
        <li><strong>ALERGIAS:</strong> puede producirse por la administración de los sueros, medicamentos anestésicos en vena, anestésicos locales y aun el látex de los guantes del equipo quirúrgico, no pudiendo ser predecibles, pero si la mayor parte de las veces controlables.</li>
        <li><strong>ALTERACIONES EN LA PRESIÓN ARTERIAL:</strong> Hipotensión o Hipertensión, sobre todo en pacientes con afectaciones previas. Disminución del Latido Cardiaco.</li>
        <li>Puede haber dificultad para intubar (colocar un tubo en la garganta).</li>
        <li>Bronco aspiración, que es la entrada de contenido gástrico a los pulmones. El riesgo es menor cuando se respetan las horas de ayuno previas a la cirugía.</li>
        <li>Náuseas y/o vómitos postoperatorios.</li>
        <li><strong>OTRAS COMPLICACIONES DE LA ANESTESIA GENERAL PUEDEN SER:</strong> ruptura de dientes, infecciones, falla hepática, daño renal, liberación de coágulos inclusive la más remota, el paro cardiorrespiratorio que puede llevar a la muerte.</li>
    </ul>

    <p class="lead-paragraph">
        En el mundo entero diariamente miles de pacientes son sometidos a esta técnica de anestesia en nuestra clínica sucede del mismo modo, siendo el beneficio mucho mayor que el riesgo, con un índice muy bajo de complicaciones.
    </p>

    <div class="section-title">CONSENTIMIENTO Y FIRMAS:</div>
    <p class="lead-paragraph">
        <strong>ACEPTACIÓN:</strong><br>
        Yo: <strong>{{ strtoupper($paciente->nombre_completo ?? '________________________________________________') }}</strong> de <strong>{{ $edadTexto }}</strong> años de edad<br>
        Paciente del Servicio de <strong>{{-- strtoupper($servicioNombre) --}}_____________________________</strong><br>
        Declaro que he sido informado clara y detalladamente sobre el procedimiento anestésico propuesto, sus beneficios y sus riesgos. Por tanto, por el derecho que se me concede por ser mayor de edad y (el o los) familiares abajo firmantes, sin presión alguna <strong>ACEPTAMOS Y CONSENTIMOS</strong> la realización de la <strong>ANESTESIA RAQUIDEA</strong> propuesta, con todos los riesgos que esto conlleva, deslindando de responsabilidad en caso de accidentes imprevistos que pese a la experiencia, cuidados y destreza profesional pudiera ocurrir. Si surgiera alguna situación imprevista URGENTE QUE PONGA EN RIESGO MI VIDA y requiera de algún procedimiento distinto al que he sido informado, autorizo al equipo médico a realizarlo aun sin previo consentimiento.
    </p>

    <p style="font-weight: 800; font-size: 11px; margin: 4px 0 2px 0;">PARA CONSTANCIA:</p>
    <table class="signatures-table">
        <tr>
            <td style="width: 55%;">
                <strong>Nombre del paciente:</strong> {{ strtoupper($paciente->nombre_completo ?? '....................................................') }}
            </td>
            <td style="width: 45%;">
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> {{ $edadTexto }} años
            </td>
            <td>
                <strong>C.I.:</strong> {{ $paciente->cedula ?? '....................................................' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Nombre del Familiar:</strong> {{ strtoupper($familiarNombre ?: '....................................................') }}
            </td>
            <td>
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> ........................ años
            </td>
            <td>
                <strong>C.I.:</strong> ....................................................
            </td>
        </tr>
    </table>

    <!-- Salto a Página 2 -->
    <div class="page-break"></div>

    <!-- ==================== PÁGINA 2: INCAPACIDAD Y REVOCACIÓN ==================== -->
    <div style="font-weight: 800; font-size: 11px; text-transform: uppercase; margin-bottom: 2px;">
        EN CASO DE INCAPACIDAD DEL PACIENTE, MINORIA DE EDAD O INCOSCIENCIA
    </div>
    <div style="font-size: 8px; color: #475569; margin-bottom: 6px;">
        Representante legal. Padre, Madre, Tutor, Otros...
    </div>

    <table class="signatures-table">
        <tr>
            <td style="width: 55%;">
                <strong>Nombre del paciente:</strong> {{ strtoupper($paciente->nombre_completo ?? '....................................................') }}
            </td>
            <td style="width: 45%;">
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> {{ $edadTexto }} años
            </td>
            <td>
                <strong>C.I.:</strong> {{ $paciente->cedula ?? '....................................................' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>En calidad de:</strong> ........................................................................................................................................
            </td>
        </tr>
    </table>

    <div class="date-box">
        {{ $ciudad }}, ...... de ......................................... de 20.....
    </div>

    <div class="divider"></div>

    <!-- SECCIÓN DE RECHAZO / REVOCACIÓN -->
    <div style="font-weight: 800; font-size: 9.5px; text-transform: uppercase; margin-bottom: 4px;">
        RECHAZO Y/O REVOCACIÓN
    </div>

    <p class="lead-paragraph">
        Yo: <strong>{{ strtoupper($paciente->nombre_completo ?? '....................................................') }}</strong> de <strong>{{ $edadTexto }}</strong> años de edad<br>
        Paciente del Servicio de <strong>{{-- strtoupper($servicioNombre) --}}_____________________________</strong><br>
        Declaro que he sido informado clara detalladamente acerca del procedimiento anestésico propuesto, sus beneficios y sus riesgos. Por tanto, por el derecho que se me concede por ser mayor de edad y (el o los) familiares abajo firmantes sin presión alguna <strong>RECHAZAMOS Y NO CONSENTIMOS</strong> la realización de la <strong>ANESTESIA</strong> propuesta, con todos los riesgos que esto conlleva. <strong>ASUMIENDO</strong> toda la responsabilidad de esta decisión.
    </p>

    <table class="signatures-table">
        <tr>
            <td style="width: 55%;">
                <strong>Nombre del paciente:</strong> {{ strtoupper($paciente->nombre_completo ?? '....................................................') }}
            </td>
            <td style="width: 45%;">
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> {{ $edadTexto }} años
            </td>
            <td>
                <strong>C.I.:</strong> {{ $paciente->cedula ?? '....................................................' }}
            </td>
        </tr>
        <tr>
            <td>
                <strong>Nombre del Familiar:</strong> {{ strtoupper($familiarNombre ?: '....................................................') }}
            </td>
            <td>
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> ........................ años
            </td>
            <td>
                <strong>C.I.:</strong> ....................................................
            </td>
        </tr>
    </table>

    <div class="date-box">
        {{ $ciudad }}, ...... de ......................................... de 20.....
    </div>

    <div class="divider"></div>

    <!-- SEGUNDO BLOQUE DE INCAPACIDAD EN REVOCACIÓN -->
    <div style="font-weight: 800; font-size: 9px; text-transform: uppercase; margin-bottom: 2px;">
        EN CASO DE INCAPACIDAD DEL PACIENTE, MINORIA DE EDAD O INCOSCIENCIA
    </div>
    <div style="font-size: 8px; color: #475569; margin-bottom: 6px;">
        Representante legal. Padre, Madre, Tutor, Otros...
    </div>

    <table class="signatures-table">
        <tr>
            <td style="width: 55%;">
                <strong>Nombre del paciente:</strong> {{ strtoupper($paciente->nombre_completo ?? '....................................................') }}
            </td>
            <td style="width: 45%;">
                <strong>Firma:</strong> ....................................................
            </td>
        </tr>
        <tr>
            <td>
                <strong>Edad:</strong> {{ $edadTexto }} años
            </td>
            <td>
                <strong>C.I.:</strong> {{ $paciente->cedula ?? '....................................................' }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <strong>En calidad de:</strong> ........................................................................................................................................
            </td>
        </tr>
    </table>

    <div class="date-box">
        {{ $ciudad }}, ...... de ......................................... de 20.....
    </div>
</body>
</html>

