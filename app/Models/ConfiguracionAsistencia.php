<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionAsistencia extends Model
{
    protected $table = 'configuracion_asistencia';

    protected $fillable = [
        'clave',
        'valor',
        'descripcion',
        'tipo',
    ];

    /**
     * Obtener valor de configuración por clave
     */
    public static function obtener(string $clave, $default = null)
    {
        $config = static::where('clave', $clave)->first();
        
        if (!$config) {
            return $default;
        }
        
        // Convertir según el tipo
        return match($config->tipo) {
            'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $config->valor,
            'float' => (float) $config->valor,
            default => $config->valor,
        };
    }

    /**
     * Establecer valor de configuración
     */
    public static function establecer(string $clave, $valor): bool
    {
        $config = static::where('clave', $clave)->first();
        
        if (!$config) {
            return false;
        }
        
        $config->valor = (string) $valor;
        return $config->save();
    }

    /**
     * Obtener minutos de tolerancia para registro de asistencia
     */
    public static function minutosTolerancia(): int
    {
        return static::obtener('minutos_tolerancia', 15);
    }

    /**
     * Verificar si el auto-registro de faltas está habilitado
     */
    public static function autoFaltaHabilitado(): bool
    {
        return static::obtener('auto_falta_enabled', true);
    }

    /**
     * Obtener hora límite para registro
     */
    public static function horaLimiteRegistro(): string
    {
        return static::obtener('hora_limite_registro', '23:59');
    }

    /**
     * Obtener todas las configuraciones
     */
    public static function todas(): array
    {
        return static::all()->mapWithKeys(function ($config) {
            $valor = match($config->tipo) {
                'boolean' => filter_var($config->valor, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $config->valor,
                'float' => (float) $config->valor,
                default => $config->valor,
            };
            
            return [$config->clave => [
                'valor' => $valor,
                'descripcion' => $config->descripcion,
                'tipo' => $config->tipo,
            ]];
        })->toArray();
    }
}
