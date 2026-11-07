<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento Quirúrgico - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
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
            font-size: 12.5px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
            line-height: 1.25;
        }

        .lead-paragraph {
            text-align: justify;
            margin-bottom: 6px;
            color: #1e293b;
            font-size: 11px;
        }
        .highlight-line {
            border-bottom: 1px solid #0f172a;
            font-weight: 800;
            color: #0f172a;
            display: inline-block;
            min-width: 300px;
            padding: 0 4px;
        }
        .section-header {
            font-weight: 800;
            font-size: 9px;
            color: #0f172a;
            text-transform: uppercase;
            margin-top: 8px;
            margin-bottom: 3px;
        }

        .doctor-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 6px 8px;
            margin: 8px 0;
            border-radius: 4px;
        }
        .sign-area {
            margin-top: 35px;
            text-align: center;
        }
        .sign-line {
            width: 220px;
            border-top: 1.5px solid #0f172a;
            margin: 0 auto 4px auto;
        }
        .date-footer {
            text-align: right;
            margin-top: 35px;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $cirugia = $proforma->cirugias->first();
        $procedimientoNombre = $cirugia?->cirugia_efectuada ?? $cirugia?->diagnostico_operacion ?? $proforma->servicios->first()?->servicio?->nombre ?? $proforma->diagnostico ?? 'CIRUGÍA / PROCEDIMIENTO PROGRAMADO';
        $medicoCirujano = $cirugia?->cirujano?->nombre_completo ?? $proforma->medicos->first()?->nombre_completo ?? 'MÉDICO CIRUJANO TRATANTE';
        $fechaActual = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : ($proforma->created_at ? $proforma->created_at->format('d/m/Y') : now()->format('d/m/Y'));
        $ciudad = $proforma->sucursal->ciudad ?? 'La Paz';
        $familiarNombre = $paciente->contacto_emergencia_nombre ?? '';
    @endphp

    <!-- ==================== PÁGINA 1: INFORMACIÓN Y DECLARACIÓN ==================== -->
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
                <h1>
                    INFORMACIÓN, DECLARACIÓN,<br>
                    CONSENTIMIENTO Y ACEPTACION DE<br>
                    PROCEDIMIENTO QUIRURGICO
                </h1>
            </td>
        </tr>
    </table>

    <p class="lead-paragraph">
        Después de haber estudiado los síntomas que usted padece y realizado las exploraciones oportunas y siguiendo sus deseos de tratamiento, y/o intervención quirúrgica, y/o procedimiento que desea realizarse, y analizando su caso en particular, procuramos informarle de la manera más sencilla las posibles soluciones que existen para resolver este tipo de problemas y elegir con usted la alternativa más indicada.
    </p>

    <p class="lead-paragraph">
        Ante todo, queremos transmitirle que lo más importante es que tenga la absoluta seguridad que en caso de ocurrir alguna de las posibles complicaciones, estaremos a su lado y pondremos de todo nuestro esfuerzo para resolverlas.
    </p>

    <p class="lead-paragraph">
        Habiéndome sido informado/a y entendido el propósito y naturaleza del tratamiento; y/o intervención quirúrgica, y/o procedimiento a ser sometido/a, después de haberme dado a conocer las alternativas de este tratamiento y/o intervención quirúrgica y/o procedimiento y otros que me han sido claramente explicados, después de haber sido interrogado/a sobre los aspectos de anestesia y estos relacionados con mi familia y examinada físicamente es que he optado de forma voluntaria, a que se me realice:
    </p>

    <p style="margin: 8px 0; font-size: 11px;">
        {{-- <strong>PROCEDIMIENTO:</strong> <span class="highlight-line">{{ strtoupper($procedimientoNombre) }}</span> --}}
        <strong>PROCEDIMIENTO:</strong> <span class="highlight-line"></span>
    </p>

    <p class="lead-paragraph">
        Entiendo y he sido informado/a sobre los riesgos quirúrgicos, idiosincrasia, reacciones imprevisibles, <strong>Infecciones:</strong> Pueden manifestarse en la herida operatoria o de forma sistémica (como infecciones urinarias) días después de la intervención. <strong>Hemorragias:</strong> Sangrado excesivo que puede ocurrir durante la cirugía o en el periodo postoperatorio inmediato. <strong>Problemas respiratorios:</strong> Como atelectasia o neumonía, frecuentemente relacionados con el reposo prolongado y el efecto de la anestesia. <strong>Complicaciones de la herida:</strong> Incluyen la dehiscencia (apertura de los puntos o grapas), formación de seromas (acumulación de líquido) o hematomas. <strong>Trombosis y embolias:</strong> Riesgo de formación de coágulos (Trombosis Venosa Profunda) que pueden viajar a los pulmones (Embolismo Pulmonar).
    </p>

    <div style="font-weight: 800; font-size: 11px; margin-top: 6px;">
        FACTORES DE RIESGO: LA PROBABILIDAD DE SUFRIR COMPLICACIONES DEPENDE DE:
    </div>
    <p class="lead-paragraph" style="margin-top: 2px;">
        <strong>Factores del paciente:</strong> Edad avanzada, obesidad, tabaquismo y enfermedades crónicas de base (diabetes o problemas cardíacos). <strong>Complejidad de la cirugía:</strong> Procedimientos prolongados o cirugías de urgencia (especialmente por traumas) conllevan un riesgo mayor.
    </p>

    <div class="doctor-box">
        <div style="font-weight: 800; font-size: 11px; text-transform: uppercase;">MEDICO RESPONSABLE</div>
        <div style="margin: 3px 0; font-size: 11px;">
            <strong>DR./DRA.:</strong> <span style="font-weight: 800; color: #1e3a8a;">{{-- strtoupper($medicoCirujano) --}}_____________________________</span>
        </div>
        <p style="margin: 3px 0; text-align: justify; font-size: 11px;">
            - He informado verbal y gráficamente a el/la paciente del propósito y naturaleza del tratamiento, y/o intervención quirúrgica, y/o procedimiento a realizarse, descrito anteriormente, de sus alternativas, de los posibles riesgos, de sus limitaciones y de los resultados que pueden esperarse.
        </p>
        <div style="margin-top: 6px; font-size: 11px;">
            <strong>FECHA:</strong> {{ $fechaActual }} &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            <strong>FIRMA Y SELLO:</strong> __________________________________________________
        </div>
    </div>

    <div class="section-header">I. DECLARACION DEL PACIENTE .-</div>
    <p class="lead-paragraph">
        Se me ha indicado la necesidad de advertir de mis posibles alergias medicamentosas, alteraciones de la coagulación, existencia de prótesis, marcapasos, medicaciones actuales o cualquier otra circunstancia;
    </p>
    <p class="lead-paragraph">
        Declaro ciertos todos los datos relativos a mis Historial Clínico, no habiendo omitido ningún aspecto de interés o que se me hubiera cuestionado;
    </p>
    <p class="lead-paragraph" style="margin-top: 6px;">
        Declaro que he sido informado/a satisfactoriamente de la naturaleza y propósito del tratamiento; y/o intervención quirúrgica, y/o procedimiento arriba citado. Se me ha explicado verbal y gráficamente los posibles riesgos y complicaciones, así como las otras alternativas de tratamiento con los posibles resultados y riesgos.
    </p>

    <p class="lead-paragraph">
        Declaro que he sido informado/a de los beneficios del procedimiento a corto, mediano y largo plano.
    </p>

    <p class="lead-paragraph">
        Se me ha comunicado la libertad para reconsiderar, en cualquier momento, la decisión tomada.
    </p>

    <div class="section-header">II. CONSENTIMIENTO.</div>
    <p class="lead-paragraph">
        Una vez recibida dicha información, comprendido el tratamiento, y/o intervención quirúrgica y/o procedimiento a realizarse; y aceptados los riesgos:
    </p>
    <p class="lead-paragraph">
        Estando en pleno uso de mis facultades físicas y mentales doy mi consentimiento para que el/la <strong>Dr./Dra. {{-- strtoupper($medicoCirujano) --}}_____________________________</strong> y su equipo, me realice el tratamiento y/o intervención quirúrgica y/o procedimiento descrito a realizarse. Si durante la intervención surgiera alguna situación inesperada que requiriese cualquier procedimiento distinto o añadido a los ahora previsto y que me han explicados, solicito y autorizo al equipo médico que realice aquellos que crean convenientes o necesarios.
    </p>

    <!-- Salto a Página 2 -->
    <div class="page-break"></div>

    <!-- ==================== PÁGINA 2: CONSENTIMIENTO Y ACEPTACIÓN ==================== -->

    <div class="section-header">III. ACEPTACION. -</div>
    <p class="lead-paragraph">
        Acepto y me comprometo a seguir fielmente las recomendaciones recibidas tanto antes como después del tratamiento y/o intervención quirúrgica y/o procedimiento a realizarse así como a acudir a las revisiones postoperatorias durante el tiempo indicado.
    </p>
    <p class="lead-paragraph">
        Acepto y reconozco que no se me pueden dar garantías o seguridad absoluta por los riesgos que conlleva toda intervención quirúrgica y anestésica.
    </p>
    <p class="lead-paragraph">
        Acepto firmar este consentimiento informado y doy fe de que la <strong>Dr/Dra. {{-- strtoupper($medicoCirujano) --}}_____________________________</strong> y su equipo me han informado del tratamiento y/o intervención quirúrgica y/o procedimiento al que deseo ser sometido/a.
    </p>

    <!-- Firmas -->
    <div class="sign-area" style="margin-top: 45px;">
        <div class="sign-line"></div>
        <div style="font-weight: 800; font-size: 11px; margin-bottom: 6px;">Firma del paciente</div>
        <div style="font-size: 11px;">
            <strong>Nombre:</strong> {{ strtoupper($paciente->nombre_completo ?? '________________________________________________') }}<br>
            <strong>C.I.:</strong> {{ $paciente->cedula ?? '________________________' }}
        </div>
    </div>

    <div class="sign-area" style="margin-top: 35px;">
        <div class="sign-line"></div>
        <div style="font-weight: 800; font-size: 11px; margin-bottom: 6px;">Firma del familiar</div>
        <div style="font-size: 11px;">
            <strong>Nombre:</strong> {{ strtoupper($familiarNombre ?: '________________________________________________') }}<br>
            <strong>C.I.:</strong> ________________________
        </div>
    </div>

    <div class="date-footer">
        FECHA: ...... DE ......................................... DE 20.....
    </div>
</body>
</html>

