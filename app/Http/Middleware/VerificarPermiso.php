<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\BitacoraHelper;

class VerificarPermiso
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permiso - Slug del permiso requerido (ej: 'usuarios.ver')
     */
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        $user = auth()->user();

        // Si no está autenticado, redirigir a login
        if (!$user) {
            return redirect('/login');
        }

        // Cargar el rol y permisos del usuario
        $user->load('rol.permisos');

        // Verificar si el usuario tiene el permiso
        $tienePermiso = $user->rol
            && $user->rol->permisos->contains('slug', $permiso);

        if (!$tienePermiso) {
            // Registrar acceso denegado en bitácora
            BitacoraHelper::accesoDenegado(
                $user->id,
                $request->ip(),
                "Intento de acceso a: {$request->path()} (requiere: {$permiso})"
            );

            // Redirigir con mensaje de error
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
