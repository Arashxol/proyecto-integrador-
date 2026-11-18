@extends('layouts.app')
@section('title','Usuarios - DIALAB')
@section('top_title','Gestión de Usuarios')
@section('top_subtitle','Roles y auditoría')

@section('content')
<div class="row g-4">
<div class="col-lg-4">
<div class="card shadow-sm h-100"><div class="card-header bg-warning"><i class="bi bi-person-plus"></i> Crear Usuario</div><div class="card-body">
<form action="{{ route('admin.usuarios.crear') }}" method="POST">@csrf
<div class="mb-2"><label class="form-label">Nombre</label><input name="name" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Contraseña</label><input name="password" type="password" class="form-control" required></div>
<div class="mb-2"><label class="form-label">Rol</label><select name="rol" class="form-select" required><option value="Recepcionista">Recepcionista</option><option value="Bioquimico">Bioquímico</option><option value="Administrador">Administrador</option><option value="Paciente">Paciente</option></select></div>
<button class="btn btn-warning w-100">Crear</button>
</form>
</div></div>
</div>
<div class="col-lg-8">
<div class="card shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><span>Usuarios ({{ $usuarios->count() }})</span><span class="badge bg-primary">{{ $usuarios->where('activo',true)->count() }} activos</span></div><div class="table-responsive"><table class="table table-sm mb-0 align-middle"><thead class="table-light"><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Activo</th><th>Acción</th></tr></thead><tbody>@foreach($usuarios as $u)<tr><td>{{ $u->name }}</td><td><small>{{ $u->email }}</small></td><td><span class="badge bg-{{ $u->rol=='Administrador'?'danger':($u->rol=='Bioquimico'?'info':($u->rol=='Recepcionista'?'success':'secondary')) }}">{{ $u->rol }}</span></td><td>{!! $u->activo?'<span class="badge bg-success">Sí</span>':'<span class="badge bg-secondary">No</span>' !!}</td><td><form action="{{ route('admin.usuarios.toggle',$u->id) }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-{{ $u->activo?'outline-warning':'outline-success' }}">{{ $u->activo?'Desactivar':'Activar' }}</button></form></td></tr>@endforeach</tbody></table></div></div>
<div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between"><span>Auditoría reciente</span><a href="{{ route('admin.logs') }}" class="small">Ver todos</a></div><ul class="list-group list-group-flush">@forelse($logs as $l)<li class="list-group-item"><small>{{ $l->created_at->format('d/m/Y H:i') }} — <strong>{{ $l->user->name ?? 'Sistema' }}</strong>: {{ $l->accion }} @if($l->tabla_afectada)<code>[{{ $l->tabla_afectada }} #{{ $l->registro_id }}]</code> @endif <span class="text-muted">{{ $l->detalle }}</span></small></li>@empty<li class="list-group-item text-muted">Sin registros</li>@endforelse</ul></div>
</div>
</div>
@endsection
