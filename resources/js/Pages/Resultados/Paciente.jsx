import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function ResultadosPaciente({ 
  paciente, 
  resultados, 
  grupos 
}) {
  return (
    <AppLayout header={`Resultados - ${paciente?.nombre_completo}`}>
      <div className="container-fluid mt-4">
        
        {/* Membrete */}
        <div className="membrete">
          <div className="d-flex justify-content-between align-items-start">
            <div>
              <h3 className="mb-0 text-success">DIALAB - Laboratorio de Análisis Clínicos</h3>
              <p className="mb-0 small">Universidad Franz Tamayo - Centro de Diagnóstico</p>
              <p className="mb-0 small text-muted">Tel: 2-2441234 • La Paz, Bolivia</p>
            </div>
            <div className="text-end">
              <p className="mb-0"><strong>Fecha:</strong> {new Date().toLocaleDateString('es-ES')}</p>
              <p className="mb-0"><strong>Paciente:</strong> {paciente?.nombre_completo}</p>
              <p className="mb-0"><strong>CI:</strong> {paciente?.ci}</p>
            </div>
          </div>
        </div>

        {/* Datos del paciente */}
        <div className="row mb-4">
          <div className="col-md-6">
            <div className="card">
              <div className="card-body">
                <strong>Edad:</strong> {paciente?.edad} años | <strong>Sexo:</strong> {paciente?.sexo}
              </div>
            </div>
          </div>
          <div className="col-md-6">
            <div className="card">
              <div className="card-body">
                <strong>Email:</strong> {paciente?.email || '—'} | <strong>Tel:</strong> {paciente?.telefono ?? '—'}
              </div>
            </div>
          </div>
        </div>

        <h5 className="mb-3">
          <i className="bi bi-clipboard2-pulse"></i> Resultados de Laboratorio
        </h5>

        {/* Resultados agrupados por fecha/hora */}
        {grupos && grupos.length > 0 ? (
          <>
            {Object.entries(grupos).map(([grupoKey, grupo]) => {
              const [fechaGrupo, horaGrupo] = grupoKey.split('|');
              const totalGrupo = grupo.length;
              return (
                <div key={grupoKey} className="card mb-4 shadow-sm">
                  <div className="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <span>
                      <strong>Reporte {grupos.keys().indexOf(grupoKey) + 1}</strong> — {totalGrupo} análisis
                    </span>
                    <span>
                      {fechaGrupo !== 'sin-fecha' ? new Date(fechaGrupo).toLocaleDateString('es-ES') : 'Sin fecha'}
                      {horaGrupo && horaGrupo !== 'sin-hora' ? ` a las ${horaGrupo}` : ''}
                    </span>
                  </div>
                  <table className="table table-bordered mb-0">
                    <thead className="table-light">
                      <tr>
                        <th>#</th>
                        <th>Análisis</th>
                        <th>Categoría</th>
                        <th>Muestra</th>
                        <th>Resultado</th>
                        <th>Referencia</th>
                        <th>Estado</th>
                        <th>Validado por</th>
                      </tr>
                    </thead>
                    <tbody>
                      {grupo.map((r, i) => (
                        <tr key={r.id}>
                          <td>{i + 1}</td>
                          <td>
                            <strong>{muestra?.tipoAnalisis?.nombre}</strong>
                          </td>
                          <td>
                            <span className="badge bg-secondary">{muestra?.tipoAnalisis?.categoria}</span>
                          </td>
                          <td>
                            {muestra.codigo_muestra}<br />
                            <small className="text-muted">{muestra.tipo_muestra}</small>
                          </td>
                          <td>
                            <strong className="text-success">{r.resultado}</strong>
                          </td>
                          <td>
                            <small>{r.valores_referencia}</small>
                          </td>
                          <td>
                            <span className={`badge bg-${r.estado === 'Validado' ? 'success' : r.estado === 'Pendiente' ? 'warning' : 'danger'}`}>
                              {r.estado}
                            </span>
                          </td>
                          <td>
                            {r.bioquimico?.name ?? '—'}<br />
                            {r.fecha_validacion ? new Date(r.fecha_validacion).toLocaleString('es-ES') : ''}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              )}
            </>
          ) : (
            <div className="alert alert-warning text-center py-4">
              No hay resultados registrados para este paciente.
            </div>
          )}

          {/* Firma */}
          {resultados?.length > 0 && (
            <div className="card shadow-sm mt-4">
              <div className="card-body text-center py-4">
                <p className="mb-0"><strong>___________________________</strong></p>
                <p className="mb-0 small">Firma y Sello del Bioquímico Validador</p>
              </div>
            </div>
          )}

          <div className="text-center text-muted small mt-4 pb-3">
            <p className="mb-0">
              Documento generado por DIALAB LIS el {new Date().toLocaleDateString('es-ES')}
            </p>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

export default ResultadosPaciente;
