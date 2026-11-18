@extends('layouts.app')
@section('title','Reportes - DIALAB')
@section('top_title','Reportes Gerenciales')
@section('top_subtitle','Filtrado, operativo y exportación CSV')

@section('content')
@if(auth()->user()->rol==='Paciente')
<div class="alert alert-danger">No tienes permiso para ver reportes gerenciales.</div>
@else
<div class="card shadow-sm mb-4">
<div class="card-header bg-white d-flex justify-content-between align-items-center"><span><i class="bi bi-funnel"></i> Filtros</span><span class="badge bg-dark">{{ $totalOrdenes ?? 0 }} órdenes</span></div>
<div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-3"><label class="form-label">Desde</label><input type="date" name="desde" value="{{ $desde ?? request('desde') }}" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Hasta</label><input type="date" name="hasta" value="{{ $hasta ?? request('hasta') }}" class="form-control"></div>
<div class="col-md-3"><label class="form-label">Estado</label><select name="estado" class="form-select"><option value="">Todos</option>@foreach(['Pendiente','En Proceso','Completada','Cancelada'] as $e)<option value="{{ $e }}" @selected(( $estado ?? request('estado'))==$e)>{{ $e }}</option>@endforeach</select></div>
<div class="col-md-3 d-flex align-items-end gap-1"><button class="btn btn-primary w-50">Filtrar</button><a href="{{ route('laboratorio.reportes') }}" class="btn btn-outline-secondary w-25">Limpiar</a><button type="submit" name="export" value="csv" class="btn btn-success w-25">⬇ CSV</button></div>
</form>
</div>
</div>

<div class="row g-3 mb-4">
<div class="col-md-3"><div class="card bg-success text-white shadow-sm"><div class="card-body text-center py-3"><h6>Ingresos Confirmados</h6><h3 class="fw-bold">{{ number_format($totalIngresos, 2) }} Bs</h3></div></div></div>
<div class="col-md-3"><div class="card bg-primary text-white shadow-sm"><div class="card-body text-center py-3"><h6>Total Pacientes</h6><h3 class="fw-bold">{{ $pacientesAtendidos }}</h3></div></div></div>
<div class="col-md-3"><div class="card bg-info text-white shadow-sm"><div class="card-body text-center py-3"><h6>Muestras Analizadas</h6><h3 class="fw-bold">{{ $analisisRealizados }}</h3></div></div></div>
<div class="col-md-3"><div class="card bg-dark text-white shadow-sm"><div class="card-body text-center py-3"><h6>Total Órdenes</h6><h3 class="fw-bold">{{ $totalOrdenes ?? 0 }}</h3></div></div></div>
</div>

<div class="row g-4">
<div class="col-lg-6">
<div class="card shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><span>Top Exámenes</span><button onclick="window.print()" class="btn btn-sm btn-light">🖨 Imprimir</button></div><ul class="list-group list-group-flush">@forelse($analisisPopulares as $a)<li class="list-group-item d-flex justify-content-between"><span>{{ $a->nombre }} <small class="text-muted">({{ $a->categoria }})</small></span><span class="badge bg-primary">{{ $a->cantidad }}</span></li>@empty<li class="list-group-item text-muted">Sin datos</li>@endforelse</ul></div>
@if(isset($ingresosPorMes) && $ingresosPorMes->count())<div class="card shadow-sm"><div class="card-header bg-white">Ingresos por mes</div><ul class="list-group list-group-flush">@foreach($ingresosPorMes as $r)<li class="list-group-item d-flex justify-content-between"><span>{{ $r->mes }}</span><span class="fw-bold">Bs. {{ number_format($r->ingresos,2) }}</span></li>@endforeach</ul></div>@endif
</div>
<div class="col-lg-6">
<div class="card shadow-sm mb-3"><div class="card-header bg-white">Órdenes por estado</div><ul class="list-group list-group-flush">@forelse($ordenesPorEstado ?? [] as $k=>$v)<li class="list-group-item d-flex justify-content-between"><span>{{ $k }}</span><span class="badge bg-dark">{{ $v }}</span></li>@empty<li class="list-group-item text-muted">Sin datos</li>@endforelse</ul></div>
<div class="card shadow-sm"><div class="card-header bg-white">Muestras por estado</div><ul class="list-group list-group-flush">@forelse($muestrasPorEstado ?? [] as $k=>$v)<li class="list-group-item d-flex justify-content-between"><span>{{ $k }}</span><span class="badge bg-info">{{ $v }}</span></li>@empty<li class="list-group-item text-muted">Sin datos</li>@endforelse</ul></div>
<div class="mt-3 d-flex gap-2"><a href="{{ request()->fullUrlWithQuery(['export'=>'csv']) }}" class="btn btn-success w-50">⬇ Exportar CSV</a><button onclick="window.print()" class="btn btn-outline-secondary w-50">🖨 Imprimir</button></div>
@if(auth()->user()->rol==='Administrador')<div class="mt-2"><a href="{{ route('admin.logs') }}" class="btn btn-outline-dark w-100">📋 Ver auditoría</a></div>@endif
</div>
</div>
@endif
@endsection
