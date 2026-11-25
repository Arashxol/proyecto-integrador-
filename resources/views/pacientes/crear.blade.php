@extends('layouts.app')
@section('title','Registro de Paciente - DIALAB')
@section('top_title','Registrar Paciente')
@section('top_subtitle','Admisión de pacientes y selección de análisis')

@section('content')
@if(session('success'))<div class="alert alert-success alert-dismissible fade show shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
@if($errors->any())<div class="alert alert-danger shadow-sm"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

<form action="{{ route('pacientes.guardar') }}" method="POST" id="formPaciente">
@csrf
<div class="row g-4">
<div class="col-lg-5">
<div class="card shadow-sm mb-3">
<div class="card-header bg-white fw-semibold"><i class="bi bi-person"></i> Datos del Paciente</div>
<div class="card-body">
<div class="mb-2"><label class="form-label">Nombre Completo *</label><input type="text" name="nombre_completo" class="form-control" required></div>
<div class="row">
<div class="col-7 mb-2"><label class="form-label">CI *</label><input type="text" name="ci" id="ciInput" class="form-control" required onblur="checkCi(this.value)"><div id="ciFeedback" class="form-text"></div></div>
<div class="col-2 mb-2"><label class="form-label">Edad *</label><input type="number" name="edad" class="form-control" required min="0"></div>
<div class="col-3 mb-2"><label class="form-label">Sexo *</label><select name="sexo" class="form-select" required><option value="Masculino">M</option><option value="Femenino">F</option></select></div>
</div>
<div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="paciente@email.com"></div>
<div class="mb-2"><label class="form-label">Teléfono</label><input type="text" name="telefono" class="form-control"></div>
<div class="mb-2"><label class="form-label">Dirección</label><input type="text" name="direccion" class="form-control"></div>
</div>
</div>
</div>

<div class="col-lg-7">
<div class="card shadow-sm mb-3">
<div class="card-header bg-white d-flex justify-content-between align-items-center">
<span class="fw-semibold"><i class="bi bi-list-check"></i> Análisis Requeridos</span>
<span class="badge bg-primary" id="contador">0 seleccionados</span>
</div>
<div class="card-body" style="max-height: 500px; overflow-y: auto;">
@foreach(($categorias ?? []) as $categoria => $analisis)
<div class="area-card card mb-2 p-2" style="border-color: {{ $loop->iteration % 2 == 0 ? '#3b82f6' : '#10b981' }};">
<div class="d-flex align-items-center px-2 py-1" onclick="toggleArea(this)">
<i class="bi bi-chevron-down me-2"></i>
<strong>{{ $categoria }}</strong>
<span class="badge bg-light text-dark ms-auto">{{ count($analisis) }}</span>
</div>
<div class="area-body px-3 pb-2" style="display: none;">
@foreach($analisis as $a)
<label class="analisis-item">
<input type="checkbox" name="examenes[]" value="{{ $a->id }}">
<span class="flex-grow-1">{{ $a->nombre }}</span>
<span class="badge badge-precio">Bs. {{ $a->precio }}</span>
</label>
@endforeach
</div>
</div>
@endforeach
</div>
</div>
<div class="card shadow-sm border-success">
<div class="card-body d-flex justify-content-between align-items-center">
<div><strong>Total estimado:</strong> <span class="badge bg-success fs-6" id="totalPrecio">Bs. 0.00</span></div>
<button type="submit" class="btn btn-success btn-lg"><i class="bi bi-check2-circle"></i> Registrar Paciente y Generar Orden</button>
</div>
</div>
</div>
</div>
</form>

<div id="ciAlert" class="alert alert-warning alert-dismissible fade show d-none position-fixed" style="top: 80px; right: 20px; z-index: 1050; max-width: 350px;">
<strong>⚠️ CI Existente:</strong> <span id="ciAlertMsg"></span>
<button type="button" class="btn-close" onclick="document.getElementById('ciAlert').classList.add('d-none')"></button>
</div>

<style>
.area-card { cursor: pointer; transition: .2s; }
.area-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.analisis-item { display: flex; align-items: center; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: .15s; }
.analisis-item:hover { background: #f1f5f9; }
.analisis-item input { margin-right: 10px; }
.badge-precio { background: #dcfce7; color: #166534; }
</style>

<script>
function toggleArea(el) {
  const body = el.nextElementSibling;
  body.style.display = body.style.display === 'none' ? 'block' : 'none';
  el.querySelector('i').classList.toggle('bi-chevron-down');
  el.querySelector('i').classList.toggle('bi-chevron-up');
}
document.querySelectorAll('.area-body').forEach(b => b.style.display = 'block');
document.querySelectorAll('.area-card > div:first-child i').forEach(i => { i.classList.remove('bi-chevron-down'); i.classList.add('bi-chevron-up'); });
function actualizarTotal() {
  const checkboxes = document.querySelectorAll('input[name="examenes[]"]:checked');
  document.getElementById('contador').textContent = checkboxes.length + ' seleccionados';
  let total = 0;
  checkboxes.forEach(cb => {
    const precio = parseFloat(cb.closest('.analisis-item').querySelector('.badge-precio').textContent.replace('Bs. ',''));
    total += precio;
  });
  document.getElementById('totalPrecio').textContent = 'Bs. ' + total.toFixed(2);
}
document.querySelectorAll('input[name="examenes[]"]').forEach(cb => cb.addEventListener('change', actualizarTotal));
function checkCi(ci) {
  if (!ci || ci.length < 4) return;
  fetch("{{ url('/pacientes/check') }}/" + ci)
    .then(r => r.json())
    .then(data => {
      const a = document.getElementById('ciAlert'), m = document.getElementById('ciAlertMsg'), f = document.getElementById('ciFeedback'), i = document.getElementById('ciInput');
      if (data.existe) {
        a.classList.remove('d-none'); m.textContent = data.paciente.nombre_completo + ' ya registrado. Se reutilizará.';
        f.textContent = '⚠️ Existe: ' + data.paciente.nombre_completo; f.className = 'form-text text-warning fw-bold';
        if (!sessionStorage.getItem('ciWarn_' + ci)) { alert('⚠️ CI Duplicado\nCI ' + ci + ' ya existe: ' + data.paciente.nombre_completo); sessionStorage.setItem('ciWarn_' + ci, '1'); }
      } else { a.classList.add('d-none'); f.textContent = '✓ Disponible'; f.className = 'form-text text-success'; }
    }).catch(() => {});
}
</script>
@endsection
