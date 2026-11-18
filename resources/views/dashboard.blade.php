<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIALAB - LIS Control</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">🔬 DIALAB - LIS</span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3">👨‍⚕️ {{ auth()->user()->name }} ({{ auth()->user()->rol }})</span>
                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-sm btn-outline-light" type="submit">Cerrar Sesión</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Pacientes Registrados</h5>
                        <p class="card-text fs-2">{{ $pacientes }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Órdenes Pendientes</h5>
                        <p class="card-text fs-2">{{ $ordenesPendientes }}</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Muestras en Análisis</h5>
                        <p class="card-text fs-2">{{ $muestrasEnProceso }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">Fase 1: Admisión de Paciente y Orden</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('laboratorio.registrar') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nombre Completo del Paciente</label>
                                <input type="text" name="nombre_completo" class="form-control" required>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cédula de Identidad (CI)</label>
                                    <input type="text" name="ci" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Edad</label>
                                    <input type="number" name="edad" class="form-control" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Sexo</label>
                                    <select name="sexo" class="form-select" required>
                                        <option value="Masculino">M</option>
                                        <option value="Femenino">F</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Médico Solicitante</label>
                                <input type="text" name="medico_solicitante" class="form-control" placeholder="Ej: Dr. Alvarez">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Selección de Análisis Requeridos</label>
                                <div class="p-3 border rounded bg-white" style="max-height: 150px; overflow-y: auto;">
                                    @foreach($examenesDisponibles as $examen)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="examenes[]" value="{{ $examen->id }}" id="exam_{{ $examen->id }}">
                                            <label class="form-check-label" for="exam_{{ $examen->id }}">
                                                {{ $examen->nombre }} (<span class="text-success">{{ $examen->precio }} Bs</span>)
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Generar Orden y Muestras</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0">Monitoreo General de Órdenes</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Paciente</th>
                                        <th>Fecha</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($ordenes as $orden)
                                        <tr>
                                            <td><strong>{{ $orden->codigo_orden }}</strong></td>
                                            <td>{{ $orden->paciente->nombre_completo }}</td>
                                            <td>{{ $orden->fecha_solicitud }}</td>
                                            <td>{{ $orden->total }} Bs</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">{{ $orden->estado }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No hay órdenes procesadas el día de hoy.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>