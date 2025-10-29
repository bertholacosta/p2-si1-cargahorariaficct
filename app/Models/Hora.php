<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hora extends Model
{
    use HasFactory;

    protected $table = 'hora';

    protected $fillable = [
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * Relación con horarios
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_hora');
    }

    /**
     * Accessor para obtener el periodo completo
     */
    public function getPeriodoAttribute(): string
    {
        return "{$this->hora_inicio} - {$this->hora_fin}";
    }
}
