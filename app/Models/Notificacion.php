<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificacion';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'titulo',
        'mensaje',
        'datos',
        'leida',
        'fecha_lectura',
    ];

    protected $casts = [
        'datos' => 'array',
        'leida' => 'boolean',
        'fecha_lectura' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Tipos de notificaciones
    public const TIPO_RECORDATORIO_CLASE = 'recordatorio_clase';
    public const TIPO_MENSAJE_ADMIN = 'mensaje_admin';
    public const TIPO_INICIO_SESION = 'inicio_sesion';
    public const TIPO_SISTEMA = 'sistema';
    public const TIPO_ASISTENCIA = 'asistencia';

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeNoLeidas($query)
    {
        return $query->where('leida', false);
    }

    /**
     * Scope para notificaciones de un tipo específico
     */
    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    /**
     * Marcar como leída
     */
    public function marcarComoLeida(): void
    {
        if (!$this->leida) {
            $this->update([
                'leida' => true,
                'fecha_lectura' => now(),
            ]);
        }
    }

    /**
     * Obtener el ícono según el tipo
     */
    public function getIconoAttribute(): string
    {
        return match($this->tipo) {
            self::TIPO_RECORDATORIO_CLASE => 'pi-calendar',
            self::TIPO_MENSAJE_ADMIN => 'pi-megaphone',
            self::TIPO_INICIO_SESION => 'pi-sign-in',
            self::TIPO_ASISTENCIA => 'pi-check-circle',
            default => 'pi-bell',
        };
    }

    /**
     * Obtener el color según el tipo
     */
    public function getColorAttribute(): string
    {
        return match($this->tipo) {
            self::TIPO_RECORDATORIO_CLASE => 'info',
            self::TIPO_MENSAJE_ADMIN => 'warn',
            self::TIPO_INICIO_SESION => 'success',
            self::TIPO_ASISTENCIA => 'primary',
            default => 'secondary',
        };
    }
}
