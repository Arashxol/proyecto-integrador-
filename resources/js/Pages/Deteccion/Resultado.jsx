import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';

export default function DeteccionResultado({
  usuario, examen, resultadoValor, referencia, esAnomala, razon, resultadosSimilares, resultadoActual
}) {
  const formatFecha = (f) => new Date(f).toLocaleString('es-ES');

  return (
    <AppLayout>
      <div className="space-y-6">
        <Link href="/deteccion-analisis"><Button variant="outline"><span className="mr-1">←</span> Volver al inicio</Button></Link>

        {esAnomala ? (
          <Card className="border-red-300">
            <CardHeader className="bg-red-50 border-red-200"><CardTitle className="text-red-700 flex items-center gap-2">
              <span className="text-xl">⚠</span> RESULTADO ANÓMALO DETECTADO
            </CardTitle></CardHeader>
            <CardContent className="space-y-4">
              <div className="grid sm:grid-cols-2 gap-4">
                <Info label="Análisis" value={examen?.nombre} />
                <Info label="Categoría" value={examen?.categoria} />
                <Info label="Resultado Obtenido" value={resultadoValor} cls="font-semibold" />
                <Info label="Rango de Referencia" value={referencia} />
              </div>
              <div className="bg-red-50 rounded-lg p-3 border border-red-100">
                <p className="font-semibold text-red-700 mb-1">¿Por qué es anómalo?</p>
                <p className="text-sm text-gray-700">{razon}</p>
                {rmin !== null && rmax !== null && (
                  <p className="text-sm text-gray-700 mt-1">Rango considerado: {rmin} - {rmax}</p>
                )}
              </div>
              <Link href="/deteccion-analisis"><Button className="bg-red-600 hover:bg-red-700">Evaluar Otro Resultado</Button></Link>
            </CardContent>
          </Card>
        ) : (
          <Card className="border-green-300">
            <CardHeader className="bg-green-50 border-green-200"><CardTitle className="text-green-700 flex items-center gap-2">
              <span className="text-xl">✓</span> Resultado Normal
            </CardTitle></CardHeader>
            <CardContent className="space-y-4">
              <div className="grid sm:grid-cols-2 gap-4">
                <Info label="Análisis" value={examen?.nombre} />
                <Info label="Categoría" value={examen?.categoria} />
                <Info label="Resultado Obtenido" value={resultadoValor} cls="font-semibold text-green-600" />
                <Info label="Rango de Referencia" value={referencia} />
              </div>
              <div className="bg-green-50 rounded-lg p-3 border border-green-100">
                <p className="text-sm text-gray-700">El resultado está dentro del rango de referencia esperado. No se detectaron anomalías.</p>
              </div>
              <Link href="/deteccion-analisis"><Button className="bg-green-600 hover:bg-green-700">Evaluar Otro Resultado</Button></Link>
            </CardContent>
          </Card>
        )}

        {resultadosSimilares && resultadosSimilares.length > 0 && (
          <Card>
            <CardHeader><CardTitle>Resultados Similares Recientes</CardTitle></CardHeader>
            <CardContent>
              <table className="w-full">
                <thead><tr className="border-b text-left text-sm text-gray-600">
                  <th className="py-2 px-3">Fecha</th><th className="py-2 px-3">Paciente</th><th className="py-2 px-3">Resultado</th><th className="py-2 px-3">Referencia</th>
                </tr></thead>
                <tbody className="text-sm">
                  {resultadosSimilares.map((r) => (
                    <tr key={r.id} className="border-b last:border-0">
                      <td className="py-2 px-3 text-gray-500">{formatFecha(r.fecha_validacion)}</td>
                      <td className="py-2 px-3">{r.muestra?.orden?.paciente?.nombre_completo}</td>
                      <td className="py-2 px-3">{r.resultado}</td>
                      <td className="py-2 px-3 text-gray-500">{r.valores_referencia}</td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </CardContent>
          </Card>
        )}
      </div>
    </AppLayout>
  );
}

function Info({ label, value, cls = '' }) {
  return (
    <div>
      <p className="text-xs font-medium text-gray-500 uppercase tracking-wide">{label}</p>
      <p className={`mt-1 font-medium ${cls}`}>{value || '—'}</p>
    </div>
  );
}
