@extends('layouts.app')
@section('title','Logs Auditoría - DIALAB')
@section('top_title','Logs de Auditoría')
@section('top_subtitle','Trazabilidad completa del sistema')

@section('content')
<div class="card shadow-sm mb-3">
<div class="card-header bg-white">Filtros</div>
<div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-3"><input name="accion" value="{{ request('accion') }}" placeholder="Acción" class="form-control"></div>
<div class="col-md-3"><input name="usuario" value="{{ request('usuario') }}" placeholder="Usuario / email" class="form-control"></div>
<div class="col-md-2"><input type="date" name="desde" value="{{ request('desde') }}" class="form-control"></div>
<div class="col-md-2"><input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control"></div>
<div class="col-md-2 d-flex gap-1"><button class="btn btn-primary w-50">Filtrar</button><a href="{{ route('admin.logs') }}" class="btn btn-outline-secondary w-50">Limpiar</a></div>
</form>
</div>
</div>
<div class="card shadow-sm">
<div class="card-header bg-white d-flex justify-content-between"><span>Total: {{ $logs->total() }}</span><span class="badge bg-dark">{{ $logs->currentPage() }}/{{ $logs->lastPage() }}</span></div>
<div class="table-responsive"><table class="table table-hover table-sm align-middle mb-0"><thead class="table-light"><tr><th>#</th><th>Fecha</th><th>Usuario</th><th>Acción</th><th>Tabla</th><th>Detalle</th></tr></thead><tbody>
@forelse($logs as $l)
<tr><td>{{ $l->id }}</td><td><small>{{ $l->created_at->format('d/m/Y H:i:s') }}</small></td><td>{{ $l->user->name ?? 'Sistema' }}<br><small class="text-muted">{{ $l->user->email ?? '-' }}</small></td><td>{{ $l->accion }}</td><td><code>{{ $l->tabla_afectada ?? '-' }}</code> {{ $l->registro_id ?? '' }}</td><td><small>{{ Str::limit($l->detalle, 80) }}<br><span class="text-muted">{{ $l->ip }}</span></small></td></tr>
@empty<tr><td colspan="6" class="text-center py-4 text-muted">Sin registros</td></tr>@endforelse
</tbody></table></div>
<div class="card-footer">{{ $logs->links() }}</div>
</div>
@endsection
