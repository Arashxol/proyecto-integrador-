import { Inertia } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { Search, Activity, Shield } from 'lucide-react';
import { useState } from 'react';

export default function DeteccionIndex({ usuario, examenes, totalValidados, totalAnalisis, resultados }) {
  const [seleccionado, setSeleccionado] = useState('');

  const submit = (e) => {
    e.preventDefault();
    if (seleccionado) Inertia.post('/deteccion-analisis/evaluar', { resultado_id: seleccionado });
  };

  const estiloEstado = (s) => ({
    'Sangre': 'bg-blue-100 text-blue-800',
    'Orina': 'bg-purple-100 text-purple-800',
    'Heces': 'bg-green-100 text-green-800',
  }[s] || 'bg-gray-100 text-gray-700');

  return (
    <AppLayout>
      <div className="space-y-6">
        <div className="flex items-center gap-3">
          <Activity size={24} className="text-blue-600" />
          <h1 className="text-2xl font-bold text-gray-900">Detección de Anomalías</h1>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
          <Stat icon={Activity} color="bg-blue-100 text-blue-800" label="Resultados Validados" value={totalValidados || 0} />
          <Stat icon={Shield} color="bg-indigo-100 text-indigo-800" label="Tipos de Análisis" value={totalAnalisis || 0} />
          <Stat icon={Activity} color="bg-green-100 text-green-800" label="Resultados listos" value={(resultados || []).length} />
        </div>

        <Card>
          <CardHeader><CardTitle>Seleccionar resultado a analizar</CardTitle></CardHeader>
          <CardContent>
            <form onSubmit={submit} className="space-y-4">
              <div>
                <label className="label-form">Resultado de la BD</label>
                <select
                  value={seleccionado}
                  onChange={(e) => setSeleccionado(e.target.value)}
                  className="input-base mt-1"
                >
                  <option value="">-- Seleccionar resultado --</option>
                  {(resultados || []).map((r) => (
                    <option key={r.id} value={r.id}>
                      {r.muestra?.orden?.codigo_orden} · {r.muestra?.tipoAnalisis?.nombre} · {r.muestra?.orden?.paciente?.nombre_completo}
                    </option>
                  ))}
                </select>
              </div>

              {seleccionado && (
                <div className="flex justify-end">
                  <button type="submit" className="btn-primary">
                    <Search size={16} className="inline mr-2" /> Evaluar resultado
                  </button>
                </div>
              )}
            </form>
          </CardContent>
        </Card>

        <Card>
          <CardContent className="p-4">
            <ul className="list-disc list-inside text-sm text-gray-600 space-y-2">
              <li>El sistema compara el valor ingresado contra el rango de referencia del examen seleccionado.</li>
              <li>Se marca como <strong>anómalo</strong> si el valor está fuera del rango [mínimo, máximo].</li>
              <li>Los datos históricos provienen de resultados previamente validados en el laboratorio.</li>
            </ul>
          </CardContent>
        </Card>
      </div>
    </AppLayout>
  );
}

function Stat({ icon: Icon, color, label, value }) {
  return (
    <Card>
      <CardContent className="flex items-center justify-between p-4">
        <div>
          <p className="text-sm font-medium text-gray-600">{label}</p>
          <p className="text-2xl font-bold text-gray-900">{value}</p>
        </div>
        <div className={`w-10 h-10 rounded-lg flex items-center justify-center ${color}`}><Icon size={20} /></div>
      </CardContent>
    </Card>
  );
}
