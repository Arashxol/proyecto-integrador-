<?php

namespace App\Http\Controllers;

use App\Models\Resultado;
use App\Models\Paciente;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use App\Models\OrdenAnalisis;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnalisisHeuristicoController extends Controller
{
    /**
     * Mostrar la página de detección de anomalías
     */
    public function index()
    {
        $usuario = Auth::user();

        // Solo Administrador y Bioquímico pueden acceder
        if (!in_array($usuario->rol, ['Administrador', 'Bioquimico'])) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        // Obtener estadísticas de resultados validados
        $totalResultados = Resultado::where('estado', 'Validado')->count();
        $totalMuestras = Muestra::where('estado', 'Resultado Listo')->count();
        $totalPacientes = Paciente::count();
        $totalExamenes = TipoAnalisis::where('activo', true)->count();

        // Obtener resultados recientes para mostrar contexto
        $resultadosRecientes = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis', 'bioquimico'])
            ->where('estado', 'Validado')
            ->latest('fecha_validacion')
            ->limit(10)
            ->get();

        // Verificar si hay datos suficientes para ML
        $suficientesDatos = $totalResultados >= 5;

        return view('busqueda_heuristica.index', compact(
            'usuario', 'totalResultados', 'totalMuestras', 'totalPacientes',
            'totalExamenes', 'resultadosRecientes', 'suficientesDatos'
        ));
    }

    /**
     * Ejecutar la detección de anomalías mediante el script Python
     */
    public function ejecutar(Request $request)
    {
        $usuario = Auth::user();

        if (!in_array($usuario->rol, ['Administrador', 'Bioquimico'])) {
            return response()->json(['error' => 'No tiene permiso'], 403);
        }

        $rutaScript = base_path('python_busqueda/deteccion_anomalias_ml.py');
        
        if (!file_exists($rutaScript)) {
            return response()->json(['error' => 'Script de Python no encontrado'], 500);
        }

        // Ejecutar el script de Python
        $comando = "python \"" . $rutaScript . "\" 2>&1";
        $salida = [];
        $codigoRetorno = 0;
        
        exec($comando, $salida, $codigoRetorno);

        $salidaTexto = implode("\n", $salida);

        // Registrar en auditoría
        AuditoriaLog::registrar(
            'Ejecución detección de anomalías (ML)',
            'analisis_heuristico',
            null,
            "Ejecutado por: {$usuario->name} ({$usuario->email}) - Resultado: " . ($codigoRetorno === 0 ? 'Éxito' : 'Error')
        );

        if ($codigoRetorno === 0) {
            return response()->json([
                'exito' => true,
                'mensaje' => 'Detección de anomalías completada exitosamente',
                'salida' => $salidaTexto
            ]);
        } else {
            return response()->json([
                'exito' => false,
                'mensaje' => 'Error al ejecutar la detección',
                'salida' => $salidaTexto
            ], 500);
        }
    }

    /**
     * Mostrar resultados de la última detección (si se guarda en BD)
     * Por ahora muestra los datos que el modelo analizaría
     */
    public function resultados()
    {
        $usuario = Auth::user();

        if (!in_array($usuario->rol, ['Administrador', 'Bioquimico'])) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        // Obtener todos los resultados validados para mostrar qué se analizaría
        $resultados = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis', 'bioquimico'])
            ->where('estado', 'Validado')
            ->latest('fecha_validacion')
            ->paginate(20);

        return view('busqueda_heuristica.resultados', compact('resultados', 'usuario'));
    }
}