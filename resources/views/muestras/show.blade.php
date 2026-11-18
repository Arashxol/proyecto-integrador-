@extends('layouts.app')
@section('title','Muestra '.$muestra->codigo_muestra)
@section('top_title','Muestra '.$muestra->codigo_muestra)
@section('top_subtitle','Trazabilidad y estado')

@section('content')
<div class="row g-4">
<div class="col-lg-8">
<div class="card shadow-sm mb-3"><div class="card-header bg-white"><i class="bi bi-diagram-3"></i> Trazabilidad</div><div class="card-body">
<p><strong>Orden:</strong> {{ $muestra->orden->codigo_orden }} — {{ $muestra->orden->paciente->nombre_completo }} (CI: {{ $muestra->orden->paciente->ci }})</p>
<p><strong>Análisis:</strong> {{ $muestra->tipoAnalisis->nombre }} ({{ $muestra->tipoAnalisis->categoria }}) • Bs. {{ $muestra->tipoAnalisis->precio }}</p>
<p><strong>Estado:</strong> <span class="badge bg-primary">{{ $muestra->estado }}</span> • <strong>Tipo:</strong> {{ $muestra->tipo_muestra }}</p>
<p><strong>Recolección:</strong> {{ $muestra->fecha_recoleccion }} @if($muestra->fecha_analisis) • <strong>Análisis:</strong> {{ $muestra->fecha_analisis }} @endif<br><strong>Técnico:</strong> {{ $muestra->tecnico->name ?? 'Sin asignar' }}</p>
<hr><h6>Línea de tiempo</h6>
<ul class="list-group"><li class="list-group-item {{ $muestra->estado=='Recolectado'?'active':'' }}">1. Recolectado — {{ $muestra->fecha_recoleccion }}</li><li class="list-group-item {{ $muestra->estado=='En Transito'?'active':'' }}">2. En Tránsito</li><li class="list-group-item {{ $muestra->estado=='En Analisis'?'active':'' }}">3. En Análisis @if($muestra->tecnico) por {{ $muestra->tecnico->name }} @endif</li><li class="list-group-item {{ $muestra->estado=='Resultado Listo'?'active':'' }}">4. Resultado Listo @if($muestra->resultado?->estado==='Validado') — Validado por {{ $muestra->resultado->bioquimico->name ?? '' }} @endif</li><li class="list-group-item {{ $muestra->estado=='Archivada'?'active':'' }}">5. Archivada</li></ul>
</div></div>
@if($muestra->resultado)<div class="card shadow-sm"><div class="card-header bg-white">Resultado</div><div class="card-body"><p><strong>Valor:</strong> {{ $muestra->resultado->resultado }}</p><p><strong>Ref:</strong> {{ $muestra->resultado->valores_referencia }}</p><p><strong>Interpretación:</strong> {{ $muestra->resultado->interpretacion ?? '-' }}</p><p><span class="badge bg-{{ $muestra->resultado->estado=='Validado'?'success':'warning' }}">{{ $muestra->resultado->estado }}</span></p></div></div>@endif
</div>
<div class="col-lg-4">
<div class="card shadow-sm"><div class="card-header bg-white"><i class="bi bi-arrow-repeat"></i> Cambiar Estado</div><div class="card-body">
<form action="{{ route('muestras.estado',$muestra->id) }}" method="POST">@csrf<select name="estado" class="form-select mb-2" required>@foreach(['Recolectado','En Transito','En Analisis','Resultado Listo','Archivada'] as $e)<option value="{{ $e }}" @selected($muestra->estado==$e)>{{ $e }}</option>@endforeach</select><button class="btn btn-primary w-100">Actualizar</button></form>
<small class="text-muted d-block mt-2">Solo Bioquímico/Admin. Auditado.</small>
<a href="{{ route('muestras.index') }}" class="btn btn-outline-secondary w-100 mt-2">← Volver</a>
</div></div>
</div>
</div>
@endsection
