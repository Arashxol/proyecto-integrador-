@extends('layouts.app')
@section('title','Laboratorio - DIALAB')
@section('top_title','Panel Bioquímico')
@section('top_subtitle','Gestión de muestras y validación')

@section('content')
<div class="card shadow-sm border-0">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <span class="fw-semibold"><i class="bi bi-droplet"></i> Muestras Pendientes</span>
    <span class="badge bg-info">{{ $muestrasPendientes->count() }} pendientes</span>
  </div>
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead class="table-light"><tr><th>Código</th><th>Paciente</th><th>Examen</th><th>Tipo</th><th>Recolección</th><th class="text-center">Acción</th></tr></thead>
      <tbody>
      @forelse($muestrasPendientes as $muestra)
        <tr>
          <td><span class="fw-semibold">{{ $muestra->codigo_muestra }}</span></td>
          <td>{{ $muestra->orden->paciente->nombre_completo }}<br><small class="text-muted">CI: {{ $muestra->orden->paciente->ci }}</small></td>
          <td>{{ $muestra->tipoAnalisis->nombre }}<br><small class="text-muted">{{ $muestra->tipoAnalisis->categoria }}</small></td>
          <td><span class="badge bg-secondary">{{ $muestra->tipo_muestra }}</span></td>
          <td>{{ \Carbon\Carbon::parse($muestra->fecha_recoleccion)->format('d/m/Y H:i') }}</td>
          <td class="text-center">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalResultado{{ $muestra->id }}"><i class="bi bi-pencil-square"></i> Resultado</button>
            <a href="{{ route('muestras.show',$muestra->id) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i></a>
          </td>
        </tr>

        <div class="modal fade" id="modalResultado{{ $muestra->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog"><div class="modal-content">
            <div class="modal-header bg-primary text-white"><h5 class="modal-title">{{ $muestra->tipoAnalisis->nombre }}</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <form action="{{ route('laboratorio.guardar_resultado', $muestra->id) }}" method="POST">@csrf
              <div class="modal-body">
                <p><strong>Paciente:</strong> {{ $muestra->orden->paciente->nombre_completo }}</p>
                <div class="mb-2"><label class="form-label fw-bold text-primary">Resultado *</label><textarea name="resultado" class="form-control" rows="2" required placeholder="Ej: Glucosa 95 mg/dL"></textarea></div>
                <div class="mb-2"><label class="form-label">Valores de Referencia</label><input type="text" name="valores_referencia" class="form-control bg-light" value="{{ $muestra->tipoAnalisis->valores_referencia }}" readonly><small class="text-muted">Predefinido por el sistema</small></div>
                <div class="mb-2"><label class="form-label">Interpretación</label><textarea name="interpretacion" class="form-control" rows="2" placeholder="Valores dentro del rango..."></textarea></div>
              </div>
              <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success">Guardar y Validar</button></div>
            </form>
          </div></div>
        </div>
      @empty
        <tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-check-circle fs-3"></i><p class="mb-0">¡Excelente! No hay muestras pendientes.</p></td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
