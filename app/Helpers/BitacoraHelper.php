<?php

namespace App\Helpers;

use App\Models\Bitacora;

class BitacoraHelper
{
    /**
     * Obtener la IP real del cliente (considerando proxies y load balancers)
     */
    public static function obtenerIpReal(): string
    {
        // Verificar headers en orden de preferencia
        $headers = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_X_REAL_IP',             // Nginx proxy
            'HTTP_X_FORWARDED_FOR',       // Proxy estándar
            'HTTP_CLIENT_IP',             // Proxy
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'                 // Fallback
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                
                // Si X-Forwarded-For contiene múltiples IPs, tomar la primera (cliente original)
                if (strpos($ip, ',') !== false) {
                    $ips = explode(',', $ip);
                    $ip = trim($ips[0]);
                }
                
                // Validar que sea una IP válida
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }

        // Fallback a la IP del request de Laravel
        return request()->ip() ?? '0.0.0.0';
    }

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
            'fecha' => now(),
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

    /**
     * Método genérico para registrar acciones en la bitácora
     * Compatible con llamadas antiguas de 4 parámetros
     * 
     * @param string $accion - Descripción de la acción
     * @param string|null $entidad - Tipo de entidad (legacy, ahora ignorado)
     * @param int|null $idEntidad - ID de la entidad (legacy, ahora ignorado)
     * @param string|null $detalles - Detalles adicionales (legacy, se concatena con acción)
     * @param string|null $fechaCliente - Fecha/hora del cliente en formato ISO 8601
     */
    public static function registrar(
        string $accion,
        ?string $entidad = null,
        ?int $idEntidad = null,
        ?string $detalles = null,
        ?string $fechaCliente = null
    ): void {
        // Si se pasaron los parámetros legacy (entidad, idEntidad, detalles)
        // los usamos para enriquecer la acción
        if ($detalles !== null) {
            $accion = $detalles;
        }

        $ip = self::obtenerIpReal();
        
        Bitacora::registrar(
            $accion,
            auth()->id(),
            $ip,
            $fechaCliente
        );
    }
}
