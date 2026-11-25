@extends('layouts.app')
@section('title','QA Automatizado - DIALAB')
@section('top_title','Automatización de Pruebas QA')
@section('top_subtitle','Ejecuta suites funcionales o no funcionales y recibe los reportes (PDF + HTML) por correo')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="row g-4">

  <div class="col-lg-5">
    <div class="card shadow-sm mb-3">
      <div class="card-header bg-dark text-white">
        <i class="bi bi-play-circle"></i> Configurar Ejecución
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold small"><i class="bi bi-envelope"></i> Correo destinatario *</label>
          <input type="email" id="emailInput" class="form-control form-control-sm"
                 placeholder="correo@ejemplo.com" required oninput="validarEmail()">
          <div class="form-text">Los reportes PDF y HTML se enviarán a este correo.</div>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small"><i class="bi bi-diagram"></i> Tipo de pruebas *</label>
          <select id="tipoInput" class="form-select form-select-sm" onchange="cambiarTipo()">
            @foreach($tipos as $tipo)
              <option value="{{ $tipo }}">{{ ucfirst(str_replace('_',' ',$tipo)) }}</option>
            @endforeach
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label fw-semibold small"><i class="bi bi-collection"></i> Suites disponibles *</label>
          @foreach($catalogo as $tipo => $suites)
            <div class="mb-2" data-tipo="{{ $tipo }}">
              <span class="text-muted small">{{ ucfirst(str_replace('_',' ',$tipo)) }}:</span>
              @foreach($suites as $key => $suite)
                <div class="form-check d-inline-flex me-3">
                  <input class="form-check-input me-1 suite-cb" type="checkbox" id="suite-{{ $key }}" value="{{ $key }}">
                  <label class="form-check-label small" for="suite-{{ $key }}">{{ $suite['label'] }}</label>
                </div>
              @endforeach
            </div>
          @endforeach
          <div class="form-text">Selecciona suites. Si ninguna está marcada, se ejecutan todas.</div>
        </div>
        <div class="d-grid">
          <button id="btnEjecutar" type="button" class="btn btn-primary btn-sm"
                  onclick="ejecutarQA()" disabled>
            <i class="bi bi-play-fill"></i> Ejecutar Pruebas
          </button>
        </div>
        <div id="statusArea" class="mt-3" hidden></div>
        <div class="mt-2 small text-muted" id="infoSeleccion">
          Total seleccionado: <b id="totalSeleccionado">0</b> caso(s)
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card shadow-sm">
      <div class="card-header bg-dark text-white">
        <i class="bi bi-clipboard-check"></i> Resultados
      </div>
      <div class="card-body" id="areaResultados">
        <div id="resumen" hidden>
          <div class="d-flex flex-wrap gap-2 mb-3" id="summaryCards"></div>
        </div>
        <div id="tablaResultados" hidden></div>
        <div id="msgSinResultados" hidden class="alert alert-info small"></div>
      </div>
    </div>
  </div>

</div>

<script>
function validarEmail() {
  const v = document.getElementById('emailInput').value;
  const re = /^[\w.+-]+@[\w-]+\.[\w.-]+$/;
  document.getElementById('btnEjecutar').disabled = !(v && re.test(v));
}

function contarTotal() {
  const total = document.querySelectorAll('.suite-cb').length;
  const sel   = document.querySelectorAll('.suite-cb:checked').length;
  document.getElementById('totalSeleccionado').textContent = sel || total;
}

function cambiarTipo() {
  const tipo = document.getElementById('tipoInput').value;
  document.querySelectorAll('[data-tipo]').forEach(c => c.style.display = c.dataset.tipo === tipo ? '' : 'none');
}

function badgeEstado(estado) {
  const map = {PASS:'badge bg-success', FAIL:'badge bg-danger', SKIPPED:'badge bg-warning', NOTRUN:'badge bg-secondary'};
  const cls = (map[estado]||'badge bg-secondary').replace('NOTRUN','NOT RUN');
  return `<span class="${cls} text-white px-2 py-1 rounded">${estado}</span>`;
}

