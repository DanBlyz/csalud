<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Enfermería en Emergencia - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
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
            margin-bottom: 6px;
        }
        .header-logo {
            width: 25%;
            vertical-align: middle;
            text-align: left;
        }
        .header-logo img {
            height: 40px;
            max-width: 160px;
            object-fit: contain;
        }
        .header-title {
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 20%;
        }
        .header-title h1 {
            font-size: 14.5px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .border-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #1e293b;
            margin-bottom: 5px;
        }
        .border-table th, .border-table td {
            border: 1px solid #1e293b;
            padding: 2.5px 4px;
            vertical-align: middle;
        }
        .section-header {
            background-color: #f1f5f9;
            font-weight: 800;
            font-size: 9px;
            text-transform: uppercase;
            text-align: left;
            padding: 2.5px 5px;
            border-bottom: 1px solid #1e293b;
        }
        .label {
            font-weight: 700;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 8px;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }
        .check-box {
            display: inline-block;
            width: 10px;
            height: 10px;
            border: 1px solid #334155;
            margin-right: 3px;
            vertical-align: middle;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
        }
        .table-center th, .table-center td {
            text-align: center;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            font-size: 8.5px;
        }
        .sign-line {
            width: 180px;
            border-bottom: 1px solid #0f172a;
            margin: 0 auto 4px auto;
        }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('img/logo_clinica.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : null;
        $paciente = $proforma->paciente;
        $historia = $proforma->historiaClinica;
        $edad = $proforma->edad_paciente ?? ($paciente ? $paciente->edad : null);
        $edadTexto = $edad !== null ? "{$edad} años" : '---';
        $telefono = $paciente->celular ?? $paciente->contacto_emergencia_telefono ?? '---';
        $tieneAlergias = !empty(trim((string)($paciente->antecedentes_alergias ?? '')));
        $horaEntrada = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('H:i') : ($proforma->created_at ? $proforma->created_at->format('H:i') : now()->format('H:i'));
        $fechaEntrada = $proforma->fecha_ingreso ? $proforma->fecha_ingreso->format('d/m/Y') : ($proforma->created_at ? $proforma->created_at->format('d/m/Y') : now()->format('d/m/Y'));
        $servicioNombre = $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion ?: 'EMERGENCIA');
    @endphp

    <!-- Cabecera -->
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
                <h1>Registro de Enfermería en Emergencia</h1>
            </td>
        </tr>
    </table>

    <!-- Datos del Paciente -->
    <table class="border-table">
        <tr>
            <td style="width: 75%;">
                <span class="label">NOMBRE:</span>
                <span class="val">{{ $paciente->nombre_completo ?? '---' }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">EDAD:</span>
                <span class="val">{{ $edadTexto }}</span>
            </td>
        </tr>
        <tr>
            <td style="width: 75%;" colspan="1">
                <table style="width: 100%; border-collapse: collapse; border: none;">
                    <tr>
                        <td style="border: none; padding: 0; width: 45%;">
                            <span class="label">TELÉFONO:</span>
                            <span class="val">{{ $telefono }}</span>
                        </td>
                        <td style="border: none; padding: 0; width: 27%;">
                            <span class="label">PESO:</span>
                            <span class="val">{{ $historia?->peso ? $historia->peso . ' kg' : '_____' }}</span>
                        </td>
                        <td style="border: none; padding: 0; width: 28%;">
                            <span class="label">TALLA:</span>
                            <span class="val">{{ $historia?->talla ? $historia->talla . ' cm' : '_____' }}</span>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width: 25%;">
                <span class="label">HC:</span>
                <span class="val">{{ $paciente->numero_historia_clinica ?? $proforma->id }}</span>
            </td>
        </tr>
    </table>

    <!-- Datos Previos -->
    <table class="border-table">
        <tr>
            <th colspan="4" class="section-header">DATOS PREVIOS</th>
        </tr>
        <tr>
            <td style="width: 20%;">
                <span class="label">HOJA N°:</span> <span class="val">1</span>
            </td>
            <td style="width: 25%;">
                <span class="label">FECHA:</span> <span class="val">{{ $fechaEntrada }}</span>
            </td>
            <td style="width: 25%;">
                <span class="label">HORA ENTRADA:</span> <span class="val">{{ $horaEntrada }}</span>
            </td>
            <td style="width: 30%;">
                <span class="label">SERVICIO:</span> <span class="val">{{ $servicioNombre }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 4px;">
                <span class="label">MOTIVO DE CONSULTA:</span>
                <span class="val">{{ $historia?->motivo_consulta ?? $proforma->motivo_consulta ?? 'Sin registro especificado' }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 3px 4px;">
                <span class="label">MODO DE INGRESO:</span>&nbsp;&nbsp;
                <span class="check-box"></span> CAMINANDO &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> SILLA DE RUEDAS &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> CAMILLA &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> OTRO: _________________
            </td>
        </tr>
        <tr>
            <td colspan="4" style="padding: 3px 4px;">
                <span class="label" style="visibility: hidden;">MODO DE INGRESO:</span>&nbsp;&nbsp;
                <span class="check-box"></span> TRAÍDO POR FAMILIARES &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> AMBULANCIA &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> SOLO &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box"></span> OTRO: _________________
            </td>
        </tr>
    </table>

    <!-- Valoración Inicial -->
    <table class="border-table">
        <tr>
            <th colspan="2" class="section-header">VALORACIÓN INICIAL</th>
        </tr>
        <tr>
            <td colspan="2" style="padding: 3.5px 5px;">
                <span class="label">DOLOR:</span>
                <span class="check-box"></span> NO &nbsp;&nbsp;
                <span class="check-box"></span> SI &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="label">TIPO:</span> ____________________________________ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="label">LOCALIZACIÓN:</span> ____________________________________
            </td>
        </tr>
        <tr>
            <td colspan="2" style="padding: 3.5px 5px;">
                <span class="label">ALERGIAS:</span> &nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box">{{ !$tieneAlergias ? 'X' : '' }}</span> NO CONOCIDAS &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="check-box">{{ $tieneAlergias ? 'X' : '' }}</span> SI &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="label">A:</span> {{ $tieneAlergias ? $paciente->antecedentes_alergias : '__________________________________________________________________________________' }}
            </td>
        </tr>
    </table>

    <!-- Grilla Dual: Necesidades + Signos Vitales (Izquierda) / Escala de Glasgow (Derecha) -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 2px;">
        <tr>
            <!-- Columna Izquierda -->
            <td style="width: 48%; vertical-align: top; padding-right: 4px;">
                <!-- Necesidad -->
                <table class="border-table table-center" style="margin-bottom: 6px;">
                    <thead>
                        <tr style="background-color: #f1f5f9;">
                            <th style="width: 50%; text-align: left; font-size: 8px;">NECESIDAD</th>
                            <th style="width: 25%; font-size: 8px;">NORMAL</th>
                            <th style="width: 25%; font-size: 8px;">ALTERADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align: left; font-weight: 700; font-size: 8px;">CONCIENCIA</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700; font-size: 8px;">RESPIRACIÓN</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700; font-size: 8px;">PIEL</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700; font-size: 8px;">SENSORIAL</td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700; font-size: 8px;">MOVILIDAD</td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>

                <!-- Signos Vitales y Otros -->
                <table class="border-table table-center">
                    <thead>
                        <tr>
                            <th colspan="7" class="section-header" style="text-align: center; font-size: 8px;">SIGNOS VITALES Y OTROS HORA</th>
                        </tr>
                        <tr style="background-color: #f8fafc; font-size: 7.5px;">
                            <th style="width: 28%; text-align: left;">PARÁMETRO</th>
                            <th style="width: 12%;"></th>
                            <th style="width: 12%;"></th>
                            <th style="width: 12%;"></th>
                            <th style="width: 12%;"></th>
                            <th style="width: 12%;"></th>
                            <th style="width: 12%;"></th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 8px;">
                        <tr>
                            <td style="text-align: left; font-weight: 700;">HORA</td>
                            <td>{{ $horaEntrada }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">F.C. (lpm)</td>
                            <td>{{ $historia?->fc ?? '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">P.A. (mmHg)</td>
                            <td>{{ $historia?->pa ?? '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">F.R. (rpm)</td>
                            <td>{{ $historia?->fr ?? '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">SAT O2 (%)</td>
                            <td>{{ $historia?->sato2 ? $historia->sato2 . '%' : '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">T° (°C)</td>
                            <td>{{ $historia?->temperatura ? $historia->temperatura . '°' : '' }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">NÁUSEAS</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">VÓMITO</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">DIURESIS</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: left; font-weight: 700;">DEPOSICIÓN</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </td>

            <!-- Columna Derecha: Escala de Glasgow -->
            <td style="width: 52%; vertical-align: top; padding-left: 4px;">
                <table class="border-table" style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th colspan="3" class="section-header" style="text-align: center; font-size: 8.5px;">ESCALA DE COMA DE GLASGOW</th>
                        </tr>
                    </thead>
                    <tbody style="font-size: 7.8px;">
                        <!-- Ocular -->
                        <tr>
                            <td rowspan="4" style="width: 32%; font-weight: 800; text-align: center; background-color: #f8fafc; font-size: 8px;">
                                APERTURA<br>OCULAR
                            </td>
                            <td style="width: 54%;">ESPONTÁNEA</td>
                            <td style="width: 14%; text-align: center; font-weight: 700;">4</td>
                        </tr>
                        <tr>
                            <td>AL HABLA</td>
                            <td style="text-align: center; font-weight: 700;">3</td>
                        </tr>
                        <tr>
                            <td>AL DOLOR</td>
                            <td style="text-align: center; font-weight: 700;">2</td>
                        </tr>
                        <tr>
                            <td>NULA</td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                        </tr>

                        <!-- Verbal -->
                        <tr>
                            <td rowspan="5" style="font-weight: 800; text-align: center; background-color: #f8fafc; font-size: 8px;">
                                RESPUESTA<br>VERBAL
                            </td>
                            <td>ORIENTADO</td>
                            <td style="text-align: center; font-weight: 700;">5</td>
                        </tr>
                        <tr>
                            <td>CONFUSO</td>
                            <td style="text-align: center; font-weight: 700;">4</td>
                        </tr>
                        <tr>
                            <td>PALABRAS INAPROPIADAS</td>
                            <td style="text-align: center; font-weight: 700;">3</td>
                        </tr>
                        <tr>
                            <td>GRUÑIDOS</td>
                            <td style="text-align: center; font-weight: 700;">2</td>
                        </tr>
                        <tr>
                            <td>NULA</td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                        </tr>

                        <!-- Motora -->
                        <tr>
                            <td rowspan="6" style="font-weight: 800; text-align: center; background-color: #f8fafc; font-size: 8px;">
                                RESPUESTA<br>MOTORA
                            </td>
                            <td>OBEDECE ÓRDENES</td>
                            <td style="text-align: center; font-weight: 700;">6</td>
                        </tr>
                        <tr>
                            <td>LOCALIZA EL DOLOR</td>
                            <td style="text-align: center; font-weight: 700;">5</td>
                        </tr>
                        <tr>
                            <td>RETIRADA AL DOLOR</td>
                            <td style="text-align: center; font-weight: 700;">4</td>
                        </tr>
                        <tr>
                            <td>REFLEJO FLEXOR</td>
                            <td style="text-align: center; font-weight: 700;">3</td>
                        </tr>
                        <tr>
                            <td>REFLEJO EXTENSOR</td>
                            <td style="text-align: center; font-weight: 700;">2</td>
                        </tr>
                        <tr>
                            <td>NULO</td>
                            <td style="text-align: center; font-weight: 700;">1</td>
                        </tr>
                        <tr style="background-color: #f1f5f9; font-weight: 800;">
                            <td colspan="2" style="text-align: right; font-size: 8.5px; padding-right: 8px;">PUNTUACIÓN TOTAL:</td>
                            <td style="text-align: center; font-size: 9px;">/ 15</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- Firmas -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sign-line"></div>
                <strong>ENFERMERA(O) DE TURNO</strong><br>
                <span style="font-size: 7.5px; color: #475569;">Firma y Sello</span>
            </td>
            <td>
                <div class="sign-line"></div>
                <strong>MÉDICO DE GUARDIA</strong><br>
                <span style="font-size: 7.5px; color: #475569;">Firma y Sello</span>
            </td>
        </tr>
    </table>
</body>
</html>

