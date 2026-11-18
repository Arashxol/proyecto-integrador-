@extends('layouts.app')
@section('title','Órdenes Pendientes - DIALAB')
@section('top_title','Órdenes Pendientes')
@section('top_subtitle','Lista de trabajo del laboratorio')

@section('content')
<div class="card shadow-sm">
<div class="card-header bg-white d-flex justify-content-between"><span><i class="bi bi-clipboard2-pulse"></i> Lista de trabajo</span><span class="badge bg-primary">{{ $ordenesPendientes->count() }} pendientes</span></div>
<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Orden</th><th>Paciente</th><th>Exámenes</th><th>Fecha</th><th class="text-center">Acción</th></tr></thead><tbody>
@forelse($ordenesPendientes as $orden)
<tr><td class="fw-semibold">{{ $orden->codigo_orden }}</td><td>{{ $orden->paciente->nombre_completo }}<br><small class="text-muted">CI: {{ $orden->paciente->ci }}</small></td><td>@foreach($orden->muestras as $muestra)<span class="badge bg-secondary mb-1">{{ $muestra->tipoAnalisis->nombre }}</span> @endforeach</td><td>{{ \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') }}</td><td class="text-center"><button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalOrden{{ $orden->id }}"><i class="bi bi-pencil"></i> Resultados</button></td></tr>

<div class="modal fade" id="modalOrden{{ $orden->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content">
<div class="modal-header bg-success text-white"><h5 class="modal-title">Registrar Resultados — {{ $orden->codigo_orden }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
<form action="{{ route('laboratorio.guardar_resultados', $orden->id) }}" method="POST">@csrf
<div class="modal-body bg-light">
<p><strong>Paciente:</strong> {{ $orden->paciente->nombre_completo }}</p>
@foreach($orden->muestras as $muestra)
<div class="card mb-3"><div class="card-header bg-white fw-semibold">{{ $muestra->tipoAnalisis->nombre }}</div><div class="card-body row">
<div class="col-md-6 mb-2"><label class="form-label fw-bold text-success">Resultado *</label><input type="text" name="resultados[{{ $muestra->resultado->id }}][valor]" class="form-control" required placeholder="Ej: 95 mg/dL"></div>
<div class="col-md-6 mb-2"><label class="form-label">Referencia</label><input type="text" class="form-control bg-light" value="{{ $muestra->tipoAnalisis->valores_referencia }}" readonly></div>
<div class="col-12"><label class="form-label">Interpretación</label><input type="text" name="resultados[{{ $muestra->resultado->id }}][interpretacion]" class="form-control" placeholder="Opcional"></div>
</div></div>
@endforeach
</div>
<div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Guardar y Completar</button></div>
</form>
</div></div></div>
@empty<tr><td colspan="5" class="text-center py-4 text-muted">No hay órdenes pendientes.</td></tr>@endforelse
</tbody></table></div>
</div>
@endsection
