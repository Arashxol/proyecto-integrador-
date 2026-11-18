<?php

namespace App\Http\Controllers;

use App\Models\OrdenAnalisis;
use App\Models\Paciente;
use App\Models\Muestra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller {
    
    // Reporte gerencial con filtros por fecha y exportación
    public function index(Request $request) {
        $desde = $request->input('desde');
        $hasta = $request->input('hasta');
        $estado = $request->input('estado');

        $queryOrdenes = OrdenAnalisis::query();
        $queryMuestras = Muestra::query();

        if ($desde) {
            $queryOrdenes->whereDate('fecha_solicitud', '>=', $desde);
            $queryMuestras->whereDate('fecha_recoleccion', '>=', $desde);
        }
        if ($hasta) {
            $queryOrdenes->whereDate('fecha_solicitud', '<=', $hasta);
            $queryMuestras->whereDate('fecha_recoleccion', '<=', $hasta);
        }
        if ($estado) {
            $queryOrdenes->where('estado', $estado);
        }

        $totalIngresos = (clone $queryOrdenes)->where('estado', 'Completada')->sum('total');
        $totalOrdenes = (clone $queryOrdenes)->count();
        $pacientesAtendidos = Paciente::when($desde, fn($q) => $q->whereDate('fecha_registro', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha_registro', '<=', $hasta))->count();
        $analisisRealizados = (clone $queryMuestras)->where('estado', 'Resultado Listo')->count();

        // Análisis más solicitados con filtros
        $analisisPopulares = DB::table('muestras')
            ->join('tipos_analisis', 'muestras.tipo_analisis_id', '=', 'tipos_analisis.id')
            ->join('ordenes_analisis', 'muestras.orden_id', '=', 'ordenes_analisis.id')
            ->when($desde, fn($q) => $q->whereDate('muestras.fecha_recoleccion', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('muestras.fecha_recoleccion', '<=', $hasta))
            ->select('tipos_analisis.nombre', 'tipos_analisis.categoria', DB::raw('count(*) as cantidad'))
            ->groupBy('tipos_analisis.nombre', 'tipos_analisis.categoria')
            ->orderBy('cantidad', 'desc')
            ->get();

        $ordenesPorEstado = (clone $queryOrdenes)->select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total','estado');
        $muestrasPorEstado = (clone $queryMuestras)->select('estado', DB::raw('count(*) as total'))->groupBy('estado')->pluck('total','estado');
        $ingresosPorMes = OrdenAnalisis::select(DB::raw("DATE_FORMAT(fecha_solicitud, '%Y-%m') as mes"), DB::raw('SUM(total) as ingresos'))
            ->where('estado','Completada')
            ->when($desde, fn($q) => $q->whereDate('fecha_solicitud', '>=', $desde))
            ->when($hasta, fn($q) => $q->whereDate('fecha_solicitud', '<=', $hasta))
            ->groupBy('mes')->orderBy('mes')->get();

        // Si pide exportación
        if ($request->input('export') === 'csv') {
            $filename = 'reporte_dialab_'.date('Y-m-d_His').'.csv';
            $headers = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"$filename\""];
            $callback = function() use ($analisisPopulares, $ordenesPorEstado, $muestrasPorEstado, $totalIngresos, $pacientesAtendidos, $analisisRealizados) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['REPORTE DIALAB - '.date('Y-m-d H:i:s')]);
                fputcsv($file, ['Indicador','Valor']);
                fputcsv($file, ['Ingresos Confirmados', $totalIngresos]);
                fputcsv($file, ['Pacientes Atendidos', $pacientesAtendidos]);
                fputcsv($file, ['Muestras Analizadas', $analisisRealizados]);
                fputcsv($file, []);
                fputcsv($file, ['Analisis','Categoria','Cantidad']);
                foreach ($analisisPopulares as $a) fputcsv($file, [$a->nombre, $a->categoria, $a->cantidad]);
                fputcsv($file, []);
                fputcsv($file, ['Ordenes por estado']);
                foreach ($ordenesPorEstado as $k=>$v) fputcsv($file, [$k, $v]);
                fclose($file);
            };
            \App\Models\AuditoriaLog::registrar('Exportación reporte CSV', 'reportes', null, "desde:$desde hasta:$hasta");
            return response()->stream($callback, 200, $headers);
        }

        if ($request->filled('desde') || $request->filled('hasta') || $request->filled('estado')) {
            \App\Models\AuditoriaLog::registrar('Consulta reporte con filtros', 'reportes', null, json_encode($request->only(['desde','hasta','estado'])));
        }

        return view('reportes.index', compact('totalIngresos', 'totalOrdenes', 'pacientesAtendidos', 'analisisRealizados', 'analisisPopulares', 'ordenesPorEstado', 'muestrasPorEstado', 'ingresosPorMes', 'desde', 'hasta', 'estado'));
    }
}