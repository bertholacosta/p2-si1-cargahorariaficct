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
     * @param  string  ...$permisos - Slug(s) del permiso requerido (ej: 'usuarios.ver' o 'usuarios.ver,usuarios.editar')
     *                                 Si se pasan múltiples permisos, se evalúa con lógica OR (basta con tener uno)
     */
    public function handle(Request $request, Closure $next, string ...$permisos): Response
    {
        $user = auth()->user();

        // Si no está autenticado, redirigir a login
        if (!$user) {
            return redirect('/login');
        }

        // Cargar el rol y permisos del usuario
        $user->load('rol.permisos');

        // Verificar si el usuario tiene al menos uno de los permisos (OR logic)
        $tienePermiso = false;
        $permisosRequeridos = [];
        
        foreach ($permisos as $permiso) {
            $permisosRequeridos[] = $permiso;
            if ($user->rol && $user->rol->permisos->contains('slug', $permiso)) {
                $tienePermiso = true;
                break;
            }
        }

        if (!$tienePermiso) {
            // Registrar acceso denegado en bitácora
            BitacoraHelper::accesoDenegado(
                $user->id,
                $request->ip(),
                "Intento de acceso a: {$request->path()} (requiere uno de: " . implode(', ', $permisosRequeridos) . ")"
            );

            // Redirigir con mensaje de error
            return redirect()->back()->with('error', 'No tienes permiso para realizar esta acción.');
        }

        return $next($request);
    }
}
