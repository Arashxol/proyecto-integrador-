<?php

namespace App\Http\Controllers;

use App\Models\Muestra;
use App\Models\OrdenAnalisis;
use App\Models\TipoAnalisis;
use App\Models\AuditoriaLog;
use App\Libraries\Validaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MuestraController extends Controller
{
    public function index(Request $request)
    {
        $query = Muestra::with(['orden.paciente', 'tipoAnalisis', 'resultado']);

        if ($request->estado) {
            $query->where('estado', $request->estado);
        }
        if ($request->buscar) {
            $q = $request->buscar;
            $query->where(function($w) use ($q) {
                $w->where('codigo_muestra', 'LIKE', "%$q%")
                  ->orWhereHas('orden', fn($o) => $o->where('codigo_orden', 'LIKE', "%$q%"))
                  ->orWhereHas('orden.paciente', fn($p) => $p->where('ci', 'LIKE', "%$q%")->orWhere('nombre_completo', 'LIKE', "%$q%"));
            });
        }

        $muestras = $query->orderBy('fecha_recoleccion', 'desc')->paginate(15)->withQueryString();
        $estados = ['Recolectado','En Transito','En Analisis','Resultado Listo','Archivada'];
        return view('muestras.index', compact('muestras','estados'));
    }

    public function show($id)
    {
        $muestra = Muestra::with(['orden.paciente','tipoAnalisis','resultado.bioquimico','tecnico'])->findOrFail($id);
        return view('muestras.show', compact('muestra'));
    }

    public function actualizarEstado(Request $request, $id)
    {
        $request->validate(['estado' => 'required|in:Recolectado,En Transito,En Analisis,Resultado Listo,Archivada']);
        $muestra = Muestra::findOrFail($id);
        $anterior = $muestra->estado;
        $muestra->estado = $request->estado;
        if ($request->estado == 'En Analisis') {
            $muestra->tecnico_asignado_id = Auth::id();
            $muestra->fecha_analisis = Carbon::now();
        }
        $muestra->save();
        AuditoriaLog::registrar("Cambio estado muestra {$muestra->codigo_muestra}: $anterior -> {$request->estado}", 'muestras', $muestra->id);
        return redirect()->back()->with('success', "Muestra {$muestra->codigo_muestra} cambió de $anterior a {$request->estado}");
    }
}
