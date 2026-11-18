@extends('layouts.app')
@section('title','Pacientes - DIALAB')
@section('top_title','Pacientes Registrados')
@section('top_subtitle','Listado con conteo de órdenes')

@section('content')
<div class="card shadow-sm">
<div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th>CI</th><th>Nombre</th><th>Edad/Sexo</th><th>Contacto</th><th>Órdenes</th><th>Registro</th></tr></thead><tbody>
@foreach($pacientes as $p)<tr><td><span class="badge bg-light text-dark border">{{ $p->ci }}</span></td><td class="fw-semibold">{{ $p->nombre_completo }}</td><td>{{ $p->edad }} / <span class="badge bg-secondary">{{ $p->sexo }}</span></td><td><small>{{ $p->email }}<br>{{ $p->telefono }}</small></td><td><span class="badge bg-primary">{{ $p->ordenes_count }}</span></td><td><small>{{ $p->fecha_registro }}</small></td></tr>@endforeach
</tbody></table></div>
<div class="card-footer">{{ $pacientes->links() }}</div>
</div>
@endsection
