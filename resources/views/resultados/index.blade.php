<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resultados - DIALAB</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
body { background: #f1f3f6; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.stat-card .card-body { padding: 16px; }
.stat-value { font-size: 2rem; font-weight: 700; margin: 0; }
.stat-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.7; }
.table-container { border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
.table thead th { background: #0f172a; color: #fff; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; padding: 12px; border: none; }
.table tbody td { border-bottom: 1px solid #f1f5f9; padding: 12px; vertical-align: middle; }
.table tbody tr:hover { background: #f8fafc; }
.btn-export { background: linear-gradient(135deg, #2563eb, #7c3aed); border: none; }
.btn-export:hover { background: linear-gradient(135deg, #1d4ed8, #6d28d9); }
@media print {
  .no-print { display: none !important; }
  body { background: #fff; }
}
</style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark no-print">
<div class="container-fluid">
<span class="navbar-brand">📊 Resultados - DIALAB</span>
<div class="d-flex gap-2 align-items-center">
<a href="{{ route('dashboard') }}" class="btn btn-sm btn-outline-light">Dashboard</a>
<form action="{{ route('logout') }}" method="POST" class="d-inline">@csrf<button class="btn btn-sm btn-outline-danger">Salir</button></form>
</div>
</div>
</nav>

<div class="container mt-4">

<!-- Estadísticas -->
<div class="row mb-4">
<div class="col-md-3"><div class="card stat-card bg-success text-white"><div class="card-body"><h5 class="stat-label">Total Resultados</h5><h2 class="stat-value">{{ $total }}</h2></div></div></div>
<div class="col-md-3"><div class="card stat-card bg-info text-white"><div class="card-body"><h5 class="stat-label">Validados</h5><h2 class="stat-value">{{ $validados }}</h2></div></div></div>
<div class="col-md-3"><div class="card stat-card bg-warning text-dark"><div class="card-body"><h5 class="stat-label">Pendientes</h5><h2 class="stat-value">{{ $pendientes }}</h2></div></div></div>
<div class="col-md-3"><div class="card stat-card bg-danger text-white"><div class="card-body"><h5 class="stat-label">Rechazados</h5><h2 class="stat-value">{{ $rechazados }}</h2></div></div></div>
</div>

<!-- Filtros -->
<div class="card shadow-sm mb-4 no-print">
<div class="card-header bg-white d-flex justify-content-between align-items-center">
<span class="fw-semibold"><i class="bi bi-funnel"></i> Filtros y Búsqueda</span>
<div class="btn-group btn-group-sm">
<button class="btn btn-outline-primary" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
</div>
</div>
<div class="card-body">
<form method="GET" class="row g-2">
<div class="col-md-4">
<input name="buscar" value="{{ request('buscar') }}" placeholder="Paciente, CI, orden, análisis..." class="form-control">
</div>
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
@foreach($pacientes as $p)
<option value="{{ $p->id }}" @selected(request('paciente_id')==$p->id)>{{ $p->nombre_completo }} ({{ $p->ci }})</option>
@endforeach
</select>
</div>
<div class="col-md-2">
<input type="date" name="desde" value="{{ request('desde') }}" class="form-control" placeholder="Desde">
</div>
<div class="col-md-2">
<input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control" placeholder="Hasta">
</div>
<div class="col-12 d-flex gap-2">
<button class="btn btn-primary"><i class="bi bi-search"></i> Buscar</button>
<a href="{{ route('resultados.index') }}" class="btn btn-outline-secondary">Limpiar filtros</a>
</div>
</form>
</div>
</div>

<!-- Tabla de resultados -->
<div class="card shadow-sm">
<div class="card-header bg-white d-flex justify-content-between align-items-center">
<span class="fw-semibold">Resultados ({{ $resultados->total() }})</span>
<div class="d-flex gap-2">
<a href="{{ route('resultados.index') }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-file-earmark-pdf"></i> Exportar PDF</a>
<button onclick="window.print()" class="btn btn-sm btn-success no-print"><i class="bi bi-printer"></i> Imprimir</a>
</div>
</div>
<div class="table-responsive">
<table class="table table-hover mb-0 align-middle">
<thead class="table-light">
<tr><th style="width:5%">ID</th><th style="width:15%">Fecha</th><th style="width:20%">Paciente</th><th style="width:20%">Análisis</th><th style="width:15%">Resultado</th><th style="width:12%">Referencia</th><th style="width:10%">Estado</th><th style="width:8%" class="no-print">Acción</th></tr>
</thead>
<tbody>
@forelse($resultados as $r)
<tr>
<td>{{ $r->id }}</td>
<td>{{ $r->created_at->format('d/m/Y') }}</td>
<td>
<a href="{{ route('resultados.paciente', $r->muestra->orden->paciente_id) }}" class="text-decoration-none fw-semibold">{{ $r->muestra->orden->paciente->nombre_completo }}</a><br><small class="text-muted">CI: {{ $r->muestra->orden->paciente->ci }}</small>
</td>
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
<div class="card-footer no-print">
{{ $resultados->links() }}
</div>
</div>
</div>
</body>
</html>