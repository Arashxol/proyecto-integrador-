@extends('layouts.app')
@section('title','Recepción - DIALAB')
@section('top_title','Panel Recepción')
@section('top_subtitle','Admisión y gestión de pacientes')

@section('content')
<div class="row g-3 mb-4">
  <div class="col-md-4"><div class="card shadow-sm text-white bg-primary"><div class="card-body"><div class="d-flex justify-content-between align-items-center"><div><div class="small opacity-75">Pacientes Registrados</div><h2 class="mb-0">{{ $pacientes }}</h2></div><i class="bi bi-people fs-1 opacity-50"></i></div></div></div></div>
  <div class="col-md-4"><div class="card shadow-sm text-white bg-warning"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="small">Órdenes Pendientes</div><h2 class="mb-0">{{ $ordenesPendientes }}</h2></div><i class="bi bi-clipboard fs-1 opacity-50"></i></div></div></div></div>
  <div class="col-md-4"><div class="card shadow-sm text-white bg-info"><div class="card-body"><div class="d-flex justify-content-between"><div><div class="small">Muestras en Análisis</div><h2 class="mb-0">{{ $muestrasEnProceso }}</h2></div><i class="bi bi-droplet fs-1 opacity-50"></i></div></div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-5">
    <div class="card shadow-sm">
      <div class="card-header bg-white"><span class="fw-semibold"><i class="bi bi-person-plus"></i> Admisión — Nueva Orden</span></div>
      <div class="card-body">
        <form action="{{ route('laboratorio.registrar') }}" method="POST">
          @csrf
          <div class="mb-2"><label class="form-label">Nombre Completo *</label><input type="text" name="nombre_completo" class="form-control" required></div>
          <div class="row">
            <div class="col-7 mb-2"><label class="form-label">CI *</label><input type="text" name="ci" id="ciInput" class="form-control" required onblur="checkCi(this.value)"><div id="ciFeedback" class="form-text"></div></div>
            <div class="col-2 mb-2"><label class="form-label">Edad *</label><input type="number" name="edad" class="form-control" required></div>
            <div class="col-3 mb-2"><label class="form-label">Sexo *</label><select name="sexo" class="form-select" required><option value="Masculino">M</option><option value="Femenino">F</option></select></div>
          </div>
          <div class="mb-2"><label class="form-label">Correo (Portal Paciente)</label><input type="email" name="email" class="form-control" placeholder="correo@paciente.com" required><div class="form-text">Debe coincidir con el registro del paciente.</div></div>
          <div class="mb-2"><label class="form-label">Médico Solicitante</label><input type="text" name="medico_solicitante" class="form-control" placeholder="Ej: Dr. Alvarez"></div>
          <div class="mb-3"><label class="form-label">Análisis Requeridos</label><div class="border rounded p-2 bg-light" style="max-height:160px;overflow-y:auto;">@foreach($examenesDisponibles as $examen)<div class="form-check"><input class="form-check-input" type="checkbox" name="examenes[]" value="{{ $examen->id }}" id="exam_{{ $examen->id }}"><label class="form-check-label" for="exam_{{ $examen->id }}">{{ $examen->nombre }} <small class="text-success">Bs.{{ $examen->precio }}</small></label></div>@endforeach</div></div>
          <button type="submit" class="btn btn-success w-100"><i class="bi bi-check2-square"></i> Generar Orden y Muestras</button>
        </form>
      </div>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card shadow-sm border-primary">
      <div class="card-header bg-primary text-white"><i class="bi bi-search"></i> Consulta de Historial</div>
      <div class="card-body">
        <form action="{{ route('dashboard') }}" method="GET" class="d-flex gap-2">
          <input type="text" name="buscar_paciente" class="form-control" placeholder="CI o Nombre del paciente..." value="{{ $queryBusqueda ?? '' }}">
          <button type="submit" class="btn btn-primary">Buscar</button>
          @if(isset($queryBusqueda))<a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Limpiar</a>@endif
        </form>
        @if(isset($queryBusqueda))
          <hr>
          @if($pacienteBuscado)
            <div class="alert alert-success py-2">Paciente: <strong>{{ $pacienteBuscado->nombre_completo }}</strong> (CI: {{ $pacienteBuscado->ci }})</div>
            <div class="table-responsive">
              <table class="table table-sm table-hover align-middle">
                <thead class="table-light"><tr><th>Orden</th><th>Fecha</th><th>Exámenes</th><th>Estado</th></tr></thead>
                <tbody>
                @forelse($historialOrdenes as $historial)
                  <tr><td><strong>{{ $historial->codigo_orden }}</strong></td><td>{{ \Carbon\Carbon::parse($historial->fecha_solicitud)->format('d/m/Y') }}</td><td>@foreach($historial->muestras as $m)<span class="badge bg-info text-dark mb-1">{{ $m->tipoAnalisis->nombre }}</span> @endforeach</td><td><span class="badge bg-{{ $historial->estado==='Completada'?'success':'warning' }}">{{ $historial->estado }}</span></td></tr>
                @empty<tr><td colspan="4" class="text-center text-muted">No tiene órdenes previas.</td></tr>@endforelse
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-warning mt-3 mb-0">No se encontraron pacientes con ese Nombre o CI.</div>
          @endif
        @else
          <div class="text-center text-muted py-4"><i class="bi bi-search fs-3"></i><p class="mb-0">Busca un paciente para ver su historial.</p></div>
        @endif
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function checkCi(ci){if(!ci||ci.length<4)return;fetch("{{ url('/pacientes/check') }}/"+ci).then(r=>r.json()).then(data=>{const a=document.getElementById('ciAlert'),m=document.getElementById('ciAlertMsg'),f=document.getElementById('ciFeedback'),i=document.getElementById('ciInput');if(data.existe){a.classList.remove('d-none');m.textContent=`El CI ${ci} ya está registrado a nombre de ${data.paciente.nombre_completo}. Se reutilizará.`;f.textContent=`⚠️ CI existente: ${data.paciente.nombre_completo}`;f.className='form-text text-warning fw-bold';i.classList.add('border-warning');if(!sessionStorage.getItem('ciWarn_'+ci)){alert(`⚠️ CI Duplicado\nCI ${ci} ya existe: ${data.paciente.nombre_completo}`);sessionStorage.setItem('ciWarn_'+ci,'1');}}else{a.classList.add('d-none');f.textContent='✓ CI disponible';f.className='form-text text-success';i.classList.remove('border-warning');i.classList.add('border-success');}}).catch(()=>{});}
@if(session('ci_duplicado'))window.addEventListener('load',()=>alert("⚠️ {{ session('ci_duplicado') }}"));@endif
</script>
@endpush
