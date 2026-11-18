<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\OrdenAnalisis;
use App\Models\TipoAnalisis;
use App\Models\Muestra;
use App\Models\Resultado;
use App\Models\Pago;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PacienteController extends Controller
{
    public function create()
    {
        $categorias = TipoAnalisis::where('activo', true)
            ->get()
            ->groupBy('categoria');
        return view('pacientes.crear', compact('categorias'));
    }

    public function store(Request $request)
    {
        $validacion = Validaciones::validarPacienteCompleto($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $p = $validacion['datos'];

        $ordenVal = Validaciones::validarOrden($request->all());
        if(!$ordenVal['valido']) {
            return redirect()->back()->withErrors($ordenVal['errores'])->withInput();
        }

        $examenesIds = $ordenVal['datos']['examenes'];
        $total = TipoAnalisis::whereIn('id', $examenesIds)->sum('precio');

        $paciente = Paciente::where('ci', $p['ci'])->first();
        $esNuevo = false;

        if ($paciente) {
            $paciente->update([
                'nombre_completo' => $p['nombre_completo'],
                'edad' => $p['edad'],
                'sexo' => $p['sexo'],
                'telefono' => $p['telefono'] ?? $paciente->telefono,
                'direccion' => $p['direccion'] ?? $paciente->direccion,
                'email' => $p['email'] ?? $paciente->email,
            ]);
        } else {
            $paciente = Paciente::create(array_merge($p, ['fecha_registro' => Carbon::now()->toDateString()]));
            $esNuevo = true;
        }

        $orden = OrdenAnalisis::create([
            'codigo_orden' => 'DIA-' . Carbon::now()->year . '-' . strtoupper(Str::random(4)),
            'paciente_id' => $paciente->id,
            'medico_solicitante' => 'Particular',
            'fecha_solicitud' => Carbon::now()->toDateString(),
            'fecha_entrega_estimada' => Carbon::now()->addDays(1)->toDateString(),
            'total' => $total,
            'estado' => 'Pendiente',
        ]);

        Pago::create([
            'orden_id' => $orden->id,
            'monto' => $total,
            'estado' => 'Pendiente',
        ]);

        foreach ($examenesIds as $id) {
            $examen = TipoAnalisis::find($id);
            if (!$examen) continue;

            $tipoM = 'Sangre';
            if (Str::contains(Str::lower($examen->nombre), ['orina'])) $tipoM = 'Orina';
            if (Str::contains(Str::lower($examen->nombre), ['copro', 'heces'])) $tipoM = 'Heces';

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
                'estado' => 'Pendiente',
            ]);
        }

        AuditoriaLog::registrar(
            ($esNuevo ? 'Registro paciente + orden' : 'Nueva orden paciente existente'),
            'pacientes',
            $paciente->id,
            "Orden: {$orden->codigo_orden} | Total: Bs.{$total}"
        );

        return redirect()->route('pacientes.crear')->with('success',
            "Paciente registrado. Orden {$orden->codigo_orden} creada con " . count($examenesIds) . " análisis. Total: Bs. " . number_format($total, 2));
    }
}
