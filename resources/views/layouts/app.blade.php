<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title','DIALAB LIS')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<style>
:root{ --sidebar-w:260px; }
body{ background:#f1f3f6; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
.sidebar{
  position:fixed; inset:0 auto 0 0; width:var(--sidebar-w); background:#0f172a; color:#e2e8f0;
  display:flex; flex-direction:column; z-index:1040; overflow-y:auto;
}
.sidebar-brand{
  padding:18px 18px; border-bottom:1px solid rgba(255,255,255,.08);
  display:flex; align-items:center; gap:10px; font-weight:700; letter-spacing:.3px;
}
.sidebar-brand .logo{
  width:38px; height:38px; border-radius:10px; background:#22c55e; display:grid; place-items:center; color:#0f172a; font-weight:800;
}
.nav-section{ padding:14px 12px 6px; font-size:.72rem; text-transform:uppercase; letter-spacing:.08em; color:#94a3b8; }
.sidebar .nav-link{
  color:#cbd5e1; border-radius:10px; padding:9px 12px; margin:2px 8px; display:flex; align-items:center; gap:10px; font-size:.92rem;
}
.sidebar .nav-link:hover{ background:rgba(255,255,255,.07); color:#fff; }
.sidebar .nav-link.active{ background:#1d4ed8; color:#fff; }
.sidebar-footer{ margin-top:auto; padding:12px; border-top:1px solid rgba(255,255,255,.08); }
.content{ margin-left:var(--sidebar-w); min-height:100vh; }
.topbar{
  background:#fff; border-bottom:1px solid #e2e8f0; padding:12px 20px; position:sticky; top:0; z-index:1020;
  display:flex; align-items:center; justify-content:space-between; gap:12px;
}
@media(max-width: 991px){
  .sidebar{ transform:translateX(-100%); transition:.25s; }
  .sidebar.open{ transform:none; }
  .content{ margin-left:0; }
}
.badge-role{ background:#e2e8f0; color:#0f172a; }
.card{ border:0; border-radius:14px; }
.card-header{ border-radius:14px 14px 0 0 !important; }
</style>
@stack('head')
</head>
<body>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="logo">Dx</div>
    <div>
      <div style="line-height:1">DIALAB</div>
      <small style="opacity:.7; font-weight:500">LIS • Laboratorio Clínico</small>
    </div>
  </div>

  <div class="px-2 py-2">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
  </div>

  @if(in_array(auth()->user()->rol, ['Administrador']))
  <div class="nav-section">Administración</div>
  <a href="{{ route('admin.usuarios') }}" class="nav-link {{ request()->routeIs('admin.usuarios*') ? 'active' : '' }}"><i class="bi bi-people"></i> Usuarios</a>
  <a href="{{ route('admin.pacientes') }}" class="nav-link {{ request()->routeIs('admin.pacientes') ? 'active' : '' }}"><i class="bi bi-person-vcard"></i> Pacientes</a>
  <a href="{{ route('admin.logs') }}" class="nav-link {{ request()->routeIs('admin.logs') ? 'active' : '' }}"><i class="bi bi-journal-text"></i> Logs Auditoría</a>
  <a href="{{ route('admin.backups') }}" class="nav-link {{ request()->routeIs('admin.backups*') ? 'active' : '' }}"><i class="bi bi-cloud-arrow-down"></i> Backups</a>
  @endif

  @if(in_array(auth()->user()->rol, ['Administrador','Recepcionista','Bioquimico']))
  <div class="nav-section">Recepción</div>
  <a href="{{ route('pacientes.crear') }}" class="nav-link {{ request()->routeIs('pacientes.crear') ? 'active' : '' }}"><i class="bi bi-person-plus"></i> Registrar Paciente</a>
  <a href="{{ route('citas.index') }}" class="nav-link {{ request()->routeIs('citas.*') ? 'active' : '' }}"><i class="bi bi-calendar-event"></i> Citas</a>
  @endif

  <div class="nav-section">Laboratorio</div>
  <a href="{{ route('muestras.index') }}" class="nav-link {{ request()->routeIs('muestras.*') ? 'active' : '' }}"><i class="bi bi-droplet"></i> Muestras</a>
  <a href="{{ route('ordenes.pendientes') }}" class="nav-link {{ request()->routeIs('ordenes.pendientes') ? 'active' : '' }}"><i class="bi bi-clipboard2-pulse"></i> Órdenes Pendientes</a>
  <a href="{{ route('resultados.index') }}" class="nav-link {{ request()->routeIs('resultados.index') ? 'active' : '' }}"><i class="bi bi-clipboard-check"></i> Resultados</a>

  @if(in_array(auth()->user()->rol, ['Administrador','Recepcionista','Bioquimico']))
  <div class="nav-section">Reportes</div>
  <a href="{{ route('laboratorio.reportes') }}" class="nav-link {{ request()->routeIs('laboratorio.reportes') ? 'active' : '' }}"><i class="bi bi-graph-up"></i> Reportes</a>
  @endif

  @if(auth()->user()->rol==='Paciente')
  <div class="nav-section">Paciente</div>
  <a href="{{ route('paciente.citas.index') }}" class="nav-link {{ request()->routeIs('paciente.citas.*') ? 'active' : '' }}"><i class="bi bi-calendar-check"></i> Mis Citas</a>
  <a href="{{ route('dashboard') }}#resultados" class="nav-link"><i class="bi bi-file-earmark-medical"></i> Mis Resultados</a>
  @endif

  @if(in_array(auth()->user()->rol, ['Administrador','Bioquimico']))
  <div class="nav-section">QA & Testing</div>
  <a href="{{ route('qa.index') }}" class="nav-link {{ request()->routeIs('qa.*') ? 'active' : '' }}">
    <i class="bi bi-bug"></i> Test Runner
  </a>
  @endif

  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-2">
      <div class="rounded-circle bg-white text-dark d-grid" style="width:36px;height:36px;place-items:center;font-weight:700">{{ strtoupper(substr(auth()->user()->name,0,1)) }}</div>
      <div style="line-height:1.1">
        <div style="font-size:.9rem; font-weight:600">{{ auth()->user()->name }}</div>
        <small style="opacity:.7">{{ auth()->user()->email }}</small><br>
        <span class="badge badge-role mt-1">{{ auth()->user()->rol }}</span>
      </div>
    </div>
    <form action="{{ route('logout') }}" method="POST" class="mt-3">@csrf<button class="btn btn-outline-light btn-sm w-100"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form>
  </div>
</aside>

<!-- Content -->
<div class="content">
  <div class="topbar">
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-outline-secondary d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('open')"><i class="bi bi-list"></i></button>
      <div>
        <div style="font-weight:700">@yield('top_title','Panel')</div>
        <small class="text-muted">@yield('top_subtitle','Sistema de Información de Laboratorio')</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="d-none d-md-inline text-muted small">{{ now()->format('d/m/Y H:i') }}</span>
      <span class="badge bg-success d-none d-md-inline">● En línea</span>
    </div>
  </div>

  <div class="p-3 p-md-4">
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('ci_duplicado'))<div class="alert alert-warning alert-dismissible fade show border-warning shadow-sm"><strong>⚠️ CI Duplicado:</strong> {{ session('ci_duplicado') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if($errors->any())<div class="alert alert-danger shadow-sm"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif
    <div id="ciAlert" class="alert alert-warning alert-dismissible fade show d-none shadow-sm"><strong>⚠️ Atención:</strong> <span id="ciAlertMsg"></span><button type="button" class="btn-close" onclick="document.getElementById('ciAlert').classList.add('d-none')"></button></div>
    @yield('content')
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>