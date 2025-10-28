<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
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
