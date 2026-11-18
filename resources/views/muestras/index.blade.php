@extends('layouts.app')
@section('title','Muestras - DIALAB')
@section('top_title','Gestión de Muestras')
@section('top_subtitle','Trazabilidad completa')

@section('content')
<div class="card shadow-sm mb-3"><div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-5"><input name="buscar" value="{{ request('buscar') }}" placeholder="Código, orden, CI o nombre" class="form-control"></div>
<div class="col-md-3"><select name="estado" class="form-select"><option value="">Todos los estados</option>@foreach($estados as $e)<option value="{{ $e }}" @selected(request('estado')==$e)>{{ $e }}</option>@endforeach</select></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
<div class="col-md-2"><a href="{{ route('muestras.index') }}" class="btn btn-outline-secondary w-100">Limpiar</a></div>
</form>
</div></div>
<div class="card shadow-sm"><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th>Código</th><th>Orden / Paciente</th><th>Análisis</th><th>Tipo</th><th>Estado</th><th>Recolección</th><th></th></tr></thead><tbody>
@forelse($muestras as $m)<tr><td class="fw-semibold">{{ $m->codigo_muestra }}</td><td>{{ $m->orden->codigo_orden }}<br><small class="text-muted">{{ $m->orden->paciente->nombre_completo }} ({{ $m->orden->paciente->ci }})</small></td><td>{{ $m->tipoAnalisis->nombre }}<br><small class="text-muted">{{ $m->tipoAnalisis->categoria }}</small></td><td><span class="badge bg-secondary">{{ $m->tipo_muestra }}</span></td><td><span class="badge bg-{{ $m->estado=='Recolectado'?'warning':($m->estado=='En Analisis'?'info':($m->estado=='Resultado Listo'?'success':'secondary')) }}">{{ $m->estado }}</span></td><td><small>{{ \Carbon\Carbon::parse($m->fecha_recoleccion)->format('d/m/Y H:i') }}</small><br>@if($m->tecnico)<small class="text-muted">{{ $m->tecnico->name }}</small>@endif</td><td><a href="{{ route('muestras.show',$m->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Ver</a></td></tr>
@empty<tr><td colspan="7" class="text-center py-4 text-muted">Sin muestras</td></tr>@endforelse
</tbody></table></div><div class="card-footer">{{ $muestras->links() }}</div></div>
@endsection
