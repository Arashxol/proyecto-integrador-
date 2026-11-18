@extends('layouts.app')
@section('title','Panel Administrador - DIALAB')
@section('top_title','Panel Administrador')
@section('top_subtitle','Control total del LIS')

@section('content')
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-2"><div class="card shadow-sm h-100"><div class="card-body text-center"><div class="text-muted small">Pacientes</div><h3 class="mb-0">{{ $stats['pacientes'] }}</h3></div></div></div>
  <div class="col-6 col-lg-2"><div class="card shadow-sm h-100 border-start border-4 border-warning"><div class="card-body text-center"><div class="text-muted small">Órdenes Pendientes</div><h3 class="mb-0">{{ $stats['ordenesPendientes'] }}</h3></div></div></div>
  <div class="col-6 col-lg-2"><div class="card shadow-sm h-100 border-start border-4 border-info"><div class="card-body text-center"><div class="text-muted small">En Análisis</div><h3 class="mb-0">{{ $stats['muestrasEnProceso'] }}</h3></div></div></div>
  <div class="col-6 col-lg-2"><div class="card shadow-sm h-100 border-start border-4 border-success"><div class="card-body text-center"><div class="text-muted small">Completadas</div><h3 class="mb-0">{{ $stats['ordenesCompletadas'] }}</h3></div></div></div>
  <div class="col-12 col-lg-4"><div class="card shadow-sm h-100 bg-success text-white"><div class="card-body text-center"><div class="small opacity-75">Ingresos Pagados</div><h3 class="mb-0">Bs. {{ number_format($stats['ingresos'],2) }}</h3></div></div></div>
</div>

