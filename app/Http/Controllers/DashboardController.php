<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\OrdenAnalisis;
use App\Models\TipoAnalisis;
use App\Models\Muestra;
use App\Models\Pago;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller {
    public function index() {
        $user = Auth::user();

        // 1. Lógica para Administrador (vista completa)
        if ($user->rol == 'Administrador') {
            $stats = [
                'pacientes' => Paciente::count(),
                'ordenesPendientes' => OrdenAnalisis::where('estado', 'Pendiente')->count(),
                'muestrasEnProceso' => Muestra::where('estado', 'En Analisis')->count(),
                'ingresos' => Pago::where('estado', 'Pagado')->sum('monto'),
                'ordenesCompletadas' => OrdenAnalisis::where('estado', 'Completada')->count(),
            ];
            $usuarios = \App\Models\User::select('rol', \Illuminate\Support\Facades\DB::raw('count(*) as total'))->groupBy('rol')->get();
            $ordenes = OrdenAnalisis::with('paciente')->latest()->take(5)->get();
            $logs = AuditoriaLog::with('user')->latest()->take(5)->get();
            $examenesDisponibles = TipoAnalisis::where('activo', true)->get();
            $queryBusqueda = request('buscar_paciente');
            $pacienteBuscado = null;
            $historialOrdenes = collect();
            if ($queryBusqueda) {
                $pacienteBuscado = Paciente::where('ci', $queryBusqueda)->orWhere('nombre_completo', 'LIKE', "%{$queryBusqueda}%")->first();
                if ($pacienteBuscado) {
                    $historialOrdenes = OrdenAnalisis::with(['muestras.tipoAnalisis', 'muestras.resultado', 'pago'])->where('paciente_id', $pacienteBuscado->id)->latest()->get();
                }
            }
            return view('dashboards.admin', compact('stats', 'usuarios', 'ordenes', 'logs', 'examenesDisponibles', 'queryBusqueda', 'pacienteBuscado', 'historialOrdenes'));
        }

        // 1b. Lógica para Recepción
        if ($user->rol == 'Recepcionista') {
            $pacientes = Paciente::count();
            $ordenesPendientes = OrdenAnalisis::where('estado', 'Pendiente')->count();
            $muestrasEnProceso = Muestra::where('estado', 'En Analisis')->count();
            $ordenes = OrdenAnalisis::with('paciente')->orderBy('id', 'desc')->take(10)->get();
            $examenesDisponibles = TipoAnalisis::where('activo', true)->get();

            // --- NUEVA LÓGICA DE BÚSQUEDA DE HISTORIAL ---
            $queryBusqueda = request('buscar_paciente');
            $pacienteBuscado = null;
            $historialOrdenes = collect();

            if ($queryBusqueda) {
                // Buscamos por CI exacto o por coincidencia de nombre
                $pacienteBuscado = Paciente::where('ci', $queryBusqueda)
                                    ->orWhere('nombre_completo', 'LIKE', "%{$queryBusqueda}%")
                                    ->first();
                
                if ($pacienteBuscado) {
                    $historialOrdenes = OrdenAnalisis::with(['muestras.tipoAnalisis', 'muestras.resultado'])
                        ->where('paciente_id', $pacienteBuscado->id)
                        ->orderBy('id', 'desc')
                        ->get();
                }
            }

            return view('dashboards.recepcion', compact(
                'pacientes', 'ordenesPendientes', 'muestrasEnProceso', 'ordenes', 
                'examenesDisponibles', 'queryBusqueda', 'pacienteBuscado', 'historialOrdenes'
            ));
        }

        // 2. Lógica para el Bioquímico
        if ($user->rol == 'Bioquimico') {
            // Traemos las muestras que están esperando análisis, junto con los datos del paciente y el examen
            $muestrasPendientes = Muestra::with(['orden.paciente', 'tipoAnalisis', 'resultado'])
                ->whereIn('estado', ['Recolectado', 'En Analisis'])
                ->orderBy('fecha_recoleccion', 'asc')
                ->get();
                
            return view('dashboards.bioquimico', compact('muestrasPendientes'));
            
        }
        
        // 3. Lógica para el Paciente
        if ($user->rol == 'Paciente') {
            $pacienteData = Paciente::where('email', $user->email)->first();
            $ordenes = collect();
            $citas = collect();
            $statsPaciente = ['tomadas'=>0,'enProceso'=>0,'validadas'=>0,'total'=>0];
            if ($pacienteData) {
                $ordenes = OrdenAnalisis::with(['muestras.tipoAnalisis', 'muestras.resultado', 'pago'])
                    ->where('paciente_id', $pacienteData->id)
                    ->orderBy('id', 'desc')
                    ->get();
                // Estadísticas por estado de muestra
                $todasMuestras = $ordenes->flatMap->muestras;
                $statsPaciente['total'] = $todasMuestras->count();
                $statsPaciente['tomadas'] = $todasMuestras->where('estado','Recolectado')->count();
                $statsPaciente['enProceso'] = $todasMuestras->whereIn('estado',['En Transito','En Analisis'])->count();
                $statsPaciente['validadas'] = $todasMuestras->filter(fn($m)=> $m->resultado && $m->resultado->estado==='Validado')->count();
                $citas = \App\Models\Cita::where('paciente_id', $pacienteData->id)->orderBy('fecha','desc')->orderBy('hora','desc')->take(5)->get();
            }
            return view('dashboards.paciente', compact('pacienteData', 'ordenes', 'statsPaciente', 'citas'));
        }

        // Por seguridad, si el rol no coincide, cerramos sesión
        Auth::logout();
        return redirect('/login');
    }
}