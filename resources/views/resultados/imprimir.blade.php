<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado - {{ $muestra->codigo_muestra }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #fff; font-family: Arial, sans-serif; }
        .membrete { border-bottom: 2px solid #0d6efd; padding-bottom: 15px; margin-bottom: 20px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="p-4">

    <!-- Botón para imprimir (Se oculta al generar el documento) -->
    <div class="text-end mb-3 no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir / Guardar como PDF</button>
    </div>

    <!-- Membrete del Laboratorio -->
    <div class="membrete text-center">
        <h2 class="text-primary mb-0">UNIVERSIDAD PRIVADA FRANZ TAMAYO</h2>
        <h3 class="mb-1">LABORATORIO CLÍNICO "DIALAB"</h3>
        <p class="mb-0 text-muted">La Paz - El Alto, Bolivia</p>
    </div>

    <!-- Datos del Paciente -->
    <div class="row mb-4 border p-3 rounded">
        <div class="col-md-6">
            <p class="mb-1"><strong>Paciente:</strong> {{ $muestra->orden->paciente->nombre_completo }}</p>
            <p class="mb-1"><strong>Cédula de Identidad:</strong> {{ $muestra->orden->paciente->ci }}</p>
            <p class="mb-0"><strong>Edad / Sexo:</strong> {{ $muestra->orden->paciente->edad }} años / {{ $muestra->orden->paciente->sexo }}</p>
        </div>
        <div class="col-md-6 text-md-end">
            <p class="mb-1"><strong>Orden N°:</strong> {{ $muestra->orden->codigo_orden }}</p>
            <p class="mb-1"><strong>Médico:</strong> {{ $muestra->orden->medico_solicitante }}</p>
            <p class="mb-0"><strong>Fecha Emisión:</strong> {{ \Carbon\Carbon::parse($muestra->resultado->fecha_validacion)->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    <!-- Resultado Médico -->
    <h4 class="text-center mb-4 text-decoration-underline">INFORME DE LABORATORIO</h4>
    
    <div class="mb-4">
        <h5>Examen: <span class="text-primary">{{ $muestra->tipoAnalisis->nombre }}</span></h5>
        <p class="text-muted">Categoría: {{ $muestra->tipoAnalisis->categoria }} | Muestra: {{ $muestra->tipo_muestra }}</p>
    </div>

    <table class="table table-bordered border-dark">
        <thead class="table-light">
            <tr>
                <th>Análisis</th>
                <th>Resultado Obtenido</th>
                <th>Valores de Referencia</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $muestra->tipoAnalisis->nombre }}</td>
                <td class="fw-bold">{{ $muestra->resultado->resultado }}</td>
                <td>{{ $muestra->resultado->valores_referencia ?? 'No especificado' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="mt-4 p-3 bg-light border">
        <strong>Interpretación / Observaciones:</strong><br>
        {{ $muestra->resultado->interpretacion ?? 'Sin observaciones clínicas.' }}
    </div>

    <!-- Firma del Validador -->
    <div class="mt-5 pt-5 text-center">
        <hr style="width: 250px; margin: 0 auto; border-color: #000;">
        <p class="mt-2 mb-0"><strong>Dr(a). Validado por: Bioquímico DIALAB</strong></p>
        <small class="text-muted">Firma y Sello Digital</small>
    </div>

</body>
</html>