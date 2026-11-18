@extends('layouts.app')
@section('title','Backups - DIALAB')
@section('top_title','Backups de Base de Datos')
@section('top_subtitle','Respaldo laboratorio_pii')

@section('content')
<div class="card shadow-sm mb-4 border-warning">
<div class="card-header bg-warning d-flex justify-content-between"><span><i class="bi bi-hdd-stack"></i> Generar nuevo backup</span><span class="badge bg-dark">laboratorio_pii</span></div>
<div class="card-body">
<p class="text-muted small">Se generará <code>.sql</code> vía <code>mysqldump</code> o <code>.json</code> fallback en <code>storage/app/backups</code>.</p>
<form action="{{ route('admin.backups.generar') }}" method="POST" onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerText='Generando...';">@csrf<button class="btn btn-warning btn-lg w-100 fw-semibold"><i class="bi bi-cloud-arrow-up"></i> Generar Backup Ahora</button></form>
</div>
</div>
<div class="card shadow-sm">
<div class="card-header bg-white d-flex justify-content-between"><span>Histórico ({{ $files->count() }})</span></div>
<div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th>Archivo</th><th>Tamaño</th><th>Fecha</th><th>Acción</th></tr></thead><tbody>
@forelse($files as $f)<tr><td><code>{{ $f['name'] }}</code></td><td>{{ $f['size'] }}</td><td>{{ $f['date'] }}</td><td><a href="{{ route('admin.backups.descargar', $f['name']) }}" class="btn btn-sm btn-success"><i class="bi bi-download"></i> Descargar</a></td></tr>
@empty<tr><td colspan="4" class="text-center py-4 text-muted">Aún no hay backups.</td></tr>@endforelse
</tbody></table></div>
</div>
@endsection
