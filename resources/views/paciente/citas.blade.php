@extends('layouts.app')
@section('title','Mis Citas - DIALAB')
@section('top_title','Mis Citas')
@section('top_subtitle','Agenda de tomas y controles')

@section('content')
@if(!$paciente)
<div class="alert alert-warning">Tu usuario ({{ auth()->user()->email }}) no está vinculado a una ficha de paciente. Contacta a recepción.</div>
@else
<div class="row g-4">
<div class="col-lg-4">
<div class="card shadow-sm h-100"><div class="card-header bg-success text-white"><i class="bi bi-calendar-plus"></i> Agendar nueva cita</div><div class="card-body">
<form action="{{ route('paciente.citas.agendar') }}" method="POST">@csrf
<div class="mb-2"><label class="form-label">Fecha *</label><input type="date" name="fecha" class="form-control" required min="{{ date('Y-m-d') }}"></div>
<div class="mb-2"><label class="form-label">Hora *</label><input type="time" name="hora" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Tipo *</label><select name="tipo" class="form-select" required><option value="Toma de muestra">Toma de muestra</option><option value="Entrega de resultados">Entrega de resultados</option><option value="Consulta">Consulta</option><option value="Control">Control</option></select></div>
<div class="mb-3"><label class="form-label">Motivo</label><textarea name="motivo" class="form-control" rows="2" placeholder="Ej: Hemograma de control"></textarea></div>
<button class="btn btn-success w-100">📅 Solicitar cita</button>
</form>
</div></div>
<div class="card shadow-sm mt-3"><div class="card-body"><h6>{{ $paciente->nombre_completo }}</h6><small class="text-muted">CI: {{ $paciente->ci }} | {{ $paciente->email }}</small></div></div>
</div>
<div class="col-lg-8">
<div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between"><span>Mis citas ({{ $citas->count() }})</span><span class="badge bg-secondary">Pendiente = por confirmar</span></div><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th>Fecha/Hora</th><th>Tipo</th><th>Estado</th><th>Motivo</th><th></th></tr></thead><tbody>
@forelse($citas as $c)<tr><td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }}<br><small>{{ \Carbon\Carbon::parse($c->hora)->format('H:i') }}</small></td><td>{{ $c->tipo }}</td><td><span class="badge bg-{{ $c->estado==='Confirmada'?'success':($c->estado==='Pendiente'?'warning':'secondary') }}">{{ $c->estado }}</span></td><td><small>{{ $c->motivo ?? '-' }}</small></td><td>@if($c->estado==='Pendiente')<form action="{{ route('paciente.citas.cancelar', $c->id) }}" method="POST" onsubmit="return confirm('¿Cancelar?')">@csrf<button class="btn btn-sm btn-outline-danger">Cancelar</button></form>@endif</td></tr>
@empty<tr><td colspan="5" class="text-center py-4 text-muted">Aún no tienes citas.</td></tr>@endforelse
</tbody></table></div></div>
</div>
</div>
@endif
@endsection
