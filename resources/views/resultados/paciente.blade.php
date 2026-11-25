@extends('layouts.app')
@section('title','Resultados de {{ $paciente->nombre_completo }} - DIALAB')
@section('top_title','Resultados - {{ $paciente->nombre_completo }}')
@section('top_subtitle','Historial de laboratorio')

@section('content')

<!-- Membrete profesional tipo factura -->
<div class="membrete card shadow-sm">
<div class="row">
<div class="col-md-6">
<h3 class="mb-0 text-success">DIALAB - Laboratorio de Análisis Clínicos</h3>
<p class="mb-0 small">Universidad Franz Tamayo - Centro de Diagnóstico</p>
<p class="mb-0 text-muted">Tel: 2-2441234 | La Paz, Bolivia</p>
</div>
<div class="col-md-6 text-end">
<p class="mb-0"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</p>
<p class="mb-0"><strong>Paciente:</strong> {{ $paciente->nombre_completo }}</p>
<p class="mb-0"><strong>CI:</strong> {{ $paciente->ci }}</p>
</div>
</div>
</div>

<div class="row mb-3">
<div class="col-md-6"><div class="card shadow-sm"><div class="card-body"><strong>Edad:</strong> {{ $paciente->edad }} años | <strong>Sexo:</strong> {{ $paciente->sexo }}</div></div></div>
<div class="col-md-6"><div class="card shadow-sm"><div class="card-body"><strong>Email:</strong> {{ $paciente->email ?? '—' }} | <strong>Tel:</strong> {{ $paciente->telefono ?? '—' }}</div></div></div>
</div>

<h5 class="mb-3"><i class="bi bi-clipboard2-pulse"></i> Resultados de Laboratorio</h5>

@forelse(($grupos ?? []) as $grupoKey => $grupo)
@php
[$fechaGrupo, $horaGrupo] = explode('|', $grupoKey);
$totalGrupo = $grupo->count();
@endphp

<div class="card mb-4 shadow-sm">
<div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
<span><strong>Reporte {{ $loop->iteration }}</strong> — {{ $totalGrupo }} análisis</span>
<span>
@if($fechaGrupo !== 'sin-fecha')
{{ \Carbon\Carbon::parse($fechaGrupo)->format('d/m/Y') }}
@if($horaGrupo && $horaGrupo !== 'sin-hora') a las {{ $horaGrupo }} @endif
@else
Sin fecha
@endif
</span>
</div>
<table class="table table-bordered mb-0 align-middle">
<thead class="table-light">
<tr><th style="width:5%">#</th><th style="width:25%">Análisis</th><th style="width:15%">Categoría</th><th style="width:15%">Muestra</th><th style="width:15%">Resultado</th><th style="width:15%">Referencia</th><th style="width:10%">Estado</th><th style="width:10%">Validado por</th></tr>
</thead>
<tbody>
@foreach($grupo as $r)
<tr>
<td>{{ $loop->iteration }}</td>
<td><strong>{{ $r->muestra->tipoAnalisis->nombre }}</strong></td>
<td><span class="badge bg-secondary">{{ $r->muestra->tipoAnalisis->categoria }}</span></td>
<td>{{ $r->muestra->codigo_muestra }}<br><small class="text-muted">{{ $r->muestra->tipo_muestra }}</small></td>
<td><strong class="text-success">{{ $r->resultado }}</strong></td>
<td><small>{{ $r->valores_referencia }}</small></td>
<td><span class="badge bg-{{ $r->estado==='Validado'?'success':($r->estado==='Pendiente'?'warning':'danger') }}">{{ $r->estado }}</span></td>
<td>
@if($r->bioquimico)
{{ $r->bioquimico->name }}<br>
<small class="text-muted">{{ $r->fecha_validacion?->format('d/m/Y H:i') }}</small>
@else
<small class="text-muted">Pendiente</small>
@endif
</td>
</tr>
@endforeach
</tbody>
</table>
</div>
@empty
<div class="alert alert-warning text-center py-4">No hay resultados registrados para este paciente.</div>
@endforelse

@if(($resultados ?? collect())->count() > 0)
<div class="card shadow-sm">
<div class="card-body text-center py-4">
<p class="mb-2"><strong>___________________________</strong></p>
<p class="mb-0 small">Firma y Sello del Bioquímico Validador</p>
<p class="mt-3 text-muted small">Documento generado por DIALAB LIS el {{ now()->format('d/m/Y H:i') }}</p>
</div>
</div>
@endif

@endsection

@push('head')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
.membrete { background: #fff; border: none; }
.table thead th { background: #0f172a; color: #fff; font-size: 0.75rem; text-transform: uppercase; letter-spacing: .5px; padding: 10px; border: none; }
.table tbody td { border-bottom: 1px solid #f1f5f9; padding: 10px; vertical-align: middle; }
.table tbody tr:hover { background: #f8fafc; }
@media print { .no-print { display: none !important; } body { background: #fff; } .card { border: none; box-shadow: none; } }
</style>
@endpush
