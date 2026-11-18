<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Paciente;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CitaController extends Controller
{
    public function misCitas()
    {
        $paciente = Paciente::where('email', Auth::user()->email)->first();
        $citas = collect();
        if ($paciente) {
            $citas = Cita::where('paciente_id', $paciente->id)->orderBy('fecha','desc')->orderBy('hora','desc')->get();
        }
        return view('paciente.citas', compact('paciente','citas'));
    }

    public function agendar(Request $request)
    {
        $validacion = Validaciones::validarCita($request->all());
        if(!$validacion['valido']) {
            return redirect()->back()->withErrors($validacion['errores'])->withInput();
        }
        $datos = $validacion['datos'];

        $paciente = Paciente::where('email', Auth::user()->email)->first();
        if (!$paciente) {
            return redirect()->back()->withErrors(['No se encontró ficha de paciente vinculada a tu correo. Contacta recepción.']);
        }

        $cita = Cita::create([
            'paciente_id' => $paciente->id,
            'user_id' => Auth::id(),
            'fecha' => $datos['fecha'],
            'hora' => $datos['hora'],
            'tipo' => $datos['tipo'],
            'motivo' => $datos['motivo'],
            'estado' => 'Pendiente',
        ]);

        AuditoriaLog::registrar('Paciente agendó cita', 'citas', $cita->id, $datos['tipo'].' '.$datos['fecha'].' '.$datos['hora']);
        return redirect()->back()->with('success', 'Cita agendada para '.$datos['fecha'].' a las '.$datos['hora'].'. Estado: Pendiente de confirmación.');
    }

    public function cancelar($id)
    {
        $cita = Cita::findOrFail($id);
        $paciente = Paciente::where('email', Auth::user()->email)->first();
        $esDueno = $paciente && $cita->paciente_id === $paciente->id;
        $esAdmin = in_array(Auth::user()->rol, ['Administrador','Recepcionista']);
        if (!$esDueno && !$esAdmin) abort(403);
        $cita->estado = 'Cancelada';
        $cita->save();
        AuditoriaLog::registrar('Cancelación cita', 'citas', $cita->id);
        return redirect()->back()->with('success', 'Cita cancelada.');
    }

    public function index(Request $request)
    {
        $q = Cita::with(['paciente','user'])->latest();
        if ($request->filled('fecha')) $q->whereDate('fecha', $request->fecha);
        if ($request->filled('estado')) $q->where('estado', $request->estado);
        if ($request->filled('buscar')) {
            $b = $request->buscar;
            $q->whereHas('paciente', fn($qq)=> $qq->where('nombre_completo','like',"%$b%")->orWhere('ci','like',"%$b%"));
        }
        $citas = $q->paginate(20)->withQueryString();
        return view('admin.citas', compact('citas'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|in:Pendiente,Confirmada,Cancelada,Completada']);
        $cita = Cita::findOrFail($id);
        $cita->estado = $request->estado;
        $cita->observaciones = $request->observaciones ?? $cita->observaciones;
        $cita->save();
        AuditoriaLog::registrar('Actualización estado cita: '.$request->estado, 'citas', $cita->id);
        return redirect()->back()->with('success', 'Cita actualizada a '.$request->estado);
    }
}
