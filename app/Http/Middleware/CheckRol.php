<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRol
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        // 1. Separamos los roles en caso de que lleguen pegados por una coma
        $rolesPermitidos = [];
        foreach ($roles as $rol) {
            $rolesPermitidos = array_merge($rolesPermitidos, explode(',', $rol));
        }

        // 2. Verificamos si el usuario está logueado y si su rol está en la lista de permitidos
        if (!Auth::check() || !in_array(Auth::user()->rol, $rolesPermitidos)) {
            
            // Mensaje de error personalizado para saber exactamente qué falló
            abort(403, 'Acceso Denegado. Tu rol actual es: ' . (Auth::check() ? Auth::user()->rol : 'Invitado') . '. Necesitas ser: ' . implode(' o ', $rolesPermitidos));
        }

        return $next($request);
    }
}