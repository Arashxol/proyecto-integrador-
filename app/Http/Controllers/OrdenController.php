<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\OrdenAnalisis;
use App\Models\TipoAnalisis;
use App\Models\Muestra;
use App\Models\Resultado;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OrdenController extends Controller {
    public function registrar(Request $request) {
        $validacion = Validaciones::validarPacienteCompleto($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $p = $validacion['datos'];

        $ordenVal = Validaciones::validarOrden($request->all());
        if(!$ordenVal['valido']) {
            return redirect()->back()->withErrors($ordenVal['errores'])->withInput();
        }

        $pacienteExistente = Paciente::where('ci', $p['ci'])->first();
        if($pacienteExistente) {
            if($pacienteExistente->nombre_completo !== $p['nombre_completo'] || $pacienteExistente->email !== $p['email']) {
                AuditoriaLog::registrar('Intento registro CI duplicado', 'pacientes', $pacienteExistente->id, "CI: {$p['ci']} - Se reutilizó paciente existente");
            }
            $paciente = $pacienteExistente;
            $paciente->update([
                'telefono' => $p['telefono'] ?? $paciente->telefono,
                'direccion' => $p['direccion'] ?? $paciente->direccion,
            ]);
            $ciDuplicado = true;
        } else {
            if(Paciente::where('email', $p['email'])->whereNotNull('email')->exists() && $p['email']) {
                return redirect()->back()->withErrors(['email' => 'Este email ya está registrado con otro CI.'])->withInput();
            }
            $paciente = Paciente::create(array_merge($p, ['fecha_registro' => Carbon::now()->toDateString()]));
        }

        $totalCosto = TipoAnalisis::whereIn('id', $ordenVal['datos']['examenes'])->sum('precio');
        $codigoOrden = 'DIA-' . Carbon::now()->year . '-' . strtoupper(Str::random(4));

        if(!isset($ciDuplicado)) {
            AuditoriaLog::registrar('Registro paciente', 'pacientes', $paciente->id, "CI: {$paciente->ci}");
        }

        $orden = OrdenAnalisis::create([
            'codigo_orden' => $codigoOrden,
            'paciente_id' => $paciente->id,
            'medico_solicitante' => $ordenVal['datos']['medico_solicitante'],
            'diagnostico_presuntivo' => $ordenVal['datos']['diagnostico_presuntivo'],
            'fecha_solicitud' => Carbon::now()->toDateString(),
            'fecha_entrega_estimada' => Carbon::now()->addDays(1)->toDateString(),
            'total' => $totalCosto,
            'estado' => 'Pendiente'
        ]);

        foreach ($ordenVal['datos']['examenes'] as $id) {
            $examen = TipoAnalisis::find($id);
            if(!$examen) continue;

            $tipoM = 'Sangre';
            if (Str::contains(Str::lower($examen->nombre), ['orina', 'urologia'])) $tipoM = 'Orina';
            if (Str::contains(Str::lower($examen->nombre), ['heces', 'copro'])) $tipoM = 'Heces';

            $muestra = Muestra::create([
                'codigo_muestra' => 'MUE-' . strtoupper(Str::random(6)),
                'orden_id' => $orden->id,
                'tipo_analisis_id' => $examen->id,
                'tipo_muestra' => $tipoM,
                'estado' => 'Recolectado',
                'fecha_recoleccion' => Carbon::now(),
            ]);

            Resultado::create([
                'muestra_id' => $muestra->id,
                'resultado' => 'Pendiente de análisis',
                'valores_referencia' => $examen->valores_referencia,
                'estado' => 'Pendiente'
            ]);
        }

        AuditoriaLog::registrar('Creación orden', 'ordenes_analisis', $orden->id, "Código: {$codigoOrden} CI: {$paciente->ci}");

        if (isset($ciDuplicado)) {
            return redirect()->back()->with('success', "¡Orden {$codigoOrden} registrada! Nota: CI {$paciente->ci} ya existía, se reutilizó el paciente {$paciente->nombre_completo}.")->with('ci_duplicado', "¡Atención! El CI {$paciente->ci} ya estaba registrado a nombre de {$paciente->nombre_completo}. No se creó un paciente duplicado, se asoció la nueva orden al paciente existente.");
        }
        return redirect()->back()->with('success', "¡Orden {$codigoOrden} registrada con éxito para el paciente!");
    }

    public function checkCi($ci) {
        $paciente = Paciente::where('ci', $ci)->first();
        if ($paciente) {
            return response()->json(['existe' => true, 'paciente' => $paciente]);
        }
        return response()->json(['existe' => false]);
    }

    public function pendientes() {
        $ordenesPendientes = \App\Models\OrdenAnalisis::with(['paciente', 'muestras.tipoAnalisis', 'muestras.resultado'])
            ->whereIn('estado', ['Pendiente', 'En Proceso'])
            ->orderBy('fecha_solicitud', 'asc')
            ->get();
        return view('ordenes.pendientes', compact('ordenesPendientes'));
    }
}
