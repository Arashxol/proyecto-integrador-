<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Resultados - {{ $paciente->nombre_completo }}</title>
<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    background: #fff;
    color: #1a1a2e;
    font-size: 12px;
    line-height: 1.5;
}

.page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

/* Header estilo factura */
.header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    border-bottom: 3px solid #2563eb;
    padding-bottom: 20px;
    margin-bottom: 24px;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.logo {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, #2563eb, #7c3aed);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 800;
    font-size: 20px;
}

.company-name {
    font-size: 22px;
    font-weight: 800;
    color: #1e293b;
    letter-spacing: -0.5px;
}

.company-sub {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

.header-right {
    text-align: right;
}

.doc-type {
    font-size: 28px;
    font-weight: 800;
    color: #2563eb;
    text-transform: uppercase;
    letter-spacing: 2px;
}

.doc-number {
    font-size: 13px;
    font-weight: 600;
    color: #475569;
    margin-top: 4px;
}

.doc-date {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

/* Info section */
.info-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 24px;
}

.info-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 16px;
}

.info-box-title {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #2563eb;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 4px 0;
    font-size: 12px;
}

.info-label { color: #64748b; font-weight: 500; }
.info-value { color: #1e293b; font-weight: 600; }

/* Results cards */
.result-group {
    margin-bottom: 24px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
}

.result-group-header {
    background: linear-gradient(135deg, #eff6ff, #f5f3ff);
    padding: 12px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e2e8f0;
}

.result-group-title {
    font-size: 13px;
    font-weight: 700;
    color: #1e293b;
}

.result-group-badge {
    background: #2563eb;
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

/* Table */
table {
    width: 100%;
    border-collapse: collapse;
}

thead th {
    background: #f1f5f9;
    padding: 10px 12px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #475569;
    border-bottom: 2px solid #e2e8f0;
}

tbody td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: top;
}

tbody tr:last-child td { border-bottom: none; }
tbody tr:hover { background: #fafbfc; }

.test-name {
    font-weight: 700;
    color: #1e293b;
    font-size: 12px;
}

.test-category {
    display: inline-block;
    font-size: 9px;
    font-weight: 600;
    color: #2563eb;
    background: #eff6ff;
    padding: 2px 8px;
    border-radius: 4px;
    margin-top: 4px;
}

.result-value {
    font-weight: 700;
    color: #059669;
    font-size: 13px;
}

.ref-value {
    font-size: 11px;
    color: #64748b;
    background: #f8fafc;
    padding: 2px 6px;
    border-radius: 4px;
    display: inline-block;
}

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
}

.status-validado { background: #d1fae5; color: #065f46; }
.status-pendiente { background: #fef3c7; color: #92400e; }
.status-rechazado { background: #fee2e2; color: #991b1b; }

/* Footer */
.footer {
    margin-top: 32px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
}

.signature-box {
    text-align: center;
    padding-top: 40px;
}

.signature-line {
    border-top: 2px solid #1e293b;
    padding-top: 8px;
    font-size: 11px;
    font-weight: 600;
    color: #475569;
}

.signature-label {
    font-size: 9px;
    color: #94a3b8;
    margin-top: 2px;
}

.watermark {
    margin-top: 40px;
    text-align: center;
    padding: 12px;
    border: 1px dashed #e2e8f0;
    border-radius: 8px;
    font-size: 9px;
    color: #94a3b8;
}

@media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .page { padding: 0; max-width: none; }
    .result-group { break-inside: avoid; }
}
</style>
</head>
<body>
<div class="page">

    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <div class="logo">D</div>
            <div>
                <div class="company-name">DIALAB</div>
                <div class="company-sub">Laboratorio de Análisis Clínicos</div>
                <div class="company-sub">Universidad Franz Tamayo</div>
            </div>
        </div>
        <div class="header-right">
            <div class="doc-type">Resultados</div>
            <div class="doc-number">#RES-{{ str_pad($paciente->id, 5, '0', STR_PAD_LEFT) }}-{{ date('Ymd') }}</div>
            <div class="doc-date">{{ now()->format('d/m/Y H:i') }}</div>
        </div>
    </div>

    <!-- Info -->
    <div class="info-section">
        <div class="info-box">
            <div class="info-box-title">Paciente</div>
            <div class="info-row"><span class="info-label">Nombre:</span><span class="info-value">{{ $paciente->nombre_completo }}</span></div>
            <div class="info-row"><span class="info-label">CI:</span><span class="info-value">{{ $paciente->ci }}</span></div>
            <div class="info-row"><span class="info-label">Edad:</span><span class="info-value">{{ $paciente->edad }} años</span></div>
            <div class="info-row"><span class="info-label">Sexo:</span><span class="info-value">{{ $paciente->sexo }}</span></div>
        </div>
        <div class="info-box">
            <div class="info-box-title">Contacto</div>
            <div class="info-row"><span class="info-label">Email:</span><span class="info-value">{{ $paciente->email ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Teléfono:</span><span class="info-value">{{ $paciente->telefono ?? '—' }}</span></div>
            <div class="info-row"><span class="info-label">Dirección:</span><span class="info-value">{{ $paciente->direccion ?? '—' }}</span></div>
        </div>
    </div>

    <!-- Resultados por grupo -->
    @forelse($grupos as $grupoKey => $grupo)
    @php
        [$fechaGrupo, $horaGrupo] = explode('|', $grupoKey);
    @endphp
    <div class="result-group">
        <div class="result-group-header">
            <span class="result-group-title">
                Reporte {{ $loop->iteration }}
                @if($fechaGrupo !== 'sin-fecha')
                    — {{ \Carbon\Carbon::parse($fechaGrupo)->format('d/m/Y') }}
                    @if($horaGrupo && $horaGrupo !== 'sin-hora') {{ $horaGrupo }} hrs @endif
                @endif
            </span>
            <span class="result-group-badge">{{ $grupo->count() }} análisis</span>
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Análisis</th>
                    <th style="width:80px">Muestra</th>
                    <th>Resultado</th>
                    <th style="width:90px">Referencia</th>
                    <th style="width:70px">Estado</th>
                    <th style="width:90px">Validado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grupo as $r)
                <tr>
                    <td class="text-muted">{{ $loop->iteration }}</td>
                    <td>
                        <div class="test-name">{{ $r->muestra->tipoAnalisis->nombre }}</div>
                        <span class="test-category">{{ $r->muestra->tipoAnalisis->categoria }}</span>
                    </td>
                    <td>
                        <small class="text-muted">{{ $r->muestra->codigo_muestra }}</small><br>
                        <span class="text-xs" style="color:#94a3b8">{{ $r->muestra->tipo_muestra }}</span>
                    </td>
                    <td><span class="result-value">{{ $r->resultado }}</span></td>
                    <td><span class="ref-value">{{ $r->valores_referencia }}</span></td>
                    <td>
                        <span class="status-badge status-{{ strtolower($r->estado) }}">
                            @if($r->estado === 'Validado') ✓ @elseif($r->estado === 'Pendiente') ⏳ @else ✗ @endif
                            {{ $r->estado }}
                        </span>
                    </td>
                    <td>
                        @if($r->bioquimico)
                            <small class="font-semibold">{{ $r->bioquimico->name }}</small><br>
                            <small class="text-muted">{{ \Carbon\Carbon::parse($r->fecha_validacion)->format('d/m/Y') }}</small>
                        @else
                            <small class="text-muted">—</small>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @empty
    <div class="text-center py-8 text-muted">
        <p>No hay resultados registrados para este paciente.</p>
    </div>
    @endforelse

    <!-- Footer -->
    @if($resultados->count() > 0)
    <div class="footer">
        <div class="signature-box">
            <div class="signature-line">Firma del Paciente</div>
            <div class="signature-label">Nombre y CI</div>
        </div>
        <div class="signature-box">
            <div class="signature-line">Firma del Bioquímico</div>
            <div class="signature-label">Nombre y Reg. Prof.</div>
        </div>
    </div>
    @endif

    <div class="watermark">
        Documento generado electrónicamente por DIALAB LIS • {{ now()->format('d/m/Y H:i:s') }} • Verificable en laboratorio
    </div>

</div>
</body>
</html>
