<?php

namespace App\Http\Controllers;

use App\Models\Resultado;
use App\Models\Paciente;
use App\Libraries\PDFGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ResultadosController extends Controller
{
    public function index(Request $request)
    {
        $query = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis', 'bioquimico']);

        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $query->where(function($q) use ($b) {
                $q->whereHas('muestra.orden.paciente', function($p) use ($b) {
                    $p->where('nombre_completo', 'like', "%{$b}%")
                      ->orWhere('ci', 'like', "%{$b}%");
                })
                ->orWhereHas('muestra.orden', function($o) use ($b) {
                    $o->where('codigo_orden', 'like', "%{$b}%");
                })
                ->orWhereHas('muestra.tipoAnalisis', function($t) use ($b) {
                    $t->where('nombre', 'like', "%{$b}%");
                });
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('paciente_id')) {
            $query->whereHas('muestra.orden', function($o) use ($request) {
                $o->where('paciente_id', $request->paciente_id);
            });
        }

        if ($request->filled('desde')) {
            $query->whereDate('created_at', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        $resultados = $query->latest()->paginate(20)->withQueryString();
        $total = Resultado::count();
        $validados = Resultado::where('estado', 'Validado')->count();
        $pendientes = Resultado::where('estado', 'Pendiente')->count();
        $rechazados = Resultado::where('estado', 'Rechazado')->count();
        $pacientes = Paciente::orderBy('nombre_completo')->get(['id', 'nombre_completo', 'ci']);

        return view('resultados.index', compact('resultados', 'total', 'validados', 'pendientes', 'rechazados', 'pacientes'));
    }

    public function paciente($id)
    {
        $paciente = Paciente::findOrFail($id);

        $resultados = Resultado::with(['muestra.orden', 'muestra.tipoAnalisis', 'bioquimico'])
            ->whereHas('muestra.orden', function($q) use ($id) {
                $q->where('paciente_id', $id);
            })
            ->get()
            ->filter(function($r) {
                return $r->muestra && $r->muestra->orden && $r->muestra->tipoAnalisis;
            });

        // Agrupar por fecha de solicitud + hora de recolección
        $grupos = $resultados->groupBy(function($r) {
            $fecha = $r->muestra->orden->fecha_solicitud ?? 'sin-fecha';
            $hora = $r->muestra->fecha_recoleccion
                ? Carbon::parse($r->muestra->fecha_recoleccion)->format('H:i')
                : 'sin-hora';
            return $fecha . '|' . $hora;
        })->sortBy(function($grupo, $key) {
            return $key;
        });

        return view('resultados.paciente', compact('paciente', 'resultados', 'grupos'));
    }

    public function exportar($id)
    {
        $paciente = Paciente::findOrFail($id);

        $resultados = Resultado::with(['muestra.orden', 'muestra.tipoAnalisis', 'bioquimico'])
            ->whereHas('muestra.orden', function($q) use ($id) {
                $q->where('paciente_id', $id);
            })
            ->get()
            ->filter(function($r) {
                return $r->muestra && $r->muestra->orden && $r->muestra->tipoAnalisis;
            });

        $grupos = $resultados->groupBy(function($r) {
            $fecha = $r->muestra->orden->fecha_solicitud ?? 'sin-fecha';
            $hora = $r->muestra->fecha_recoleccion
                ? Carbon::parse($r->muestra->fecha_recoleccion)->format('H:i')
                : 'sin-hora';
            return $fecha . '|' . $hora;
        })->sortBy(function($grupo, $key) {
            return $key;
        });

        // Generar PDF profesional tipo factura
        $pdf = new PDFGenerator();

        // Cabecera tipo factura
        $pdf->headerFactura(
            'RESULTADOS DE LABORATORIO',
            'INFORME CLINICO',
            'RES-' . str_pad($paciente->id, 5, '0', STR_PAD_LEFT) . '-' . date('Ymd'),
            now()->format('d/m/Y H:i')
        );

        // Informacion del paciente
        $pdf->infoPaciente($paciente);

        // Tabla de resultados agrupados
        $pdf->tablaResultados($grupos);

        // Firma
        $pdf->firma();

        $filename = 'resultados_' . $paciente->ci . '_' . date('Ymd_His') . '.pdf';
        $pdf->output($filename, true);
    }
}
