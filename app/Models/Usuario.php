<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'username',
        'password',
        'email',
        'id_rol',
        'notificaciones_inicio_sesion',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'notificaciones_inicio_sesion' => 'boolean',
        ];
    }

    /**
     * Relación: Un usuario pertenece a un rol
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    /**
     * Relación: Un usuario puede tener un docente
     */
    public function docente(): HasOne
    {
        return $this->hasOne(Docente::class, 'id_usuario', 'id');
    }

    /**
     * Relación: Un usuario tiene muchas notificaciones
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class, 'id_usuario');
    }

    /**
     * Verificar si el usuario tiene un permiso específico
     */
    public function hasPermiso(string $permiso): bool
    {
        return $this->rol?->permisos()->where('nombre', $permiso)->exists() ?? false;
    }

    /**
     * Verificar si el usuario tiene un rol específico
     */
    public function hasRole(string $rol): bool
    {
        return $this->rol?->nombre === $rol;
    }
}
