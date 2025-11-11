<?php

namespace App\Http\Controllers;

use App\Services\NotificacionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionController extends Controller
{
    protected NotificacionService $notificacionService;

    public function __construct(NotificacionService $notificacionService)
    {
        $this->notificacionService = $notificacionService;
    }

    /**
     * Obtener todas las notificaciones del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $soloNoLeidas = $request->boolean('solo_no_leidas', false);
        $limite = $request->integer('limite', 50);

        $notificaciones = $this->notificacionService->obtenerNotificaciones(
            $user->id,
            $soloNoLeidas,
            $limite
        );

        return response()->json([
            'notificaciones' => $notificaciones,
            'no_leidas' => $this->notificacionService->contarNoLeidas($user->id),
        ]);
    }

    /**
     * Obtener el contador de notificaciones no leídas
     */
    public function contarNoLeidas(): JsonResponse
    {
        $user = auth()->user();
        $contador = $this->notificacionService->contarNoLeidas($user->id);

        return response()->json(['no_leidas' => $contador]);
    }

    /**
     * Marcar una notificación como leída
     */
    public function marcarComoLeida(int $id): JsonResponse
    {
        $success = $this->notificacionService->marcarComoLeida($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notificación marcada como leída' : 'Notificación no encontrada',
        ]);
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasComoLeidas(): JsonResponse
    {
        $user = auth()->user();
        $cantidad = $this->notificacionService->marcarTodasComoLeidas($user->id);

        return response()->json([
            'success' => true,
            'message' => "{$cantidad} notificaciones marcadas como leídas",
            'cantidad' => $cantidad,
        ]);
    }

    /**
     * Eliminar una notificación
     */
    public function eliminar(int $id): JsonResponse
    {
        $success = $this->notificacionService->eliminar($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Notificación eliminada' : 'Notificación no encontrada',
        ]);
    }

    /**
     * Vista para administrar notificaciones (Admin)
     */
    public function gestionAdmin(): Response
    {
        $usuarios = \App\Models\Usuario::with('rol')->get();
        
        return Inertia::render('Notificaciones/GestionAdmin', [
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Enviar mensaje masivo desde el admin
     */
    public function enviarMensajeMasivo(Request $request): JsonResponse
    {
        $request->validate([
            'destinatarios' => 'required|array',
            'destinatarios.*' => 'exists:usuario,id',
            'titulo' => 'required|string|max:200',
            'mensaje' => 'required|string',
        ]);

        $cantidad = $this->notificacionService->crearMensajeAdmin(
            $request->destinatarios,
            $request->titulo,
            $request->mensaje,
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => "Mensaje enviado a {$cantidad} usuarios",
            'cantidad' => $cantidad,
        ]);
    }
}
