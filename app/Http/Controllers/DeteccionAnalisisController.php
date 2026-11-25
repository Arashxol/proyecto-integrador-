<?php

namespace App\Http\Controllers;

use App\Models\Resultado;
use App\Models\Paciente;
use App\Models\Muestra;
use App\Models\TipoAnalisis;
use App\Models\OrdenAnalisis;
use App\Models\AuditoriaLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Inertia;
use Illuminate\Support\Carbon;

class DeteccionAnalisisController extends Controller
{
    /**
     * Mostrar la página principal de detección de anomalías
     */
    public function index()
    {
        $usuario = auth()->user();

        if (!in_array($usuario->rol, ['Administrador', 'Bioquimico'])) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        // Obtener tipos de análisis para el formulario
        $examenes = TipoAnalisis::where('activo', true)->get();

        // Obtener resultados válidos para el selector
        $resultados = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis'])
            ->where('estado', 'Validado')
            ->latest('fecha_validacion')
            ->limit(50)
            ->get();

        // Obtener resultados válidos para estadísticas simples
        $totalValidados = Resultado::where('estado', 'Validado')->count();
        $totalAnalisis = TipoAnalisis::where('activo', true)->count();

        return view('deteccion.index', compact(
            'usuario', 'examenes', 'totalValidados', 'totalAnalisis', 'resultados'
        ));
    }

    /**
     * Evaluar si un resultado es anómalo comparado con datos históricos
     */
    public function evaluar(Request $request)
    {
        $usuario = auth()->user();

        if (!in_array($usuario->rol, ['Administrador', 'Bioquimico'])) {
            abort(403, 'No tiene permiso para acceder a esta sección.');
        }

        $request->validate([
            'resultado_id' => 'required|exists:resultados,id',
        ]);

        $resultado = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis', 'bioquimico'])
            ->findOrFail($request->resultado_id);

        $examen = $resultado->muestra->tipoAnalisis;
        $resultadoValor = (float) preg_replace('/[^0-9.]/', '', $resultado->resultado);
        $referencia = $resultado->valores_referencia;

        // Extraer valores numéricos de referencia
        preg_match_all('/[\d.]+/', $referencia, $nums);
        
        $esAnomala = false;
        $razon = '';
        $rmin = null;
        $rmax = null;

        if (count($nums[0]) >= 2) {
            $rmin = (float)$nums[0][0];
            $rmax = (float)$nums[0][1];

            // Un valor es anómalo si está fuera del rango [min, max]
            if ($resultadoValor < $rmin || $resultadoValor > $rmax) {
                $esAnomala = true;
                if ($resultadoValor < $rmin) {
                    $razon = "El resultado ({$resultadoValor}) está por debajo del rango normal ({$rmin} - {$rmax}).";
                } else {
                    $razon = "El resultado ({$resultadoValor}) está por encima del rango normal ({$rmin} - {$rmax}).";
                }
            }
        } else {
            $esAnomala = false;
            $razon = "No se pudo determinar rango de referencia numérico para este examen.";
        }

        // Obtener resultados similares para contexto
        $examenId = $examen->id;
        $resultadoId = $resultado->id;
        $resultadosSimilares = Resultado::with(['muestra.orden.paciente', 'muestra.tipoAnalisis'])
            ->whereHas('muestra.tipoAnalisis', function($q) use ($examenId) {
                $q->where('id', $examenId);
            })
            ->where('estado', 'Validado')
            ->where('id', '!=', $resultadoId)
            ->latest('fecha_validacion')
            ->limit(10)
            ->get();

        // Guardar en auditoría
        \App\Models\AuditoriaLog::registrar(
            'Detección de anomalía en resultado',
            'deteccion_analisis',
            $resultado->id,
            "Examen: {$examen->nombre}, Resultado: {$resultadoValor}, Referencia: {$referencia}, Anómalo: " . ($esAnomala ? 'SÍ' : 'NO')
        );

        return view('deteccion.resultado', array_merge(compact(
            'usuario', 'examen', 'resultadoValor', 'referencia', 
            'esAnomala', 'razon', 'rmin', 'rmax', 'resultadosSimilares'
        ), [
            'resultadoActual' => $resultado
        ]));
    }
}