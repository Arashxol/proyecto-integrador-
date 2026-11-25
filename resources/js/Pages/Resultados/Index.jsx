import { Link } from '@inertiajs/react';
import {
  Users, ClipboardList, Droplet, FileText, CreditCard, Search, Home,
  LayoutDashboard, Shield, Calendar, RefreshCcw, TrendingUp, BarChart,
  Search as SearchIcon, Filter, Print
} from 'lucide-react';
import AppLayout from '@/Layouts/AppLayout';

export default function ResultadosIndex({ 
  resultados, 
  total, 
  validados, 
  pendientes, 
  rechazados, 
  pacientes 
}) {
  const estadoColors = {
    Validado: 'bg-green-100 text-green-700',
    Pendiente: 'bg-yellow-100 text-yellow-700',
    Rechazado: 'bg-red-100 text-red-700',
  };

  return (
    <AppLayout header="Resultados - DIALAB">
      <div className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        
        {/* Estadísticas */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
          <div className="rounded-lg border border-gray-200 shadow-sm p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Total Resultados</p>
                <p className="text-2xl font-bold text-gray-900">{total || 0}</p>
              </div>
              <div className="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                <ClipboardList className="w-5 h-5" />
              </div>
            </div>
          </div>
          <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Validados</div>
                <p className="text-2xl font-bold text-gray-900">{validados || 0}</p>
              </div>
              <div className="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                <FileText className="w-5 h-5" />
              </div>
            </div>
          </div>
          <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Pendientes</div>
                <p className="text-2xl font-bold text-gray-900">{pendientes || 0}</p>
              </div>
              <div className="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600">
                <RefreshCcw className="w-5 h-5" />
              </div>
            </div>
          </div>
          <div className="overflow-hidden rounded-lg border border-gray-200 shadow-sm p-4">
            <div className="flex items-center justify-between">
              <div>
                <p className="text-sm font-medium text-gray-600">Rechazados</div>
                <p className="text-2xl font-bold text-gray-900">{rechazados || 0}</p>
              </div>
              <div className="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
                <Shield className="w-5 h-5" />
              </div>
            </div>
          </div>
        </div>

        {/* Filtros */}
        <div className="rounded-lg border border-gray-200 shadow-sm mb-6">
          <div className="p-4 bg-gray-50 border-b border-gray-200">
            <form method="GET" className="flex flex-wrap gap-2">
              <input
                type="text"
                name="buscar"
                placeholder="Paciente, CI, orden, análisis..."
                className="flex-1 px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
              <select name="estado" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos los estados</option>
                <option value="Validado">Validado</option>
                <option value="Pendiente">Pendiente</option>
                <option value="Rechazado">Rechazado</option>
              </select>
              <select name="paciente_id" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Todos los pacientes</option>
                {pacientes?.map((p) => (
                  <option key={p.id} value={p.id}>
                    {p.nombre_completo} ({p.ci})
                  </option>
                ))}
              </select>
              <input type="date" name="desde" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              <input type="date" name="hasta" className="px-3 py-2 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
              <button type="submit" className="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-md">
                Buscar
              </button>
              <a href={{ route: 'resultados.index' }} className="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-md">
                Limpiar
              </a>
            </form>
          </div>
        </div>

        {/* Tabla de resultados */}
        <div className="rounded-lg border border-gray-200 shadow-sm">
          <div className="flex items-center justify-between p-4 bg-white rounded-t-lg border-b border-gray-200">
            <h3 className="text-lg font-semibold text-gray-800">
              Resultados ({resultados?.total || 0})
            </h3>
            <div className="flex gap-2">
              <button className="btn btn-sm btn-success" onClick={() => window.print()}>
                <i className="bi bi-printer mr-2"></i> Imprimir
              </button>
            </div>
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm text-left text-gray-700">
              <thead className="bg-gray-50 text-xs text-gray-700">
                <tr>
                  <th scope="col" className="px-6 py-3 font-medium">ID</th>
                  <th scope="col" className="px-6 py-3 font-medium">Fecha</th>
                  <th scope="col" className="px-6 py-3 font-medium">Paciente</th>
                  <th scope="col" className="px-6 py-3 font-medium">Análisis</th>
                  <th scope="col" className="px-6 py-3 font-medium">Resultado</th>
                  <th scope="col" className="px-6 py-3 font-medium">Referencia</th>
                  <th scope="col" className="px-6 py-3 font-medium">Estado</th>
                  <th scope="col" className="px-6 py-3 font-medium text-right">Acción</th>
                </tr>
              </thead>
              <tbody className="text-sm divide-y divide-gray-200">
                {resultados?.data?.map((r) => (
                  <tr key={r.id} className="hover:bg-gray-50">
                    <td className="px-6 py-4 font-medium text-gray-900">{r.id}</td>
                    <td className="px-6 py-4">{new Date(r.created_at).toLocaleDateString('es-ES')}</td>
                    <td className="px-6 py-4">
                      <div className="text-gray-900">{r.muestra?.orden?.paciente?.nombre_completo}</div>
                      <div className="text-xs text-gray-500">CI: {r.muestra?.orden?.paciente?.ci}</div>
                    </td>
                    <td className="px-6 py-4">{muestra?.tipoAnalisis?.nombre}</td>
                    <td className="px-6 py-4">{r.resultado}</td>
                    <td className="px-6 py-4 text-gray-600">{r.valores_referencia}</td>
                    <td className="px-6 py-4">
                      <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${ordEstadoColor(r.estado)}`}>
                        {r.estado}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-right">
                      <button
                        onClick={() => window.print()}
                        className="text-blue-600 hover:text-blue-800 text-sm font-medium"
                      >
                        Ver PDF
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}

const ordEstadoColor = (estado) => {
  const colors = {
    Validado: 'bg-green-100 text-green-700',
    Pendiente: 'bg-yellow-100 text-yellow-700',
    Rechazado: 'bg-red-100 text-red-700',
  };
  return colors[estado] || 'bg-gray-100 text-gray-700';
};
