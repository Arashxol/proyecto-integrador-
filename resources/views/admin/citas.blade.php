@extends('layouts.app')
@section('title','Citas - DIALAB')
@section('top_title','Gestión de Citas')
@section('top_subtitle','Agenda de pacientes')

@section('content')
<div class="card shadow-sm mb-3">
<div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4"><input name="buscar" value="{{ request('buscar') }}" placeholder="Nombre o CI" class="form-control"></div>
<div class="col-md-3"><input type="date" name="fecha" value="{{ request('fecha') }}" class="form-control"></div>
<div class="col-md-2"><select name="estado" class="form-select"><option value="">Todos</option>@foreach(['Pendiente','Confirmada','Cancelada','Completada'] as $e)<option value="{{ $e }}" @selected(request('estado')==$e)>{{ $e }}</option>@endforeach</select></div>
<div class="col-md-3 d-flex gap-1"><button class="btn btn-primary w-50">Filtrar</button><a href="{{ route('citas.index') }}" class="btn btn-outline-secondary w-50">Limpiar</a></div>
</form>
</div>
</div>
<div class="card shadow-sm">
<div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th>Paciente</th><th>Fecha/Hora</th><th>Tipo</th><th>Estado</th><th>Motivo</th><th>Acción</th></tr></thead><tbody>
@forelse($citas as $c)<tr><td>{{ $c->paciente->nombre_completo }}<br><small class="text-muted">CI: {{ $c->paciente->ci }}</small></td><td>{{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($c->hora)->format('H:i') }}</td><td>{{ $c->tipo }}</td><td><span class="badge bg-{{ $c->estado==='Confirmada'?'success':($c->estado==='Pendiente'?'warning':'secondary') }}">{{ $c->estado }}</span></td><td><small>{{ $c->motivo ?? '-' }}</small></td><td><form action="{{ route('citas.estado', $c->id) }}" method="POST" class="d-flex gap-1">@csrf<select name="estado" class="form-select form-select-sm">@foreach(['Pendiente','Confirmada','Cancelada','Completada'] as $e)<option value="{{ $e }}" @selected($c->estado==$e)>{{ $e }}</option>@endforeach</select><button class="btn btn-sm btn-primary">OK</button></form></td></tr>
@empty<tr><td colspan="6" class="text-center py-4 text-muted">No hay citas.</td></tr>@endforelse
</tbody></table></div>
<div class="card-footer">{{ $citas->links() }}</div>
</div>
@endsection
