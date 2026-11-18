@extends('layouts.app')
@section('title','Mi Historial - DIALAB')
@section('top_title','Mi Historial Clínico')
@section('top_subtitle','Paciente • Consulta de análisis y citas')

@section('content')
@if(!$pacienteData)
<div class="alert alert-warning text-center shadow-sm">
<h5>Aún no hay registros clínicos asociados a este correo ({{ auth()->user()->email }})</h5>
<p class="mb-2">Si acudiste a la sucursal, asegúrate de haber proporcionado este mismo correo a recepción.</p>
<a href="{{ route('paciente.citas.index') }}" class="btn btn-success"><i class="bi bi-calendar-plus"></i> Agendar cita</a>
</div>
@else
<div class="card shadow-sm border-success mb-4">
<div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
<div>
<h5 class="text-success mb-1"><i class="bi bi-person-badge"></i> {{ $pacienteData->nombre_completo }}</h5>
<p class="mb-0 small"><strong>CI:</strong> {{ $pacienteData->ci }} | <strong>Edad:</strong> {{ $pacienteData->edad }} años | <strong>Email:</strong> {{ $pacienteData->email }}</p>
</div>
<a href="{{ route('paciente.citas.index') }}" class="btn btn-success btn-sm"><i class="bi bi-calendar-event"></i> Agendar / Ver Citas</a>
</div>
</div>

<div class="row g-3 mb-4">
<div class="col-6 col-md-3"><div class="card text-center shadow-sm"><div class="card-body"><div class="text-muted small">Total análisis</div><h4 class="mb-0">{{ $statsPaciente['total'] }}</h4></div></div></div>
<div class="col-6 col-md-3"><div class="card text-center shadow-sm border-start border-4 border-warning"><div class="card-body"><div class="text-muted small">Tomadas</div><h4 class="mb-0">{{ $statsPaciente['tomadas'] }}</h4></div></div></div>
<div class="col-6 col-md-3"><div class="card text-center shadow-sm border-start border-4 border-info"><div class="card-body"><div class="text-muted small">En proceso</div><h4 class="mb-0">{{ $statsPaciente['enProceso'] }}</h4></div></div></div>
<div class="col-6 col-md-3"><div class="card text-center shadow-sm border-start border-4 border-success"><div class="card-body"><div class="text-muted small">Resultados listos</div><h4 class="mb-0">{{ $statsPaciente['validadas'] }}</h4></div></div></div>
</div>

<ul class="nav nav-pills mb-3" role="tablist">
<li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tomadas" type="button">🩸 Tomadas</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#proceso" type="button">⏳ En proceso</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#resultados" type="button">✅ Resultados</button></li>
<li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#citas" type="button">📅 Citas</button></li>
</ul>

<div class="tab-content">
@php $todas = $ordenes->flatMap->muestras; @endphp

<div class="tab-pane fade show active" id="tomadas">
@php $tomadas = $todas->where('estado','Recolectado'); @endphp
@if($tomadas->isEmpty())<p class="text-muted text-center py-4">No tienes muestras en estado Tomada.</p>
@else @foreach($tomadas->groupBy('orden.codigo_orden') as $codigo => $muestras)
<div class="card shadow-sm mb-2"><div class="card-header bg-light d-flex justify-content-between"><strong>{{ $codigo }}</strong><small class="text-muted">{{ $muestras->first()->orden->fecha_solicitud }}</small></div>
<ul class="list-group list-group-flush">@foreach($muestras as $muestra)<li class="list-group-item d-flex justify-content-between align-items-center"><div><h6 class="mb-0">{{ $muestra->tipoAnalisis->nombre }}</h6><small class="text-muted">{{ $muestra->tipo_muestra }} • {{ $muestra->codigo_muestra }} • {{ \Carbon\Carbon::parse($muestra->fecha_recoleccion)->format('d/m/Y H:i') }}</small></div><span class="badge bg-warning text-dark">Tomada</span></li>@endforeach</ul></div>
@endforeach @endif
</div>

<div class="tab-pane fade" id="proceso">
@php $enProceso = $todas->whereIn('estado',['En Transito','En Analisis']); @endphp
@if($enProceso->isEmpty())<p class="text-muted text-center py-4">No tienes análisis en proceso.</p>
@else @foreach($enProceso->groupBy('orden.codigo_orden') as $codigo => $muestras)
<div class="card shadow-sm mb-2"><div class="card-header bg-info text-white d-flex justify-content-between"><strong>{{ $codigo }}</strong><small>{{ $muestras->first()->orden->estado }}</small></div>
<ul class="list-group list-group-flush">@foreach($muestras as $muestra)<li class="list-group-item d-flex justify-content-between"><div><h6 class="mb-0">{{ $muestra->tipoAnalisis->nombre }}</h6><small class="text-muted">{{ $muestra->codigo_muestra }} • desde {{ \Carbon\Carbon::parse($muestra->fecha_recoleccion)->format('d/m/Y') }}</small></div><span class="badge bg-info">{{ $muestra->estado }}</span></li>@endforeach</ul></div>
@endforeach @endif
</div>

