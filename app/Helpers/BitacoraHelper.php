<?php

namespace App\Helpers;

use App\Models\Bitacora;

class BitacoraHelper
{
    /**
     * Registrar login exitoso
     */
    public static function loginExitoso(int $usuarioId, string $ip): void
    {
        Bitacora::registrar(
            'Usuario inició sesión en el sistema',
            $usuarioId,
            $ip
        );
    }

    /**
     * Registrar login fallido
     */
    public static function loginFallido(string $email, string $ip): void
    {
        Bitacora::create([
            'accion' => "Intento de inicio de sesión fallido con email: {$email}",
            'ip' => $ip,
            'id_usuario' => null,
        ]);
    }

    /**
     * Registrar logout
     */
    public static function logout(int $usuarioId, string $ip): void
    {
        Bitacora::registrar(
            'Usuario cerró sesión',
            $usuarioId,
            $ip
        );
    }

    /**
     * Registrar cambio de contraseña
     */
    public static function cambioContrasena(int $usuarioId): void
    {
        Bitacora::registrar(
            'Usuario cambió su contraseña',
            $usuarioId
        );
    }

    /**
     * Registrar acceso denegado
     */
    public static function accesoDenegado(int $usuarioId, string $recurso): void
    {
        Bitacora::registrar(
            "Acceso denegado al recurso: {$recurso}",
            $usuarioId
        );
    }

    /**
     * Registrar error del sistema
     */
    public static function errorSistema(string $mensaje, ?int $usuarioId = null): void
    {
        Bitacora::registrar(
            "Error del sistema: {$mensaje}",
            $usuarioId
        );
    }
}
