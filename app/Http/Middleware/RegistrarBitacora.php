<?php

namespace App\Http\Middleware;

use App\Models\Bitacora;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegistrarBitacora
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar si el usuario está autenticado y no es una petición GET
        if (auth()->check() && !$request->isMethod('get')) {
            $this->registrarAccion($request, $response);
        }

        return $response;
    }

    private function registrarAccion(Request $request, Response $response): void
    {
        // No registrar si la respuesta no fue exitosa
        if ($response->getStatusCode() >= 400) {
            return;
        }

        $metodo = $request->method();
        $ruta = $request->path();
        $accion = $this->generarDescripcionAccion($metodo, $ruta, $request);

        if ($accion) {
            Bitacora::registrar($accion, auth()->id(), $request->ip());
        }
    }

    private function generarDescripcionAccion(string $metodo, string $ruta, Request $request): ?string
    {
        $usuario = auth()->user();
        $nombreUsuario = $usuario ? $usuario->nombre : 'Usuario';

        // Mapeo de rutas y acciones
        $acciones = [
            // Usuarios
            'POST|usuarios' => "{$nombreUsuario} creó un nuevo usuario",
            'PUT|usuarios/*' => "{$nombreUsuario} actualizó un usuario",
            'PATCH|usuarios/*' => "{$nombreUsuario} actualizó un usuario",
            'DELETE|usuarios/*' => "{$nombreUsuario} eliminó un usuario",

            // Roles
            'POST|roles' => "{$nombreUsuario} creó un nuevo rol",
            'PUT|roles/*' => "{$nombreUsuario} actualizó un rol",
            'DELETE|roles/*' => "{$nombreUsuario} eliminó un rol",

            // Permisos
            'POST|permisos' => "{$nombreUsuario} creó un nuevo permiso",
            'PUT|permisos/*' => "{$nombreUsuario} actualizó un permiso",
            'DELETE|permisos/*' => "{$nombreUsuario} eliminó un permiso",

            // Docentes
            'POST|docentes' => "{$nombreUsuario} registró un nuevo docente",
            'PUT|docentes/*' => "{$nombreUsuario} actualizó los datos de un docente",
            'DELETE|docentes/*' => "{$nombreUsuario} eliminó un docente",

            // Materias
            'POST|materias' => "{$nombreUsuario} creó una nueva materia",
            'PUT|materias/*' => "{$nombreUsuario} actualizó una materia",
            'DELETE|materias/*' => "{$nombreUsuario} eliminó una materia",

            // Grupos
            'POST|grupos' => "{$nombreUsuario} creó un nuevo grupo",
            'PUT|grupos/*' => "{$nombreUsuario} actualizó un grupo",
            'DELETE|grupos/*' => "{$nombreUsuario} eliminó un grupo",

            // Aulas
            'POST|aulas' => "{$nombreUsuario} registró una nueva aula",
            'PUT|aulas/*' => "{$nombreUsuario} actualizó un aula",
            'DELETE|aulas/*' => "{$nombreUsuario} eliminó un aula",

            // Módulos
            'POST|modulos' => "{$nombreUsuario} creó un nuevo módulo",
            'PUT|modulos/*' => "{$nombreUsuario} actualizó un módulo",
            'DELETE|modulos/*' => "{$nombreUsuario} eliminó un módulo",

            // Gestiones
            'POST|gestiones' => "{$nombreUsuario} creó una nueva gestión académica",
            'PUT|gestiones/*' => "{$nombreUsuario} actualizó una gestión académica",
            'DELETE|gestiones/*' => "{$nombreUsuario} eliminó una gestión académica",

            // Días
            'POST|dias' => "{$nombreUsuario} registró un nuevo día",
            'PUT|dias/*' => "{$nombreUsuario} actualizó un día",
            'DELETE|dias/*' => "{$nombreUsuario} eliminó un día",

            // Horas
            'POST|horas' => "{$nombreUsuario} registró un nuevo bloque horario",
            'PUT|horas/*' => "{$nombreUsuario} actualizó un bloque horario",
            'DELETE|horas/*' => "{$nombreUsuario} eliminó un bloque horario",

            // Horarios
            'POST|horarios' => "{$nombreUsuario} creó un nuevo horario",
            'PUT|horarios/*' => "{$nombreUsuario} actualizó un horario",
            'DELETE|horarios/*' => "{$nombreUsuario} eliminó un horario",

            // Asignaciones
            'POST|asignaciones' => "{$nombreUsuario} creó una nueva asignación de clase",
            'PUT|asignaciones/*' => "{$nombreUsuario} actualizó una asignación de clase",
            'DELETE|asignaciones/*' => "{$nombreUsuario} eliminó una asignación de clase",

            // Login/Logout
            'POST|login' => "{$nombreUsuario} inició sesión",
            'POST|logout' => "{$nombreUsuario} cerró sesión",
        ];

        // Buscar coincidencia exacta o con wildcard
        foreach ($acciones as $patron => $descripcion) {
            [$metodoPatron, $rutaPatron] = explode('|', $patron);
            
            if ($metodo === $metodoPatron) {
                if ($rutaPatron === $ruta) {
                    return $descripcion;
                }
                
                // Verificar con wildcard
                if (str_contains($rutaPatron, '*')) {
                    $regex = '/^' . str_replace(['/', '*'], ['\/', '[\w-]+'], $rutaPatron) . '$/';
                    if (preg_match($regex, $ruta)) {
                        return $descripcion;
                    }
                }
            }
        }

        return null;
    }
}
