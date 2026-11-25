import { Link } from '@inertiajs/react';
import {
  Users, ClipboardList, Droplet, FileText, CreditCard, Search, Home,
  LayoutDashboard, Shield, Calendar, RefreshCcw, TrendingUp, BarChart
} from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

export default function Dashboard({ 
  stats, 
  usuarios, 
  ordenes, 
  logs, 
  examenesDisponibles, 
  queryBusqueda, 
  pacienteBuscado, 
  historialOrdenes,
  userRole,
  pacienteData,
  statsPaciente,
  citas,
  muestrasPendientes
}) {
  const handleRegistrarOrden = (e) => {
    e.preventDefault();
    console.log('Registrar orden');
  };

  const estadoColors = {
    Pendiente: 'bg-yellow-100 text-yellow-700',
    'En Proceso': 'bg-blue-100 text-blue-700',
    Completada: 'bg-green-100 text-green-700',
    Cancelada: 'bg-red-100 text-red-700',
    Recolectado: 'bg-purple-100 text-purple-700',
    'En Analisis': 'bg-orange-100 text-orange-700',
    'Resultado Listo': 'bg-green-100 text-green-700',
  };

  // Si es administrador o recepcionista, mostrar el dashboard principal
  if (userRole === 'Administrador' || userRole === 'Recepcionista') {
    return (
      <AppLayout header={userRole === 'Administrador' ? 'Panel Administrador' : 'Panel Recepción'}>
        <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          {/* Estadísticas superiores */}
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Pacientes</div>
                  <p className="text-2xl font-bold text-gray-900">{stats?.pacientes || 0}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                  <Users className="w-5 h-5" />
                </div>
              </div>
            </div>
            
            <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Órdenes Pendientes</div>
                  <p className="text-2xl font-bold text-gray-900">{stats?.ordenesPendientes || 0}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                  <ClipboardList className="w-5 h-5" />
                </div>
              </div>
            </div>
            
            <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">En Análisis</div>
                  <p className="text-2xl font-bold text-gray-900">{stats?.muestrasEnProceso || 0}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                  <Droplet className="w-5 h-5" />
                </div>
              </div>
            </div>
            
            <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Completadas</div>
                  <p className="text-2xl font-bold text-gray-900">{stats?.ordenesCompletadas || 0}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                  <FileText className="w-5 h-5" />
                </div>
              </div>
            </div>
            
            <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
              <div className="flex items-center justify-between">
                <div>
                  <p className="text-sm font-medium text-gray-600">Ingresos Pagados</div>
                  <p className="text-2xl font-bold text-gray-900">Bs. {Number(stats?.ingresos || 0).toLocaleString('es-BO', { minimumFractionDigits: 2 })}</p>
                </div>
                <div className="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                  <CreditCard className="w-5 h-5" />
                </div>
              </div>
            </div>
          </div>

          <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {/* Registro de Nueva Orden */}
            <div className="col-span-2 lg:col-span-2">
              <div className="rounded-lg border border-gray-200 shadow-sm">
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-t-lg border-b border-gray-200">
                  <h3 className="text-lg font-semibold text-gray-800">
                    <i className="bi bi-plus-circle text-blue-600 mr-2"></i> Registrar Nueva Orden
                  </h3>
                </div>
                <div className="p-4 space-y-4">
                  <form onSubmit={handleRegistrarOrden} className="space-y-3">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">Nombre Completo</label>
                        <input type="text" name="nombre_completo" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">CI</label>
                        <input type="text" name="ci" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">Edad</label>
                        <input type="number" name="edad" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">Sexo</label>
                        <select name="sexo" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                          <option value="Masculino">Masculino</option>
                          <option value="Femenino">Femenino</option>
                        </select>
                      </div>
                    </div>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-sm font-medium text-gray-700">Teléfono</label>
                        <input type="text" name="telefono" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                      </div>
                    </div>
                    <div className="flex flex-col gap-1">
                      <label className="text-sm font-medium text-gray-700">Análisis</label>
                      <div className="h-24 overflow-y-auto p-2 border border-gray-300 rounded-md bg-gray-50">
                        {examenesDisponibles?.map(examen => (
                          <div key={examen.id} className="flex items-center gap-2 mt-1">
                            <input type="checkbox" name="examenes" value={examen.id} className="w-4 h-4 text-blue-600 rounded border-gray-300" />
                            <span className="text-sm text-gray-700">{examen.nombre}</span>
                            <span className="ml-auto text-xs font-medium text-green-600">Bs. {examen.precio}</span>
                          </div>
                        ))}
                      </div>
                    </div>
                    <button type="submit" className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors duration-200">
                      Generar Orden y Muestras
                    </button>
                  </form>
                </div>
              </div>
            </div>

            {/* Búsqueda de Historial */}
            <div className="col-span-2 lg:col-span-1">
              <div className="rounded-lg border border-gray-200 shadow-sm">
                <div className="flex items-center justify-between p-4 bg-gray-50 rounded-t-lg border-b border-gray-200">
                  <h3 className="text-lg font-semibold text-gray-800">
                    <i className="bi bi-search mr-2"></i> Búsqueda de Historial
                  </h3>
                </div>
                <div className="p-4 space-y-4">
                  <form method="GET" className="flex gap-2 mb-3">
                    <input
                      type="text"
                      name="buscar_paciente"
                      value={queryBusqueda || ''}
                      placeholder="CI o nombre del paciente..."
                      className="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                      Buscar
                    </button>
                    {queryBusqueda && (
                      <a href={{ route: 'dashboard' }} className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md">
                        Limpiar
                      </a>
                    )}
                  </form>
                  
                  {queryBusqueda && pacienteBuscado && (
                    <div className="bg-blue-50 border border-blue-200 rounded-md p-3">
                      <h6 className="text-sm font-semibold text-blue-800">Paciente Encontrado: {pacienteBuscado.nombre_completo} (CI: {pacienteBuscado.ci})</h6>
                      <div className="space-y-2">
                        {historialOrdenes.map((ord) => (
                          <div key={ord.id} className="border rounded p-2 bg-white/50">
                            <div className="flex justify-between">
                              <strong className="text-sm">{ord.codigo_orden}</strong>
                              <span className={`text-xs font-medium ${ord.estado === 'Completada' ? 'bg-green-100 text-green-700' : ord.estado === 'Pendiente' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700'}`}>
                                {ord.estado}
                              </span>
                            </div>
                            <div className="text-xs text-gray-600 mt-1">
                              {ord.fecha_solicitud} - {ord.muestras?.map(m => m.tipoAnalisis?.nombre).join(', ')} - Bs. {ord.total}
                            </div>
                            {ord.pago && (
                              <span className={`text-xs font-medium ${ord.pago.estado === 'Pagado' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}`}>
                                {ord.pago.estado}
                              </span>
                            )}
                          </div>
                        ))}
                      ))}
                    </div>
                  )}
                  {queryBusqueda && !pacienteBuscado && (
                    <div className="alert alert-warning py-2">
                      No encontrado
                    </div>
                  )}
                </div>
              </div>
            </div>
          </div>

          {/* Últimas Órdenes */}
          <div className="mt-6">
            <div className="rounded-lg border border-gray-200 shadow-sm">
              <div className="flex items-center justify-between p-4 bg-white rounded-t-lg border-b border-gray-200">
                <h3 className="text-lg font-semibold text-gray-800">Últimas Órdenes</h3>
                <a href={route('ordenes.pendientes')} className="text-sm text-blue-600 hover:text-blue-700 font-medium">Ver todo →</a>
              </div>
              <div className="overflow-hidden rounded-b-lg border-b border-gray-200">
                <table className="w-full text-sm text-left text-gray-700">
                  <thead className="bg-gray-50 text-xs text-gray-700">
                    <tr>
                      <th scope="col" className="px-6 py-3 font-medium">Orden N°</th>
                      <th scope="col" className="px-6 py-3 font-medium">Paciente</th>
                      <th scope="col" className="px-6 py-3 font-medium">Estado</th>
                      <th scope="col" className="px-6 py-3 font-medium">Fecha Solicitud</th>
                      <th scope="col" className="px-6 py-3 font-medium text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody className="text-sm divide-y divide-gray-200">
                    {ordenes.map((o) => (
                      <tr key={o.id} className="hover:bg-gray-50">
                        <td className="px-6 py-4 font-medium text-gray-900">{o.codigo_orden}</td>
                        <td className="px-6 py-4">
                          <div className="text-gray-900">{o.paciente?.nombre_completo}</div>
                          <div className="text-xs text-gray-500">{o.paciente?.ci}</div>
                        </td>
                        <td className="px-6 py-4">
                          <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${ordEstadoColor(o.estado)}`}>
                            {o.estado}
                          </span>
                        </td>
                        <td className="px-6 py-4 text-gray-900">{o.fecha_solicitud}</td>
                        <td className="px-6 py-4 text-right text-gray-900 font-medium">Bs. {o.total}</td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

  // Vista para Bioquímico
  if (userRole === 'bioquimico') {
    return (
      <AppLayout header="Panel Bioquímico">
        <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
          <div className="rounded-lg border border-gray-200 shadow-sm">
            <div className="flex items-center justify-between p-4 bg-white rounded-t-lg border-b border-gray-200">
              <span className="fw-semibold"><i className="bi bi-droplet"></i> Muestras Pendientes</span>
              <span className="badge bg-info">{muestrasPendientes?.length || 0} pendientes</span>
            </div>
            <div className="table-responsive">
              <table className="table table-hover align-middle mb-0">
                <thead className="table-light">
                  <tr>
                    <th>Código</th>
                    <th>Paciente</th>
                    <th>Examen</th>
                    <th>Tipo</th>
                    <th>Recolección</th>
                    <th className="text-center">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  {muestrasPendientes?.map((muestra) => (
                    <tr key={muestra.id}>
                      <td><span className="fw-semibold">{muestra.codigo_muestra}</span></td>
                      <td>
                        {muestra.orden?.paciente?.nombre_completo}
                        <br />
                        <small className="text-muted">CI: {muestra.orden?.paciente?.ci}</small>
                      </td>
                      <td>
                        {muestra.tipoAnalisis?.nombre}
                        <br />
                        <small className="text-muted">{muestra.tipoAnalisis?.categoria}</small>
                      </td>
                      <td><span className="badge bg-secondary">{muestra.tipo_muestra}</span></td>
                      <td>{new Date(muestra.fecha_recoleccion).toLocaleString('es-ES')}</td>
                      <td className="text-center">
                        <button className="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target={`#modalResultado${muestra.id}`}>
                          <i className="bi bi-pencil-square"></i> Resultado
                        </button>
                        <a href={{ route: 'muestras.show', params: { id: muestra.id } }} className="btn btn-sm btn-outline-secondary">
                          <i className="bi bi-eye"></i>
                        </a>
                      </td>
                    </tr>
                  ))}
                  {muestrasPendientes?.length === 0 && (
                    <tr>
                      <td colSpan="6" className="text-center py-5 text-muted">
                        <i className="bi bi-check-circle fs-3"></i>
                        <p className="mb-0">¡Excelente! No hay muestras pendientes.</p>
                      </td>
                    </tr>
                  )}
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </AppLayout>
    );
  }

  // Vista para Paciente
  if (userRole === 'paciente') {
    return (
      <AppLayout header="Mi Historial Clínico">
        <div className="container mt-4">
          {!pacienteData && (
            <div className="alert alert-warning text-center">
              <h4>Aún no hay registros clínicos asociados a este correo ({auth.user.email})</h4>
              <p>Si acudiste a la sucursal, asegúrate de haberle proporcionado a recepción este mismo correo electrónico.</p>
            </div>
          )}
          
          {pacienteData && (
            <>
              <div className="card shadow-sm border-success mb-4">
                <div className="card-body bg-white rounded d-flex justify-content-between align-items-center">
                  <div>
                    <h5 className="card-title text-success mb-1">{pacienteData.nombre_completo}</h5>
                    <p className="mb-0">
                      <strong>CI:</strong> {pacienteData.ci} | 
                      <strong>Edad:</strong> {pacienteData.edad} años | 
                      <strong>Email:</strong> {pacienteData.email}
                    </p>
                  </div>
                </div>
              </div>

              <div className="row g-3 mb-4">
                <div className="col-6 col-md-3">
                  <div className="card text-center shadow-sm border-primary">
                    <div className="card-body">
                      <h4>{statsPaciente?.total || 0}</h4>
                      <small>Total análisis</small>
                    </div>
                  </div>
                </div>
                <div className="col-6 col-md-3">
                  <div className="card text-center shadow-sm border-warning">
                    <div className="card-body">
                      <h4>{statsPaciente?.tomadas || 0}</h4>
                      <small>Tomadas (Recolectado)</small>
                    </div>
                  </div>
                </div>
                <div className="col-6 col-md-3">
                  <div className="card text-center shadow-sm border-info">
                    <div className="card-body">
                      <h4>{statsPaciente?.enProceso || 0}</h4>
                      <small>En proceso</small>
                    </div>
                  </div>
                </div>
                <div className="col-6 col-md-3">
                  <div className="card text-center shadow-sm border-success">
                    <div className="card-body">
                      <h4>{statsPaciente?.validadas || 0}</h4>
                      <small>Resultados listos</small>
                    </div>
                  </div>
                </div>
              </div>

              <ul className="nav nav-pills mb-3" role="tablist">
                <li className="nav-item"><button className="nav-link active" data-bs-toggle="pill" data-bs-target="#tomadas">🩸 Tomadas</button></li>
                <li className="nav-item"><button className="nav-link" data-bs-toggle="pill" data-bs-target="#proceso">⏳ En proceso</button></li>
                <li className="nav-item"><button className="nav-link" data-bs-toggle="pill" data-bs-target="#resultados">✅ Resultados</button></li>
                <li className="nav-item"><button className="nav-link" data-bs-toggle="pill" data-bs-target="#citas">📅 Citas</button></li>
              </ul>

              <div className="tab-content">
                <div className="tab-pane fade show active" id="tomadas">
                  {todasMuestras?.filter((m) => m.estado === 'Recolectado').length > 0 ? (
                    <>
                      {todasMuestras
                        ?.filter((m) => m.estado === 'Recolectado')
                        .groupBy((m) => m.orden?.codigo_orden)
                        .map((muestras, codigo) => (
                          <div key={codigo} className="card shadow-sm mb-2">
                            <div className="card-header bg-light d-flex justify-content-between">
                              <strong>{codigo}</strong>
                              <small className="text-muted">{muestras[0].orden?.fecha_solicitud}</small>
                            </div>
                            <ul className="list-group list-group-flush">
                              {muestras.map((muestra) => (
                                <li key={muestra.id} className="list-group-item d-flex justify-content-between align-items-center">
                                  <div>
                                    <h6 className="mb-0">{muestra.tipoAnalisis?.nombre}</h6>
                                    <small className="text-muted">
                                      {muestra.tipo_muestra} | {muestra.codigo_muestra} | {new Date(muestra.fecha_recoleccion).toLocaleString('es-ES')}
                                    </small>
                                  </div>
                                  <span className="badge bg-warning text-dark">Tomada</span>
                                </li>
                              ))}
                            </ul>
                          </div>
                        ))
                      )}
                    </>
                  ) : (
                    <p className="text-muted text-center py-4">No tienes muestras en estado Tomada.</p>
                  )}
                </div>

                <div className="tab-pane fade" id="proceso">
                  {todasMuestras?.filter((m) => ['En Transito', 'En Analisis'].includes(m.estado)).length > 0 ? (
                    <>
                      {todasMuestras
                        ?.filter((m) => ['En Transito', 'En Analisis'].includes(m.estado))
                        .groupBy((m) => m.orden?.codigo_orden)
                        .map((muestras, codigo) => (
                          <div key={codigo} className="card shadow-sm mb-2">
                            <div className="card-header bg-info text-white d-flex justify-content-between">
                              <strong>{codigo}</strong>
                              <small>{muestras[0].orden?.estado}</small>
                            </div>
                            <ul className="list-group list-group-flush">
                              {muestras.map((muestra) => (
                                <li key={muestra.id} className="list-group-item d-flex justify-content-between">
                                  <div>
                                    <h6 className="mb-0">{muestra.tipoAnalisis?.nombre}</h6>
                                    <small className="text-muted">
                                      {muestra.codigo_muestra} | desde {new Date(muestra.fecha_recoleccion).toLocaleDateString('es-ES')}
                                    </small>
                                  </div>
                                  <span className="badge bg-info">{muestra.estado}</span>
                                </li>
                              ))}
                            </ul>
                          </div>
                        ))
                      ) : (
                        <p className="text-muted text-center py-3">No tienes análisis en proceso.</p>
                      )}
                  </div>

                  <div className="tab-pane fade" id="resultados">
                    {todasMuestras?.filter((m) => m.resultado?.estado === 'Validado').length > 0 ? (
                      <>
                        <div className="d-flex justify-content-between align-items-center mb-2">
                          <h6 className="mb-0">Resultados listos ({todasMuestras.filter((m) => m.resultado?.estado === 'Validado').length})</h6>
                          <small className="text-muted">Resultados validados por el laboratorio</small>
                        </div>
                        {todasMuestras
                          ?.filter((m) => m.resultado?.estado === 'Validado')
                          .groupBy((m) => m.orden?.codigo_orden)
                          .map((muestras, codigo) => (
                            <div key={codigo} className="card shadow-sm mb-3 border-success">
                              <div className="card-header bg-light d-flex justify-content-between align-items-center">
                                <strong>Orden {codigo}</strong>
                                <span className="text-muted">{muestras[0].orden?.fecha_solicitud}</span>
                              </div>
                              <ul className="list-group list-group-flush">
                                {muestras.map((muestra) => (
                                  <li key={muestra.id} className="list-group-item">
                                    <h6 className="mb-1">{muestra.tipoAnalisis?.nombre} <span className="badge bg-success ms-2">Validado</span></h6>
                                    <div className="row">
                                      <div className="col-md-4">
                                        <small className="text-muted d-block">Muestra</small>
                                        <strong>{muestra.codigo_muestra} ({muestra.tipo_muestra})</strong>
                                      </div>
                                      <div className="col-md-4">
                                        <small className="text-muted d-block">Resultado</small>
                                        <strong className="text-success">{muestra.resultado?.resultado}</strong>
                                      </div>
                                      <div className="col-md-4">
                                        <small className="text-muted d-block">Referencia</small>
                                        <span>{muestra.resultado?.valores_referencia}</span>
                                      </div>
                                    </div>
                                    {muestra.resultado?.interpretacion && (
                                      <div className="mt-2">
                                        <small className="text-muted">Interpretación:</small>{' '}
                                        <span className="text-dark">{muestra.resultado.interpretacion}</span>
                                      </div>
                                    )}
                                    <small className="text-muted">
                                      Validado el {new Date(muestra.resultado.fecha_validacion).toLocaleString('es-ES')}
                                    </small>
                                  </li>
                                ))}
                              </ul>
                            </div>
                          ))
                        )}
                    </>
                  ) : (
                    <p className="text-muted text-center py-4">Aún no tienes resultados validados.</p>
                  )}
                </div>

                <div className="tab-pane fade" id="citas">
                  <div className="card shadow-sm">
                    <div className="card-header bg-white d-flex justify-content-between align-items-center">
                      <span>Mis próximas citas</span>
                      <a href={route('paciente.citas.index')} className="btn btn-sm btn-success">
                        Gestionar
                      </a>
                    </div>
                    {citas?.length > 0 ? (
                      <ul className="list-group list-group-flush">
                        {citas.map((c) => (
                          <li key={c.id} className="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                              <strong>{c.tipo}</strong>{' '}
                              - {new Date(c.fecha).toLocaleDateString('es-ES')}
                              {new Date(c.hora).toLocaleTimeString('es-ES')}
                              <br />
                              <small className="text-muted">{c.motivo ?? 'Sin motivo'}</small>
                            </div>
                            <span className={`badge bg-${c.estado === 'Confirmada' ? 'success' : c.estado === 'Pendiente' ? 'warning' : 'secondary'}`}>
                              {c.estado}
                            </span>
                          </li>
                        ))}
                      </ul>
                    ) : (
                      <div className="card-body text-center text-muted">
                        No tienes citas agendadas.
                      </div>
                    )}
                  </div>
                </div>
              </div>

              <div className="mt-6">
                <h6 className="fw-semibold">
                  <i className="bi bi-clock-history"></i> Mis Órdenes
                </h6>
                {ordenes.length > 0 ? (
                  ordenes.map((orden) => (
                    <div key={orden.id} className="card shadow-sm mb-3">
                      <div className="card-header bg-white d-flex justify-content-between">
                        <strong>{orden.codigo_orden}</strong>
                        <span>
                          <span className={`badge bg-${orden.estado === 'Completada' ? 'success' : orden.estado === 'Pendiente' ? 'warning' : 'info'}`}>
                            {orden.estado}
                          </span>
                          <span className="text-muted ms-2">
                            {new Date(orden.fecha_solicitud).toLocaleDateString('es-ES')} - Bs. {orden.total}
                          </span>
                        </span>
                      </div>
                      <ul className="list-group list-group-flush">
                        {orden.muestras.map((muestra) => (
                          <li key={muestra.id} className="list-group-item d-flex justify-content-between">
                            <div>
                              <h6 className="mb-0">
                                {muestra.tipoAnalisis?.nombre}
                                <small className="text-muted">({muestra.tipoAnalisis?.categoria})</small>
                              </h6>
                              <small className="text-muted">
                                {muestra.tipo_muestra} | {muestra.codigo_muestra} | {muestra.estado}
                              </small>
                              {muestra.resultado?.estado === 'Validado' && (
                                <br />
                                <small>
                                  Resultado: <strong className="text-success">{muestra.resultado.resultado}</strong>{' '}
                                                | Ref: {muestra.resultado.valores_referencia}
                                </small>
                              )}
                            </div>
                            <span className="badge bg-light text-dark border">
                              {muestra.estado}
                            </span>
                          </li>
                        ))}
                      </ul>
                    </div>
                  ))
                ) : (
                  <p className="text-muted text-center">No tienes órdenes.</p>
                )}
              </div>
            </>
          )}
        </div>
      </AppLayout>
    );
  }

  return <AppLayout header="Dashboard">{/* fallback */}</AppLayout>;
}

const ordEstadoColor = (estado) => {
  const colors = {
    Pendiente: 'bg-yellow-100 text-yellow-700',
    'En Proceso': 'bg-blue-100 text-blue-700',
    Completada: 'bg-green-100 text-green-700',
    Cancelada: 'bg-red-100 text-red-700',
    Recolectado: 'bg-purple-100 text-purple-700',
    'En Analisis': 'bg-orange-100 text-orange-700',
    'Resultado Listo': 'bg-green-100 text-green-700',
  };
  return colors[estado] || 'bg-gray-100 text-gray-700';
};