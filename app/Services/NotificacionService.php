<?php

namespace App\Services;

use App\Models\Notificacion;
use App\Models\Usuario;
use App\Models\Asignacion;
use App\Models\Bitacora;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class NotificacionService
{
    /**
     * Crear una notificación para un usuario
     */
    public function crear(
        int $idUsuario,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?array $datos = null
    ): Notificacion {
        return Notificacion::create([
            'id_usuario' => $idUsuario,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'datos' => $datos,
        ]);
    }

    /**
     * Crear notificación masiva para múltiples usuarios
     */
    public function crearMasiva(
        array $idsUsuarios,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?array $datos = null
    ): int {
        $notificaciones = [];
        $now = now();

        foreach ($idsUsuarios as $idUsuario) {
            $notificaciones[] = [
                'id_usuario' => $idUsuario,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'datos' => json_encode($datos),
                'leida' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        Notificacion::insert($notificaciones);
        return count($notificaciones);
    }

    /**
     * Obtener notificaciones de un usuario
     */
    public function obtenerNotificaciones(int $idUsuario, ?bool $soloNoLeidas = false, int $limite = 50): Collection
    {
        $query = Notificacion::where('id_usuario', $idUsuario)
            ->orderBy('created_at', 'desc')
            ->limit($limite);

        if ($soloNoLeidas) {
            $query->noLeidas();
        }

        return $query->get();
    }

    /**
     * Contar notificaciones no leídas
     */
    public function contarNoLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->noLeidas()
            ->count();
    }

    /**
     * Marcar notificación como leída
     */
    public function marcarComoLeida(int $idNotificacion): bool
    {
        $notificacion = Notificacion::find($idNotificacion);
        
        if ($notificacion) {
            $notificacion->marcarComoLeida();
            return true;
        }

        return false;
    }

    /**
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasComoLeidas(int $idUsuario): int
    {
        return Notificacion::where('id_usuario', $idUsuario)
            ->noLeidas()
            ->update([
                'leida' => true,
                'fecha_lectura' => now(),
            ]);
    }

    /**
     * Eliminar notificación
     */
    public function eliminar(int $idNotificacion): bool
    {
        $notificacion = Notificacion::find($idNotificacion);
        return $notificacion ? $notificacion->delete() : false;
    }

    /**
     * Crear notificación de recordatorio de clase
     * Se ejecuta X minutos antes de cada clase
     */
    public function crearRecordatorioClase(Asignacion $asignacion, int $minutosAntes = 15): void
    {
        $docente = $asignacion->docente;
        
        if (!$docente || !$docente->id_usuario) {
            return;
        }

        $materia = $asignacion->grupoMateria->materia->nombre ?? 'Materia';
        $grupo = $asignacion->grupoMateria->grupo->numero ?? '';
        $aula = $asignacion->aula->nombre ?? 'Sin asignar';
        $hora = $asignacion->horario->hora->hora_inicio ?? '';

        $this->crear(
            $docente->id_usuario,
            Notificacion::TIPO_RECORDATORIO_CLASE,
            '🔔 Recordatorio de Clase',
            "Tienes clase de {$materia} (Grupo {$grupo}) en {$minutosAntes} minutos. Aula: {$aula}",
            [
                'id_asignacion' => $asignacion->id,
                'materia' => $materia,
                'grupo' => $grupo,
                'aula' => $aula,
                'hora_inicio' => $hora,
            ]
        );
    }

    /**
     * Crear notificación de mensaje del administrador
     */
    public function crearMensajeAdmin(
        array $idsUsuarios,
        string $titulo,
        string $mensaje,
        ?int $idAdminRemitente = null
    ): int {
        $datos = [];
        
        if ($idAdminRemitente) {
            $admin = Usuario::find($idAdminRemitente);
            $datos['remitente'] = $admin ? $admin->username : 'Administrador';
        }

        return $this->crearMasiva(
            $idsUsuarios,
            Notificacion::TIPO_MENSAJE_ADMIN,
            $titulo,
            $mensaje,
            $datos
        );
    }

    /**
     * Crear notificación de inicio de sesión desde la bitácora
     */
    public function crearNotificacionInicioSesion(int $idUsuario, string $ip, Carbon $fecha): void
    {
        // Obtener información del dispositivo/navegador si está disponible
        $userAgent = request()->userAgent();
        $navegador = $this->obtenerNombreNavegador($userAgent);

        $this->crear(
            $idUsuario,
            Notificacion::TIPO_INICIO_SESION,
            '🔐 Inicio de Sesión Detectado',
            "Se detectó un inicio de sesión en tu cuenta desde {$ip} usando {$navegador}.",
            [
                'ip' => $ip,
                'fecha' => $fecha->toDateTimeString(),
                'user_agent' => $userAgent,
                'navegador' => $navegador,
            ]
        );
    }

    /**
     * Crear notificación de recordatorio de asistencia no registrada
     */
    public function crearRecordatorioAsistencia(int $idUsuario, string $materias): void
    {
        $this->crear(
            $idUsuario,
            Notificacion::TIPO_ASISTENCIA,
            '⚠️ Asistencia Pendiente',
            "Tienes clases con asistencia sin registrar: {$materias}. Recuerda registrar tu asistencia a tiempo.",
            [
                'materias' => $materias,
            ]
        );
    }

    /**
     * Generar notificaciones de recordatorio de clase para el día
     * Se ejecuta mediante un comando programado
     */
    public function generarRecordatoriosDelDia(Carbon $fecha, int $minutosAntes = 15): int
    {
        $nombreDia = $this->obtenerNombreDia($fecha->dayOfWeek);
        $notificacionesCreadas = 0;

        // Obtener todas las asignaciones del día
        $asignaciones = Asignacion::with([
                'docente.usuario',
                'horario.hora',
                'horario.dia',
                'grupoMateria.materia',
                'grupoMateria.grupo',
                'aula'
            ])
            ->whereHas('horario.dia', function ($q) use ($nombreDia) {
                $q->where('nombre', $nombreDia);
            })
            ->get();

        foreach ($asignaciones as $asignacion) {
            $hora = $asignacion->horario->hora;
            $horaClase = Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora->hora_inicio);
            $horaNotificacion = $horaClase->copy()->subMinutes($minutosAntes);

            // Solo crear notificación si falta el tiempo especificado
            if (now()->between($horaNotificacion->copy()->subMinutes(5), $horaNotificacion->copy()->addMinutes(5))) {
                $this->crearRecordatorioClase($asignacion, $minutosAntes);
                $notificacionesCreadas++;
            }
        }

        return $notificacionesCreadas;
    }

    /**
     * Limpiar notificaciones antiguas (más de X días)
     */
    public function limpiarNotificacionesAntiguas(int $dias = 30): int
    {
        $fechaLimite = Carbon::now()->subDays($dias);

        return Notificacion::where('leida', true)
            ->where('fecha_lectura', '<', $fechaLimite)
            ->delete();
    }

    /**
     * Obtener nombre del día de la semana
     */
    private function obtenerNombreDia(int $dayOfWeek): string
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        return $dias[$dayOfWeek] ?? 'Lunes';
    }

    /**
     * Obtener nombre del navegador desde el User Agent
     */
    private function obtenerNombreNavegador(string $userAgent): string
    {
        if (strpos($userAgent, 'Firefox') !== false) {
            return 'Firefox';
        } elseif (strpos($userAgent, 'Chrome') !== false) {
            return 'Chrome';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            return 'Safari';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            return 'Edge';
        } elseif (strpos($userAgent, 'Opera') !== false) {
            return 'Opera';
        }

        return 'Navegador desconocido';
    }
}