function mostrarResultados(e) {
  const back = t => { const d = document.createElement('div'); d.innerHTML = t; return d.textContent; };
  const tabla = document.getElementById('tablaResultados');
  const resumen = document.getElementById('resumen');
  const msgSinResultados = document.getElementById('msgSinResultados');

  const sinCasos = !e.resultados || Object.keys(e.resultados).length === 0;
  if (sinCasos) {
    msgSinResultados.textContent = 'No hubo resultados para mostrar.';
    msgSinResultados.hidden = false;
    tabla.hidden = true;
    resumen.hidden = true;
    return;
  }

  const summary = document.getElementById('summaryCards');
  summary.innerHTML = [
    ['Total', e.total, 'bg-dark'], ['PASS', e.pass, 'bg-success'], ['FAIL', e.fail, 'bg-danger'],
    ['SKIPPED', e.skipped, 'bg-warning text-dark'], ['NOT RUN', e.not_run, 'bg-secondary'], ['Éxito', e.pct_exito + '%', 'bg-primary']
  ].map(c => `<div class="card shadow px-3 py-2 text-center">
      <div class="fs-4 lh-1">${c[1]}</div>
      <small class="text-muted text-uppercase">${c[0]}</small></div>`).join('');
  document.getElementById('resumen').hidden = false;

  tabla.hidden = false;
  tabla.innerHTML = `<div class="table-responsive"><table class="table table-striped table-sm align-middle">
    <thead><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Estado</th><th>Observación / Fallo</th><th>ms</th></tr></thead>
    <tbody>${Object.entries(e.resultados).map(([id,r]) => `
      <tr>
        <td><code>${id}</code></td>
        <td>${r.nombre}</td>
        <td><small>${r.tipo||'-'}</small></td>
        <td>${badgeEstado(r.estado||'NOT RUN')}</td>
        <td class="small">${back(r.observacion||'-').slice(0,90)}${r.estado==='FAIL' ? '<br><span class="text-danger">' + back(r.obtenido||'').slice(0,120) + '</span>' : ''}</td>
        <td>${r.duracion_ms} ms</td>
      </tr>`).join('')}</tbody></table></div>`;
}

function ejecutarQA() {
  const email = document.getElementById('emailInput').value;
  const tipo  = document.getElementById('tipoInput').value;
  const casos = [...document.querySelectorAll('.suite-cb:checked')].map(c => c.value);
  const token = document.querySelector('meta[name="csrf-token"]').content;

  const status = document.getElementById('statusArea');
  const btn    = document.getElementById('btnEjecutar');
  status.hidden = false;
  status.innerHTML = '<i class="bi bi-clock-history"></i> <b>EN CURSO</b>';
  btn.disabled = true;

  fetch('/qa/ejecutar', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
    body: JSON.stringify({ email, tipo, casos, _token: token })
  })
  .then(async r => {
    const texto = await r.text();
    if (!r.ok) {
      const sinTags = texto.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim().slice(0, 200);
      throw new Error(`HTTP ${r.status}${r.redirected ? ' (redirigido)' : ''}${sinTags ? ' — ' + sinTags : ''}`);
    }
    try { return JSON.parse(texto); } catch { throw new Error(`HTTP ${r.status} — respuesta no es JSON: ${texto.slice(0, 200)}`); }
  })
  .then(j => {
    if (j.exito) {
      status.innerHTML = `<i class="bi bi-check-circle-fill text-success"></i> <b>FINALIZADA</b> — Email enviado: ${j.enviado ? 'Sí' : 'No'}`;
      mostrarResultados(j.ejecucion);
    } else {
      status.innerHTML = `<i class="bi bi-x-circle-fill text-danger"></i> <b>FALLIDA</b> — ${j.error || ''}`;
    }
  })
  .catch(err => {
    status.innerHTML = `<i class="bi bi-x-circle-fill text-danger"></i> <b>FALLIDA</b> — ${err.message}`;
  })
  .finally(() => btn.disabled = false);
}

document.addEventListener('DOMContentLoaded', () => { contarTotal(); });
</script>

@endsection
