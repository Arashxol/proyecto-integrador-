@extends('layouts.app')
@section('title','Dashboard')
@section('top_title','Dashboard')
@section('top_subtitle','Panel principal del laboratorio')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body d-flex justify-content-between">
            <div>
                <p class="text-muted small mb-0">Pacientes Registrados</p>
                <h3 class="fw-bold mb-0">{{ $pacientes ?? \App\Models\Paciente::count() }}</h3>
            </div>
            <i class="bi bi-people fs-2 text-primary"></i>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body d-flex justify-content-between">
            <div>
                <p class="text-muted small mb-0">Órdenes Pendientes</p>
                <h3 class="fw-bold mb-0 text-warning">{{ $ordenesPendientes ?? 0 }}</h3>
            </div>
            <i class="bi bi-clipboard-pulse fs-2 text-warning"></i>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm"><div class="card-body d-flex justify-content-between">
            <div>
                <p class="text-muted small mb-0">Muestras en Análisis</p>
                <h3 class="fw-bold mb-0 text-info">{{ $muestrasEnProceso ?? 0 }}</h3>
            </div>
            <i class="bi bi-droplet fs-2 text-info"></i>
        </div></div>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-secondary text-white">Fase 1: Admisión de Paciente y Orden</div>
    <div class="card-body">
        <form action="{{ route('laboratorio.registrar') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nombre Completo del Paciente</label>
                <input type="text" name="nombre_completo" class="form-control" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Cédula de Identidad (CI)</label>
                    <input type="text" name="ci" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Edad</label>
                    <input type="number" name="edad" class="form-control" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Sexo</label>
                    <select name="sexo" class="form-select" required>
                        <option value="Masculino">M</option>
                        <option value="Femenino">F</option>
                    </select>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Médico Solicitante</label>
                <input type="text" name="medico_solicitante" class="form-control" placeholder="Ej: Dr. Alvarez">
            </div>
            <div class="mb-3">
                <label class="form-label">Selección de Análisis Requeridos</label>
                <div class="p-3 border rounded bg-body" style="max-height: 180px; overflow-y: auto;">
                    @foreach(($examenesDisponibles ?? collect()) as $examen)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="examenes[]" value="{{ $examen->id }}" id="exam_{{ $examen->id }}">
                            <label class="form-check-label" for="exam_{{ $examen->id }}">
                                {{ $examen->nombre }} (<span class="text-success">{{ $examen->precio }} Bs</span>)
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="btn btn-success w-100">Generar Orden y Muestras</button>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Monitoreo General de Órdenes</h5>
        <span class="text-muted small">{{ now()->format('d/m/Y') }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Código</th><th>Paciente</th><th>Fecha</th><th>Total</th><th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse(($ordenes ?? collect()) as $orden)
                    <tr>
                        <td class="fw-semibold">{{ $orden->codigo_orden }}</td>
                        <td>{{ $orden->paciente->nombre_completo }}</td>
                        <td>{{ $orden->fecha_solicitud }}</td>
                        <td>{{ $orden->total }} Bs</td>
                        <td><span class="badge bg-warning text-dark">{{ $orden->estado }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">No hay órdenes procesadas el día de hoy.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