<div class="row g-4">
  <div class="col-lg-8">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white d-flex justify-content-between align-items-center"><span class="fw-semibold"><i class="bi bi-plus-circle"></i> Registrar Nueva Orden</span><span class="badge bg-primary">Recepción</span></div>
      <div class="card-body">
        <form action="{{ route('laboratorio.registrar') }}" method="POST">
          @csrf
          <div class="row">
            <div class="col-md-6 mb-2"><label class="form-label">Nombre Completo *</label><input name="nombre_completo" class="form-control" required></div>
            <div class="col-md-3 mb-2"><label class="form-label">CI *</label><input name="ci" id="ciInputAdmin" class="form-control" required onblur="checkCiAdmin(this.value)"><div id="ciFeedbackAdmin" class="form-text"></div></div>
            <div class="col-md-3 mb-2"><label class="form-label">Edad *</label><input name="edad" type="number" class="form-control" required></div>
            <div class="col-md-3 mb-2"><label class="form-label">Sexo *</label><select name="sexo" class="form-select" required><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option></select></div>
            <div class="col-md-5 mb-2"><label class="form-label">Email</label><input name="email" type="email" class="form-control" placeholder="paciente@email.com"></div>
            <div class="col-md-4 mb-2"><label class="form-label">Teléfono</label><input name="telefono" class="form-control"></div>
            <div class="col-md-12 mb-2"><label class="form-label">Análisis *</label><div class="border p-2 rounded bg-light" style="max-height:140px;overflow-y:auto">@foreach($examenesDisponibles as $ex)<div class="form-check"><input class="form-check-input" type="checkbox" name="examenes[]" value="{{ $ex->id }}" id="ex{{ $ex->id }}"><label class="form-check-label" for="ex{{ $ex->id }}">{{ $ex->nombre }} <small class="text-success">Bs.{{ $ex->precio }}</small> <small class="text-muted">({{ $ex->categoria }})</small></label></div>@endforeach</div></div>
          </div>
          <button class="btn btn-primary w-100"><i class="bi bi-check2-circle"></i> Registrar Orden</button>
        </form>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white"><i class="bi bi-search"></i> Búsqueda de Historial</div>
      <div class="card-body">
        <form method="GET" class="d-flex gap-2 mb-3"><input name="buscar_paciente" value="{{ $queryBusqueda }}" placeholder="CI o nombre del paciente" class="form-control"><button class="btn btn-outline-primary"><i class="bi bi-search"></i> Buscar</button></form>
        @if($pacienteBuscado)
          <div class="alert alert-success py-2">Paciente: <strong>{{ $pacienteBuscado->nombre_completo }}</strong> (CI: {{ $pacienteBuscado->ci }})</div>
          @foreach($historialOrdenes as $ord)
            <div class="border rounded p-2 mb-2 bg-light"><div class="d-flex justify-content-between"><strong>{{ $ord->codigo_orden }}</strong><span class="badge bg-{{ $ord->estado==='Completada'?'success':'warning' }}">{{ $ord->estado }}</span></div><small class="text-muted">{{ $ord->fecha_solicitud }} • {{ $ord->muestras->pluck('tipoAnalisis.nombre')->join(', ') }} • Bs.{{ $ord->total }}</small> @if($ord->pago)<span class="badge bg-{{ $ord->pago->estado=='Pagado'?'success':'warning' }} ms-2">{{ $ord->pago->estado }}</span>@endif</div>
          @endforeach
        @elseif($queryBusqueda)<div class="alert alert-warning py-2">No encontrado</div>@endif
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card shadow-sm mb-3 border-info">
      <div class="card-header bg-info d-flex justify-content-between align-items-center"><span><i class="bi bi-brain"></i> ML - Detección de Anomalías</span><span class="badge bg-dark">Admin/Bioquímico</span></div>
      <div class="card-body text-center">
        <p class="small text-muted mb-2">Detecta resultados anómalos en el laboratorio usando Machine Learning</p>
        <a href="{{ route('deteccion.index') }}" class="btn btn-info w-100 fw-semibold"><i class="bi bi-search"></i> Ver Detección</a>
      </div>
    </div>
    <div class="card shadow-sm mb-3 border-warning">
      <div class="card-header bg-warning d-flex justify-content-between align-items-center"><span><i class="bi bi-hdd-stack"></i> Backups</span><span class="badge bg-dark">Admin</span></div>
      <div class="card-body text-center">
        <p class="small text-muted mb-2">Respaldo de <code>laboratorio_pii</code></p>
        <form action="{{ route('admin.backups.generar') }}" method="POST">@csrf<button class="btn btn-warning w-100 fw-semibold"><i class="bi bi-cloud-arrow-up"></i> Generar Backup</button></form>
        <a href="{{ route('admin.backups') }}" class="btn btn-outline-secondary btn-sm w-100 mt-2">Ver historial</a>
      </div>
    </div>
    <div class="card shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><span>Usuarios por Rol</span><a href="{{ route('admin.usuarios') }}" class="btn btn-sm btn-outline-primary">Gestionar</a></div><ul class="list-group list-group-flush">@foreach($usuarios as $u)<li class="list-group-item d-flex justify-content-between"><span>{{ $u->rol }}</span><span class="badge bg-primary">{{ $u->total }}</span></li>@endforeach</ul></div>
    <div class="card shadow-sm mb-3"><div class="card-header bg-white d-flex justify-content-between"><span>Últimas Órdenes</span><a href="{{ route('ordenes.pendientes') }}" class="small">Ver todo</a></div><ul class="list-group list-group-flush">@foreach($ordenes as $o)<li class="list-group-item"><div class="fw-semibold">{{ $o->codigo_orden }}</div><small class="text-muted">{{ $o->paciente->nombre_completo }} — {{ $o->estado }}</small></li>@endforeach</ul></div>
    <div class="card shadow-sm"><div class="card-header bg-white d-flex justify-content-between"><span>Auditoría Reciente</span><a href="{{ route('admin.logs') }}" class="small">Ver logs</a></div><ul class="list-group list-group-flush">@forelse($logs as $l)<li class="list-group-item"><small><strong>{{ $l->user->name ?? 'Sistema' }}</strong>: {{ $l->accion }}<br><span class="text-muted">{{ $l->created_at->format('d/m H:i') }}</span></small></li>@empty<li class="list-group-item text-muted small">Sin registros</li>@endforelse</ul></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function checkCiAdmin(ci){if(!ci||ci.length<4)return;fetch("{{ url('/pacientes/check') }}/"+ci).then(r=>r.json()).then(data=>{const a=document.getElementById('ciAlert'),m=document.getElementById('ciAlertMsg'),f=document.getElementById('ciFeedbackAdmin'),i=document.getElementById('ciInputAdmin');if(data.existe){a.classList.remove('d-none');m.textContent=`El CI ${ci} ya está registrado como ${data.paciente.nombre_completo}. Se reutilizará sin duplicar.`;f.textContent=`⚠️ CI existente: ${data.paciente.nombre_completo}`;f.className='form-text text-warning fw-bold';i.classList.add('border-warning');if(!sessionStorage.getItem('ciWarn_'+ci)){alert(`⚠️ CI Duplicado\nCI ${ci} ya existe: ${data.paciente.nombre_completo}`);sessionStorage.setItem('ciWarn_'+ci,'1');}}else{a.classList.add('d-none');f.textContent='✓ CI disponible';f.className='form-text text-success';i.classList.remove('border-warning');i.classList.add('border-success');}}).catch(()=>{});}
@if(session('ci_duplicado'))window.addEventListener('load',()=>alert("⚠️ {{ session('ci_duplicado') }}"));@endif
</script>
@endpush
