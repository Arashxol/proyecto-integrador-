@extends('layouts.app')
@section('title','Resultados - DIALAB')
@section('top_title','Resultados')
@section('top_subtitle','Resultados de laboratorio')
@stack('head')

@section('content')

<div class="row mb-4">
<div class="col-md-3"><div class="card shadow-sm stat-card"><div class="card-body"><h5 class="stat-label">Total Resultados</h5><h2 class="stat-value">{{ $total }}</h2></div></div></div>
<div class="col-md-3"><div class="card shadow-sm stat-card"><div class="card-body"><h5 class="stat-label">Validados</h5><h2 class="stat-value">{{ $validados }}</h2></div></div></div>
<div class="col-md-3"><div class="card shadow-sm stat-card"><div class="card-body"><h5 class="stat-label">Pendientes</h5><h2 class="stat-value">{{ $pendientes }}</h2></div></div></div>
<div class="col-md-3"><div class="card shadow-sm stat-card"><div class="card-body"><h5 class="stat-label">Rechazados</h5><h2 class="stat-value">{{ $rechazados }}</h2></div></div></div>
</div>

<div class="card shadow-sm mb-4">
<div class="card-header d-flex justify-content-between align-items-center">
<span class="fw-semibold"><i class="bi bi-funnel"></i> Filtros y Búsqueda</span>
<div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
</div>
</div>
<div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4"><input name="buscar" value="{{ request('buscar') }}" placeholder="Paciente, CI, orden, análisis..." class="form-control"></div>
<div class="col-md-2">
<select name="estado" class="form-select">
<option value="">Todos los estados</option>
@foreach(['Validado','Pendiente','Rechazado'] as $e)
<option value="{{ $e }}" @selected(request('estado')==$e)>{{ $e }}</option>
@endforeach
</select>
</div>
<div class="col-md-2">
<select name="paciente_id" class="form-select">
<option value="">Todos los pacientes</option>
@foreach(($pacientes ?? collect()) as $p)
<option value="{{ $p->id }}" @selected(request('paciente_id')==$p->id)>{{ $p->nombre_completo }} ({{ $p->ci }})</option>
@endforeach
</select>
</div>
<div class="col-md-2"><input type="date" name="desde" value="{{ request('desde') }}" class="form-control"></div>
<div class="col-md-2"><input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control"></div>
<div class="col-12 d-flex gap-2">
<button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
<a href="{{ route('resultados.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
</div>
</form>
</div>
</div>

<div class="card shadow-sm">
<div class="card-header d-flex justify-content-between align-items-center">
<span class="fw-semibold">Resultados ({{ $resultados->total() ?? count($resultados ?? []) }})</span>
<div class="d-flex gap-2">
<a href="{{ route('resultados.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
<button onclick="window.print()" class="btn btn-sm btn-success"><i class="bi bi-printer"></i> Imprimir</button>
</div>
</div>
<div class="table-responsive">
<table class="table table-hover align-middle mb-0">
<thead class="table-light">
<tr><th style="width:5%">ID</th><th style="width:15%">Fecha</th><th style="width:20%">Paciente</th><th style="width:20%">Análisis</th><th style="width:15%">Resultado</th><th style="width:12%">Referencia</th><th style="width:10%">Estado</th><th style="width:8%" class="no-print">Acción</th></tr>
</thead>
<tbody>
@forelse(($resultados ?? collect()) as $r)
<tr>
<td>{{ $r->id }}</td>
<td>{{ $r->created_at->format('d/m/Y') }}</td>
<td><a href="{{ route('resultados.paciente', $r->muestra->orden->paciente_id) }}" class="text-decoration-none fw-semibold">{{ $r->muestra->orden->paciente->nombre_completo }}</a><br><small class="text-muted">CI: {{ $r->muestra->orden->paciente->ci }}</small></td>
<td>{{ $r->muestra->tipoAnalisis->nombre }}</td>
<td>{{ Str::limit($r->resultado, 50) }}</td>
<td><small>{{ $r->valores_referencia }}</small></td>
<td><span class="badge bg-{{ $r->estado==='Validado'?'success':($r->estado==='Pendiente'?'warning':'danger') }}">{{ $r->estado }}</span></td>
<td class="no-print">
<div class="d-flex gap-1">
<a href="{{ route('resultados.paciente', $r->muestra->orden->paciente_id) }}" class="btn btn-sm btn-outline-primary" title="Ver resultados del paciente"><i class="bi bi-person"></i></a>
<a href="{{ route('resultados.exportar', $r->muestra->orden->paciente_id) }}" class="btn btn-sm btn-outline-success" title="Exportar PDF"><i class="bi bi-file-earmark-pdf"></i></a>
</div>
</td>
</tr>
@empty
<tr><td colspan="8" class="text-center py-4 text-muted">Sin resultados con los filtros aplicados.</td></tr>
@endforelse
</tbody>
</table>
</div>
@if(method_exists($resultados, 'links'))<div class="card-footer no-print">{{ $resultados->links() }}</div>@endif
</div>
@endsection

@push('head')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
.stat-card{ border:0; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.stat-card .card-body{ padding:16px; }
.stat-value{ font-size:2rem; font-weight:700; margin:0; }
.stat-label{ font-size:.85rem; text-transform:uppercase; letter-spacing:.5px; opacity:.7; }
</style>
@endpush
