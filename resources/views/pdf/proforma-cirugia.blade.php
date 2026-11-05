<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Descargo Completo de Quirófano - {{ $proforma->paciente->nombre_completo ?? 'Paciente' }}</title>
    <style>
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <!-- PÁGINA 1: HOJA DE ANESTESIOLOGIA -->
    @include('pdf.proforma-cirugia-anestesiologia')

    <!-- Salto de página para la Hoja de Enfermería -->
    <div class="page-break"></div>

    <!-- PÁGINA 2: HOJA DE ENFERMERIA -->
    @include('pdf.proforma-cirugia-enfermeria')

</body>
</html>

