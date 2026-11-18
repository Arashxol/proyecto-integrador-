<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resultados de {{ $paciente->nombre_completo }} - DIALAB</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; background: #f1f3f6; }
.membrete { border-bottom: 3px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; background: #fff; }
.card { border: 1px solid #e2e8f0; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.table thead th { background: #0f172a; color: #fff; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 10px; border: none; }
.table tbody td { border-bottom: 1px solid #f1f5f9; padding: 10px; vertical-align: middle; }
.table tbody tr:hover { background: #f8fafc; }
.no-print { display: block; }
@media print {
  .no-print { display: none !important; }
  body { background: #fff; }
  .card { border: none; box-shadow: none; }
}
</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark no-print">
<div class="container-fluid">
<span class="navbar-brand">📋 Resultados - {{ $paciente->nombre_completo }}</span>
<div class="d-flex gap-2 align-items-center">
<a href="{{ route('resultados.index') }}" class="btn btn-sm btn-outline-light">← Volver</a>
<a href="{{ route('resultados.exportar', $paciente->id) }}" class="btn btn-sm btn-success btn-export"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
<form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Salir</button></form>
</div>
</div>
</nav>

<div class="container mt-4">

<!-- Membrete profesional tipo factura -->
<div class="membrete">
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

<div class="row mb-4">
<div class="col-md-6"><div class="card"><div class="card-body"><strong>Edad:</strong> {{ $paciente->edad }} años | <strong>Sexo:</strong> {{ $paciente->sexo }}</div></div></div>
<div class="col-md-6"><div class="card"><div class="card-body"><strong>Email:</strong> {{ $paciente->email ?? '—' }} | <strong>Tel:</strong> {{ $paciente->telefono ?? '—' }}</div></div></div>
</div>

<h5 class="mb-3"><i class="bi bi-clipboard2-pulse"></i> Resultados de Laboratorio</h5>

@forelse($grupos as $grupoKey => $grupo)
@php
[$fechaGrupo, $horaGrupo] = explode('|', $grupoKey);
$totalGrupo = $grupo->count();
@endphp

<div class="card mb-4 shadow-sm {{ !$loop->first ? '' : '' }}">
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

@if($resultados->count() > 0)
<div class="card shadow-sm">
<div class="card-body text-center py-4">
<p class="mb-2"><strong>___________________________</strong></p>
<p class="mb-0 small">Firma y Sello del Bioquímico Validador</p>
<p class="mt-3 text-muted small">Documento generado por DIALAB LIS el {{ now()->format('d/m/Y H:i') }}</p>
</div>
</div>
@endif

</div>
</body>
</html>