<div class="tab-pane fade" id="resultados">
@php $validadas = $todas->filter(fn($m)=> $m->resultado && $m->resultado->estado==='Validado'); @endphp
@if($validadas->isEmpty())<p class="text-muted text-center py-4">Aún no tienes resultados validados.</p>
@else
<div class="small text-muted mb-2">Resultados validados por el laboratorio — visibles sin PDF</div>
@foreach($validadas->groupBy('orden.codigo_orden') as $codigo => $muestras)
<div class="card shadow-sm mb-3 border-success"><div class="card-header bg-light d-flex justify-content-between"><strong>Orden {{ $codigo }}</strong><small>{{ $muestras->first()->orden->fecha_solicitud }}</small></div>
<ul class="list-group list-group-flush">@foreach($muestras as $muestra)<li class="list-group-item"><h6 class="mb-1">{{ $muestra->tipoAnalisis->nombre }} <span class="badge bg-success ms-2">Validado</span></h6><div class="row"><div class="col-md-4"><small class="text-muted d-block">Muestra</small><strong>{{ $muestra->codigo_muestra }} ({{ $muestra->tipo_muestra }})</strong></div><div class="col-md-4"><small class="text-muted d-block">Resultado</small><strong class="text-success">{{ $muestra->resultado->resultado }}</strong></div><div class="col-md-4"><small class="text-muted d-block">Referencia</small>{{ $muestra->resultado->valores_referencia }}</div></div>@if($muestra->resultado->interpretacion)<div class="mt-2"><small class="text-muted">Interpretación:</small> {{ $muestra->resultado->interpretacion }}</div>@endif<small class="text-muted">Validado: {{ \Carbon\Carbon::parse($muestra->resultado->fecha_validacion)->format('d/m/Y H:i') }}</small></li>@endforeach</ul></div>
@endforeach @endif
</div>

<div class="tab-pane fade" id="citas">
<div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between"><span>Mis próximas citas</span><a href="{{ route('paciente.citas.index') }}" class="btn btn-sm btn-success">Gestionar</a></div>
@if($citas->isEmpty())<div class="card-body text-center text-muted">No tienes citas. <a href="{{ route('paciente.citas.index') }}">Agenda una</a></div>
@else<ul class="list-group list-group-flush">@foreach($citas as $c)<li class="list-group-item d-flex justify-content-between"><div><strong>{{ $c->tipo }}</strong> — {{ \Carbon\Carbon::parse($c->fecha)->format('d/m/Y') }} {{ \Carbon\Carbon::parse($c->hora)->format('H:i') }}<br><small class="text-muted">{{ $c->motivo ?? 'Sin motivo' }}</small></div><span class="badge bg-{{ $c->estado==='Confirmada'?'success':($c->estado==='Pendiente'?'warning':'secondary') }}">{{ $c->estado }}</span></li>@endforeach</ul>@endif</div>
</div>
</div>

<div class="mt-4">
<h6 class="fw-semibold"><i class="bi bi-clock-history"></i> Mis Órdenes</h6>
@forelse($ordenes as $orden)
<div class="card shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><strong>{{ $orden->codigo_orden }}</strong><span><span class="badge bg-{{ $orden->estado==='Completada'?'success':($orden->estado==='Pendiente'?'warning':'info') }}">{{ $orden->estado }}</span><small class="text-muted ms-2">{{ \Carbon\Carbon::parse($orden->fecha_solicitud)->format('d/m/Y') }} • Bs. {{ $orden->total }}</small></span></div>
<ul class="list-group list-group-flush">@foreach($orden->muestras as $muestra)<li class="list-group-item d-flex justify-content-between"><div><h6 class="mb-0">{{ $muestra->tipoAnalisis->nombre }} <small class="text-muted">({{ $muestra->tipoAnalisis->categoria }})</small></h6><small class="text-muted">{{ $muestra->tipo_muestra }} • {{ $muestra->codigo_muestra }} • {{ $muestra->estado }}</small>@if($muestra->resultado?->estado==='Validado')<br><small>Resultado: <strong class="text-success">{{ $muestra->resultado->resultado }}</strong> | Ref: {{ $muestra->resultado->valores_referencia }}</small>@endif</div><span class="badge bg-light text-dark border">{{ $muestra->estado }}</span></li>@endforeach</ul></div>
@empty<p class="text-muted text-center">No tienes órdenes.</p>@endforelse
</div>
@endif
@endsection
