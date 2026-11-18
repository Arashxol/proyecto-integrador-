@extends('layouts.app')

@section('title','Resultado de Detección - DIALAB')
@section('top_title','Resultado de Detección')
@section('top_subtitle','Análisis de resultado individual')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <!-- Alerta principal -->
            @if($esAnomala)
            <div class="card border-danger shadow-lg mb-4">
                <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> ¡RESULTADO ANÓMALO DETECTADO!</h5>
                    <span class="badge bg-light text-danger fs-5">⚠ REVISAR</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Análisis:</label>
                                <p class="fw-bold">{{ $examen->nombre }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Categoría:</label>
                                <p class="fw-bold">{{ $examen->categoria }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Resultado Obtenido:</label>
                                <p class="fw-bold text-danger">{{ $resultadoValor }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Rango de Referencia:</label>
                                <p class="fw-bold">{{ $referencia }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-danger p-3">
                                <h6 class="mb-2"><i class="bi bi-exclamation-circle"></i> ¿Por qué es anómalo?</h6>
                                <p class="mb-0">{{ $razon }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('deteccion.index') }}" class="btn btn-danger">
                            <i class="bi bi-arrow-left"></i> Evaluar Otro Resultado
                        </a>
                    </div>
                </div>
            </div>
            @else
            <div class="card border-success shadow-lg mb-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> Resultado Normal</h5>
                    <span class="badge bg-light text-success fs-5">✓ OK</span>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label fw-bold">Análisis:</label>
                                <p class="fw-bold">{{ $examen->nombre }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Categoría:</label>
                                <p class="fw-bold">{{ $examen->categoria }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Resultado Obtenido:</label>
                                <p class="fw-bold text-success">{{ $resultadoValor }}</p>
                            </div>
                            <div class="mb-2">
                                <label class="form-label fw-bold">Rango de Referencia:</label>
                                <p class="fw-bold">{{ $referencia }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-success p-3">
                                <h6 class="mb-2"><i class="bi bi-check-circle"></i> Estado</h6>
                                <p class="mb-0">El resultado está dentro del rango de referencia esperado. No se detectaron anomalías.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <a href="{{ route('deteccion.index') }}" class="btn btn-success w-100">
                            <i class="bi bi-arrow-left"></i> Evaluar Otro Resultado
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <!-- Información adicional de resultados similares -->
            @if($resultadosSimilares->count() > 0)
            <div class="card shadow-sm mt-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="bi bi-clock"></i> Resultados Similares Recientes</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Paciente</th>
                                <th>Resultado</th>
                                <th>Referencia</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($resultadosSimilares as $r)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($r->fecha_validacion)->format('d/m/Y') }}</td>
                            <td>{{ $r->muestra->orden->paciente->nombre_completo }}</td>
                            <td>{{ $r->resultado }}</td>
                            <td>{{ $r->valores_referencia }}</td>
                        </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            <div class="text-center mt-3">
                <a href="{{ route('deteccion.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection