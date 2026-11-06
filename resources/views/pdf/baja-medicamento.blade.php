<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Baja de Medicamentos - {{ $baja->codigo }}</title>
    <style>
        @page {
            margin: 20px 25px 20px 25px;
            size: letter landscape;
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
            height: 38px;
            max-width: 180px;
            object-fit: contain;
        }
        .header-title {
            width: 55%;
            vertical-align: middle;
            text-align: center;
        }
        .header-title h1 {
            font-size: 11px;
            font-weight: 800;
            color: #1e3a8a;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-title h2 {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            margin: 3px 0 0 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-box-num {
            width: 20%;
            vertical-align: top;
            text-align: right;
        }
        .box-num-container {
            display: inline-block;
            border: 1.5px solid #0f172a;
            width: 120px;
            text-align: center;
        }
        .box-num-header {
            font-weight: 800;
            font-size: 8px;
            background: #f1f5f9;
            border-bottom: 1.2px solid #0f172a;
            padding: 1.5px 0;
            text-transform: uppercase;
        }
        .box-num-value {
            font-weight: 800;
            font-size: 11px;
            font-family: monospace;
            padding: 3px 0;
            color: #0f172a;
        }

        /* Tabla de Información General */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-bottom: 6px;
        }
        .meta-table td {
            border: 1.2px solid #0f172a;
            padding: 3px 6px;
            font-size: 8px;
            vertical-align: middle;
        }
        .meta-label {
            font-weight: 800;
            text-transform: uppercase;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 8px;
        }
        .meta-val {
            font-weight: 600;
            color: #0f172a;
        }
        .badge-status {
            display: inline-block;
            padding: 1px 6px;
            font-size: 7.5px;
            font-weight: 800;
            text-transform: uppercase;
            border: 1px solid #0f172a;
        }
        .badge-aprobado {
            background-color: #dcfce7;
            color: #166534;
        }
        .badge-pendiente {
            background-color: #fef9c3;
            color: #854d0e;
        }
        .badge-rechazado {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Tabla de Medicamentos Dados de Baja */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.2px solid #0f172a;
            margin-top: 2px;
            margin-bottom: 6px;
        }
        .items-title-row th {
            border: 1.2px solid #0f172a;
            background-color: #f1f5f9;
            font-weight: 800;
            text-align: center;
            font-size: 8.5px;
            padding: 3.5px 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-header-row th {
            border: 1.2px solid #0f172a;
            background-color: #f8fafc;
            font-weight: 800;
            font-size: 7.5px;
            padding: 3px 4px;
            text-align: center;
            text-transform: uppercase;
        }
        .items-table td {
            border: 1px solid #0f172a;
            padding: 2.5px 4px;
            font-size: 8px;
            vertical-align: middle;
            min-height: 15px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .font-mono {
            font-family: monospace;
        }
        .font-bold {
            font-weight: bold;
        }

        /* Sección Justificación */
        .justificacion-box {
            border: 1.2px solid #0f172a;
            margin-bottom: 14px;
            padding: 4px 6px;
            min-height: 38px;
        }
        .justificacion-header {
            font-weight: 800;
            font-size: 8px;
            text-transform: uppercase;
            margin-bottom: 2px;
            color: #1e293b;
        }
        .justificacion-text {
            font-size: 8px;
            color: #0f172a;
            line-height: 1.3;
        }

        /* Sección de Firmas */
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .signature-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0 40px;
        }
        .signature-box {
            border: 1.2px solid #0f172a;
            height: 52px;
            margin-bottom: 3px;
            background: #ffffff;
            position: relative;
        }
        .signature-box-content {
            position: absolute;
            bottom: 3px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7px;
            color: #475569;
        }
        .signature-title {
            font-weight: 800;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #0f172a;
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
                <h1>CLINICA BOLIVIANA AMERICANA</h1>
                <h2>
                    @if (mb_strtoupper(trim($baja->motivo)) === 'VENCIMIENTO')
                        FORMULARIO DE BAJA DE MEDICAMENTOS POR VENCIMIENTO
                    @else
                        FORMULARIO DE BAJA DE MEDICAMENTOS ({{ mb_strtoupper($baja->motivo) }})
                    @endif
                </h2>
            </td>
            <td class="header-box-num">
                <div class="box-num-container">
                    <div class="box-num-header">CÓDIGO ACTA</div>
                    <div class="box-num-value">{{ $baja->codigo }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Datos de Registro -->
    <table class="meta-table">
        <tr>
            <td class="meta-label" style="width: 15%;">FECHA DE REGISTRO:</td>
            <td class="meta-val" style="width: 18%;">{{ $baja->fecha_registro?->format('d/m/Y') }}</td>
            <td class="meta-label" style="width: 18%;">ÁREA / SERVICIO SOLICITANTE:</td>
            <td class="meta-val" style="width: 25%;">{{ $baja->area_solicitante }}</td>
            <td class="meta-label" style="width: 10%;">ESTADO:</td>
            <td class="meta-val" style="width: 14%;">
                @if ($baja->estado === 'Aprobado')
                    <span class="badge-status badge-aprobado">Aprobado</span>
                @elseif ($baja->estado === 'Rechazado')
                    <span class="badge-status badge-rechazado">Rechazado</span>
                @else
                    <span class="badge-status badge-pendiente">Pendiente</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="meta-label">RESPONSABLE:</td>
            <td class="meta-val" colspan="3">{{ $baja->responsable }}</td>
            <td class="meta-label">SUCURSAL:</td>
            <td class="meta-val">{{ $baja->sucursal?->nombre ?? 'Sede Central' }}</td>
        </tr>
        @if ($baja->esAprobado() && $baja->aprobadoPor)
            <tr>
                <td class="meta-label">APROBADO POR:</td>
                <td class="meta-val" colspan="3">{{ $baja->aprobadoPor->nombre_completo }}</td>
                <td class="meta-label">FECHA APROB.:</td>
                <td class="meta-val font-mono">{{ $baja->fecha_aprobacion?->format('d/m/Y H:i') }}</td>
            </tr>
        @endif
    </table>

    <!-- Tabla Detalle de Medicamentos Dados de Baja -->
    @php
        $detalles = $baja->detalles;
        $totalItems = $detalles->count();
        $minFilas = max(10, $totalItems);
        $totalCantidad = 0;
    @endphp

    <table class="items-table">
        <thead>
            <tr class="items-title-row">
                <th colspan="9">DETALLE DE MEDICAMENTOS DADOS DE BAJA</th>
            </tr>
            <tr class="items-header-row">
                <th style="width: 4%;">Nº</th>
                <th style="width: 26%; text-align: left;">Nombre del medicamento</th>
                <th style="width: 12%;">Forma farmacéutica</th>
                <th style="width: 10%;">Concentración</th>
                <th style="width: 10%;">Lote</th>
                <th style="width: 10%;">Fecha de vencimiento</th>
                <th style="width: 7%;">Cantidad</th>
                <th style="width: 11%;">Stock/Inventario de origen</th>
                <th style="width: 10%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < $minFilas; $i++)
                @php
                    $d = $detalles[$i] ?? null;
                    if ($d) {
                        $totalCantidad += (int) $d->cantidad;
                    }
                @endphp
                <tr>
                    <td class="text-center font-mono font-bold">{{ $i + 1 }}</td>
                    <td>
                        @if ($d)
                            <strong>{{ $d->producto?->nombre ?? 'Medicamento' }}</strong>
                            @if ($d->producto?->marca)
                                <span style="font-size: 7px; color: #64748b; display: block;">{{ $d->producto->marca->nombre }}</span>
                            @endif
                        @else
                            &nbsp;
                        @endif
                    </td>
                    <td class="text-center">{{ $d?->forma_farmaceutica ?: ($d?->producto?->unidad_medida ?? '') }}</td>
                    <td class="text-center">{{ $d?->concentracion ?: ($d?->producto?->descripcion ?? '') }}</td>
                    <td class="text-center font-mono font-bold">{{ $d?->lote?->codigo_lote ?? '' }}</td>
                    <td class="text-center font-mono">{{ $d?->fecha_vencimiento ? $d->fecha_vencimiento->format('d/m/Y') : ($d?->lote?->fecha_vencimiento?->format('d/m/Y') ?? '') }}</td>
                    <td class="text-center font-mono font-bold">{{ $d ? $d->cantidad : '' }}</td>
                    <td class="text-center">{{ $d?->seccion?->nombre ?? '' }}</td>
                    <td>{{ $d?->observaciones ?? '' }}</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: 800;">
                <td colspan="6" style="text-align: right; text-transform: uppercase; padding: 3px 6px;">
                    TOTAL UNIDADES DADAS DE BAJA:
                </td>
                <td class="text-center font-mono font-bold" style="font-size: 9px;">
                    {{ $totalCantidad }}
                </td>
                <td colspan="2">&nbsp;</td>
            </tr>
        </tfoot>
    </table>

    <!-- Justificación de la Baja -->
    <div class="justificacion-box">
        <div class="justificacion-header">JUSTIFICACIÓN DE LA BAJA:</div>
        <div class="justificacion-text">
            {{ $baja->justificacion }}
            @if (! empty($baja->observaciones_aprobacion))
                <div style="margin-top: 3px; font-weight: bold; color: #1e3a8a;">
                    NOTA DE APROBACIÓN: {{ $baja->observaciones_aprobacion }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bloque de Firmas -->
    <table class="signatures-table">
        <tr>
            <td class="signature-cell">
                <div class="signature-box">
                    @if ($baja->esAprobado() && $baja->aprobadoPor)
                        <div class="signature-box-content">
                            <strong>{{ $baja->aprobadoPor->nombre_completo }}</strong><br>
                            Aprobado el {{ $baja->fecha_aprobacion?->format('d/m/Y H:i') }}
                        </div>
                    @endif
                </div>
                <div class="signature-title">SELLO Y FIRMA ADMINISTRACIÓN</div>
            </td>
            <td class="signature-cell">
                <div class="signature-box">
                    <div class="signature-box-content">
                        <strong>{{ $baja->responsable }}</strong><br>
                        Solicitado el {{ $baja->fecha_registro?->format('d/m/Y') }}
                    </div>
                </div>
                <div class="signature-title">SELLO Y FIRMA FARMACIA</div>
            </td>
        </tr>
    </table>

</body>
</html>

