<?php

namespace App\Http\Controllers;

use App\Models\OrdenAnalisis;
use App\Models\Resultado;
use App\Models\Paciente;
use App\Models\Muestra;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ResultadoController extends Controller {
    public function guardarResultadosOrden(Request $request, $orden_id) {
        $validacion = Validaciones::validarResultado($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $datos = $validacion['datos'];

        $orden = OrdenAnalisis::with('muestras.resultado')->findOrFail($orden_id);

        foreach ($request->resultados as $resultadoId => $datosR) {
            $resultado = Resultado::findOrFail($resultadoId);
            $muestra = $resultado->muestra;

            if ($muestra->orden_id != $orden->id) continue;

            $muestra->estado = 'Resultado Listo';
            $muestra->tecnico_asignado_id = Auth::id();
            $muestra->fecha_analisis = Carbon::now();
            $muestra->save();

            $resultado->update([
                'resultado' => $datosR['valor'] ?? '',
                'interpretacion' => $datosR['interpretacion'] ?? null,
                'bioquimico_validador_id' => Auth::id(),
                'fecha_validacion' => Carbon::now(),
                'estado' => 'Validado'
            ]);
        }

        $pendientes = $orden->muestras()->where('estado', '!=', 'Resultado Listo')->count();
        if ($pendientes == 0) {
            $orden->estado = 'Completada';
        } else {
            $orden->estado = 'En Proceso';
        }
        $orden->save();

        return redirect()->back()->with('success', "Resultados de la orden {$orden->codigo_orden} guardados y validados.");
    }

    public function guardarResultado(Request $request, $muestra_id) {
        $validacion = Validaciones::validarResultado($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $datos = $validacion['datos'];

        $muestra = Muestra::findOrFail($muestra_id);
        $muestra->estado = 'Resultado Listo';
        $muestra->tecnico_asignado_id = Auth::id();
        $muestra->fecha_analisis = Carbon::now();
        $muestra->save();

        $resultado = Resultado::where('muestra_id', $muestra_id)->first();
        $resultado->update([
            'resultado' => $datos['resultado'],
            'interpretacion' => $datos['interpretacion'],
            'bioquimico_validador_id' => Auth::id(),
            'fecha_validacion' => Carbon::now(),
            'estado' => 'Validado'
        ]);

        $orden = $muestra->orden;
        $muestrasPendientes = $orden->muestras()->where('estado', '!=', 'Resultado Listo')->count();
        if ($muestrasPendientes == 0) {
            $orden->estado = 'Completada';
            $orden->save();
        }

        return redirect()->back()->with('success', 'Resultado guardado y validado correctamente.');
    }

    public function imprimirResultado($muestra_id) {
        $muestra = Muestra::with(['orden.paciente', 'tipoAnalisis', 'resultado'])->findOrFail($muestra_id);

        if (Auth::user()->rol == 'Paciente') {
            $pacienteData = Paciente::where('email', Auth::user()->email)->first();
            if (!$pacienteData || $muestra->orden->paciente_id != $pacienteData->id) {
                abort(403, 'Privacidad Clínica: No tienes permiso para ver este resultado médico.');
            }
        }

        if ($muestra->resultado->estado != 'Validado') {
            return redirect()->back()->withErrors(['Aún no validado' => 'Este resultado médico aún está en proceso de validación por el Bioquímico.']);
        }

        return view('resultados.imprimir', compact('muestra'));
    }
}
