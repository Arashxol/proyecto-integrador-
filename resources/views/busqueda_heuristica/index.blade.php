@extends('layouts.app')

@section('title','Detección de Anomalías - DIALAB')
@section('top_title','Detección de Anomalías (ML)')
@section('top_subtitle','Búsqueda heurística con Machine Learning')

@section('content')
<div class="row g-4 mb-4">
    <!-- Estadísticas de datos disponibles -->
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Resultados Validados</div>
                        <h3 class="mb-0">{{ $totalResultados }}</h3>
                    </div>
                    <i class="bi bi-clipboard-check fs-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Muestras Procesadas</div>
                        <h3 class="mb-0">{{ $totalMuestras }}</h3>
                    </div>
                    <i class="bi bi-droplet fs-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Pacientes en BD</div>
                        <h3 class="mb-0">{{ $totalPacientes }}</h3>
                    </div>
                    <i class="bi bi-people fs-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card shadow-sm text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="text-muted small">Tipos de Análisis</div>
                        <h3 class="mb-0">{{ $totalExamenes }}</h3>
                    </div>
                    <i class="bi bi-bar-chart fs-4 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Panel principal de detección -->
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="bi bi-search"></i> Ejecutar Búsqueda Heurística (A*)
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-3">
                            <label class="form-label">¿Qué hace este módulo?</label>
                            <p class="text-muted small">
                                Utiliza el algoritmo <strong>A* (A Estrella)</code> y <strong>Isolation Forest</strong> para analizar los resultados de laboratorio y detectar patrones inusuales (anomalías) que podrían indicar errores de medición, contaminación de muestras o condiciones médicas atípicas que requieren revisión del bioquímico.
                            </p>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> <strong>Datos necesarios:</strong> Al menos 5 resultados validados en la base de datos para entrenar el modelo. Actualmente tienes <strong>{{ $totalResultados }}</strong> resultados.
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        @if($suficientesDatos)
                            <button id="btnEjecutar" class="btn btn-success w-100 btn-lg">
                                <i class="bi bi-play-circle"></i> Ejecutar Detección
                            </button>
                        @else
                            <button class="btn btn-outline-secondary w-100 btn-lg" disabled>
                                <i class="bi bi-exclamation-triangle"></i> Datos insuficientes
                            </button>
                        @endif
                    </div>
                </div>

                <div id="resultadoEjecucion" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="bi bi-check-circle"></i> Detección Completada</h5>
                        <div id="mensajeResultado"></div>
                        <pre id="salidaPython" class="bg-light p-3 rounded small"></pre>
                    </div>
                    <a href="{{ route('analisis.heuristico.resultados') }}" class="btn btn-outline-primary mt-3">
                        <i class="bi bi-table"></i> Ver Resultados Detallados
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel lateral con información -->
    <div class="col-lg-4">
        <div class="card shadow-sm mb-3">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-info-circle"></i> Información del Modelo</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Algoritmo:</strong> Isolation Forest
                    </li>
                    <li class="list-group-item">
                        <strong>Tipo:</strong> No supervisado (sin etiquetas previas)
                    </li>
                    <li class="list-group-item">
                        <strong>Datos analizados:</strong> Edad, sexo, tipo de examen, resultado, valores de referencia, fecha, categoría
                    </li>
                    <li class="list-group-item">
                        <strong>Umbral:</strong> 5% de datos esperados como anomalías
                    </li>
                    <li class="list-group-item">
                        <strong>Salida:</strong> Gráficos y lista de resultados anómalos
                    </li>
                </ul>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h6 class="mb-0"><i class="bi bi-clock"></i> Resultados Recientes</h6>
            </div>
            <div class="card-body">
                @forelse($resultadosRecientes as $r)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <div class="fw-semibold small">{{ $r->muestra->tipoAnalisis->nombre }}</div>
                        <div class="text-muted small">{{ $r->muestra->orden->paciente->nombre_completo }} (CI: {{ $r->muestra->orden->paciente->ci }})</div>
                    </div>
                    <span class="badge bg-{{ $r->estado==='Validado'?'success':'warning' }}">{{ $r->estado }}</span>
                </div>
                @empty
                <div class="text-muted small text-center">Sin resultados registrados</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('btnEjecutar').addEventListener('click', function() {
    const btn = document.getElementById('btnEjecutar');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-spin"></i> Procesando...';
    
    fetch("{{ route('analisis.heuristico.ejecutar') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const resultadoDiv = document.getElementById('resultadoEjecucion');
        const mensaje = document.getElementById('mensajeResultado');
        const salida = document.getElementById('salidaPython');
        
        resultadoDiv.style.display = 'block';
        
        if (data.exito) {
            resultadoDiv.className = 'alert alert-success';
            mensaje.innerHTML = '<strong>¡Éxito!</strong> ' + data.mensaje;
        } else {
            resultadoDiv.className = 'alert alert-danger';
            mensaje.innerHTML = '<strong>Error:</strong> ' + data.mensaje;
        }
        
        salida.textContent = data.salida || 'Sin salida del script';
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle"></i> Ejecutar Detección';
        
        // Scroll al resultado
        resultadoDiv.scrollIntoView({ behavior: 'smooth' });
    })
    .catch(error => {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-play-circle"></i> Ejecutar Detección';
        alert('Error al ejecutar la detección');
    });
});
</script>
@endpush