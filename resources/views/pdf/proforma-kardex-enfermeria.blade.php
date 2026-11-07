<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Kardex de Enfermería - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        @page {
            margin: 12px 20px 12px 20px;
            size: letter portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #0f172a;
            font-size: 8px;
            line-height: 1.15;
            margin: 0;
            padding: 0;
        }
        .slip-container {
            width: 100%;
            height: 485px;
            box-sizing: border-box;
        }
        .cut-line {
            border-top: 1px dashed #94a3b8;
            margin: 8px 0;
            text-align: center;
            position: relative;
        }
        .cut-line span {
            position: relative;
            top: -7px;
            background-color: #ffffff;
            padding: 0 8px;
            color: #64748b;
            font-size: 7.5px;
            font-weight: 600;
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
            height: 30px;
            max-width: 130px;
            object-fit: contain;
        }
        .header-title {
            width: 75%;
            vertical-align: middle;
            text-align: center;
            padding-right: 20%;
        }
        .header-title h1 {
            font-size: 13px;
            font-weight: 800;
            color: #1e3a8a;
            letter-spacing: 0.5px;
            margin: 0;
            text-transform: uppercase;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #0f172a;
            margin-bottom: 4px;
        }
        .meta-table td, .meta-table th {
            border: 1px solid #0f172a;
            padding: 2px 3px;
            vertical-align: middle;
            font-size: 7.5px;
        }
        .lbl {
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            font-size: 7px;
        }
        .val {
            font-weight: 600;
            color: #0f172a;
        }

        .kardex-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #0f172a;
        }
        .kardex-table th {
            border: 1px solid #0f172a;
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 800;
            font-size: 7px;
            padding: 2.5px 2px;
            text-align: center;
            text-transform: uppercase;
        }
        .kardex-table td {
            border: 1px solid #334155;
            height: 18px;
            text-align: center;
            font-size: 7.5px;
            padding: 1px 2px;
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
        $edadTexto = $edad !== null ? "{$edad}a" : '---';
        $medicosNombres = $proforma->medicos->pluck('nombre_completo')->filter()->join(', ');
        $cirugia = $proforma->cirugias->first();
        $operacionNombre = $cirugia ? ($cirugia->cirugia_efectuada ?? $cirugia->diagnostico_operacion) : '';
        $pieza = $proforma->pieza ?? $cirugia?->cama ?? $proforma->epicrisis?->cama ?? '---';
        $servicioNombre = $proforma->servicios->first()?->servicio?->nombre ?? ($proforma->tipo_atencion ?: 'HOSPITALIZACIÓN');
    @endphp

    @for ($slip = 1; $slip <= 2; $slip++)
        <div class="slip-container">
            <!-- Cabecera -->
            <table class="header-table">
                <tr>
                    <td class="header-logo">
                        @if ($logoBase64)
                            <img src="{{ $logoBase64 }}" alt="Clínica Boliviana Americana">
                        @else
                            <strong style="color: #1e3a8a; font-size: 10px;">CLÍNICA BOLIVIANA AMERICANA</strong>
                        @endif
                    </td>
                    <td class="header-title">
                        <h1>KARDEX DE ENFERMERIA</h1>
                    </td>
                </tr>
            </table>

            <!-- Metadatos Paciente -->
            <table class="meta-table">
                <tr>
                    <td style="width: 38%;">
                        <span class="lbl">NOMBRE:</span>
                        <span class="val">{{ $paciente->nombre_completo ?? '---' }}</span>
                    </td>
                    <td style="width: 7%;">
                        <span class="lbl">SEXO:</span>
                        <span class="val">{{ $paciente->genero ?? '---' }}</span>
                    </td>
                    <td style="width: 9%;">
                        <span class="lbl">EDAD:</span>
                        <span class="val">{{ $edadTexto }}</span>
                    </td>
                    <td style="width: 9%;">
                        <span class="lbl">PESO:</span>
                        <span class="val">{{ $historia?->peso ? $historia->peso . 'k' : '---' }}</span>
                    </td>
                    <td style="width: 9%;">
                        <span class="lbl">TALLA:</span>
                        <span class="val">{{ $historia?->talla ? $historia->talla . 'c' : '---' }}</span>
                    </td>
                    <td style="width: 8%;">
                        <span class="lbl">SALA:</span>
                        <span class="val">{{ $cirugia?->sala ?? $pieza }}</span>
                    </td>
                    <td style="width: 8%;">
                        <span class="lbl">CAMA:</span>
                        <span class="val">{{ $cirugia?->cama ?? $proforma->epicrisis?->cama ?? $pieza }}</span>
                    </td>
                    <td style="width: 12%;">
                        <span class="lbl">N° HC:</span>
                        <span class="val">{{ $paciente->numero_historia_clinica ?? $proforma->id }}</span>
                    </td>
                </tr>
                <tr>
                    <td style="width: 38%;">
                        <span class="lbl">MÉDICO:</span>
                        <span class="val">{{ !empty($medicosNombres) ? $medicosNombres : '---' }}</span>
                    </td>
                    <td colspan="3" style="width: 25%;">
                        <span class="lbl">ÁREA:</span>
                        <span class="val">{{ $servicioNombre }}</span>
                    </td>
                    <td colspan="2" style="width: 17%;">
                        <span class="lbl">DIAGNÓSTICO:</span>
                        <span class="val" style="font-size: 7px;">{{ Str::limit($historia?->diagnostico ?? $proforma->diagnostico ?? '---', 22) }}</span>
                    </td>
                    <td colspan="2" style="width: 20%;">
                        <span class="lbl">OPERACIÓN:</span>
                        <span class="val" style="font-size: 7px;">{{ Str::limit($operacionNombre ?: '---', 20) }}</span>
                    </td>
                </tr>
            </table>

            <!-- Tabla Kardex de Cuidados -->
            <table class="kardex-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">FECHA</th>
                        <th style="width: 6%;">HORA</th>
                        <th style="width: 26%;">EXAMEN COMPLEMENTARIOS</th>
                        <th style="width: 5%;">DC</th>
                        <th style="width: 31%;">CUIDADOS DE ENFERMERIA</th>
                        <th style="width: 5%;">DC</th>
                        <th style="width: 19%;">DIETA</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($r = 0; $r < 18; $r++)
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        @if ($slip === 1)
            <div class="cut-line">
                <span>✂ CORTE AQUÍ (KARDEX DUPLICADO) ✂</span>
            </div>
        @endif
    @endfor
</body>
</html>

