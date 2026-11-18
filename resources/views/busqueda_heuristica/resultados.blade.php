@extends('layouts.app')

@section('title','Resultados de Búsqueda Heurística - DIALAB')
@section('top_title','Resultados de Detección de Anomalías')
@section('top_subtitle','Análisis de datos para Machine Learning')

@section('content')
<div class="container-fluid mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('analisis.heuristico.index') }}">Detección de Anomalías</a></li>
            <li class="breadcrumb-item active">Resultados</li>
        </ol>
    </nav>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-table"></i> Resultados Disponibles para Análisis</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Código Orden</th>
                                    <th>Paciente</th>
                                    <th>Análisis</th>
                                    <th>Categoría</th>
                                    <th>Resultado</th>
                                    <th>Referencia</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                            @forelse($resultados as $r)
                            <tr>
                                <td>{{ $r->id }}</td>
                                <td>{{ $r->muestra->orden->codigo_orden }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $r->muestra->orden->paciente->nombre_completo }}</span><br>
                                    <small class="text-muted">CI: {{ $r->muestra->orden->paciente->ci }}</small>
                                </td>
                                <td>{{ $r->muestra->tipoAnalisis->nombre }}</td>
                                <td><span class="badge bg-secondary">{{ $r->muestra->tipoAnalisis->categoria }}</span></td>
                                <td>{{ $r->resultado }}</td>
                                <td><small>{{ $r->valores_referencia }}</small></td>
                                <td><span class="badge bg-success">{{ $r->estado }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($r->fecha_validacion)->format('d/m/Y H:i') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No hay resultados validados para analizar.</td>
                            </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $resultados->links() }}
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Información del Proceso</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-primary">1. Extracción</h6>
                                <p class="small">Se extraen los resultados validados de la BD con todos sus campos relacionados.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-primary">2. Preprocesamiento</h6>
                                <p class="small">Se normalizan los datos: extracción de valores numéricos, rangos de referencia, encoding categórico.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="bg-light p-3 rounded">
                                <h6 class="text-primary">3. Detección</h6>
                                <p class="small">Isolation Forest analiza los datos y marca las anomalías basándose en el patrón aprendido.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection