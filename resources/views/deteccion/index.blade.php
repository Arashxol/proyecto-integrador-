@extends('layouts.app')

@section('title','Detección de Anomalías - DIALAB')
@section('top_title','Detección de Anomalías')
@section('top_subtitle','Compara resultados contra datos históricos')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="bi bi-search"></i> Detectar Anomalía en Resultado</h5>
                </div>
                <div class="card-body p-4">
                    
                    <!-- Estadísticas rápidas -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light text-center py-3 rounded-3">
                                <div class="text-muted small">Resultados Validados</div>
                                <h4 class="mb-0 text-primary">{{ $totalValidados }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light text-center py-3 rounded-3">
                                <div class="text-muted small">Tipos de Análisis</div>
                                <h4 class="mb-0 text-primary">{{ $totalAnalisis }}</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light text-center py-3 rounded-3">
                                <div class="text-muted small">Pacientes en BD</div>
                                <h4 class="mb-0 text-primary">{{ $usuario->name }}</h4>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('deteccion.evaluar') }}" method="POST" id="formDeteccion">
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Seleccionar Resultado de la BD</label>
                                <select name="resultado_id" class="form-select" required id="resultadoSelect">
                                    <option value="">-- Seleccionar un resultado --</option>
                                    @foreach($resultados as $r)
                                        <option value="{{ $r->id }}" 
                                            data-resultado="{{ $r->resultado }}"
                                            data-ref="{{ $r->valores_referencia }}"
                                            data-examen="{{ $r->muestra->tipoAnalisis->nombre }}"
                                            data-paciente="{{ $r->muestra->orden->paciente->nombre_completo }}"
                                            data-ci="{{ $r->muestra->orden->paciente->ci }}"
                                            data-orden="{{ $r->muestra->orden->codigo_orden }}">
                                            {{ $r->muestra->orden->codigo_orden }} - {{ $r->muestra->tipoAnalisis->nombre }} - {{ $r->muestra->orden->paciente->nombre_completo }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Selecciona un resultado ya registrado en la base de datos</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Resultado Obtenido</label>
                                <input type="text" name="resultado_valor" class="form-control" id="resultadoValor" 
                                       placeholder="Se cargará automáticamente" readonly>
                                <div class="form-text">Valor extraído de la base de datos</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Referencia</label>
                                <input type="text" name="referencia" class="form-control" id="referenciaInput" readonly>
                                <div class="form-text">Rango de referencia del examen</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Paciente</label>
                                <input type="text" name="paciente" class="form-control" id="pacienteInput" readonly>
                                <div class="form-text">Nombre del paciente</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Código Orden</label>
                                <input type="text" name="codigoOrden" class="form-control" id="codigoOrdenInput" readonly>
                                <div class="form-text">Código de la orden</div>
                            </div>

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg w-100" id="btnEvaluar">
                                    <i class="bi bi-search"></i> Evaluar Resultado
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('resultadoSelect').addEventListener('change', function() {
    const select = this;
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        // Llenar automáticamente los campos
        document.getElementById('resultadoValor').value = selectedOption.getAttribute('data-resultado');
        document.getElementById('referenciaInput').value = selectedOption.getAttribute('data-ref');
        document.getElementById('pacienteInput').value = selectedOption.getAttribute('data-paciente');
        document.getElementById('codigoOrdenInput').value = selectedOption.getAttribute('data-orden');
    } else {
        document.getElementById('resultadoValor').value = '';
        document.getElementById('referenciaInput').value = '';
        document.getElementById('pacienteInput').value = '';
        document.getElementById('codigoOrdenInput').value = '';
    }
});
</script>
@endpush