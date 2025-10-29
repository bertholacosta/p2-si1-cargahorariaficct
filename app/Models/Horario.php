<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horario';

    protected $fillable = [
        'id_dia',
        'id_hora',
    ];

    /**
     * Relación con dia
     */
    public function dia(): BelongsTo
    {
        return $this->belongsTo(Dia::class, 'id_dia');
    }

    /**
     * Relación con hora
     */
    public function hora(): BelongsTo
    {
        return $this->belongsTo(Hora::class, 'id_hora');
    }

    /**
     * Accessor para obtener el nombre completo del horario
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->dia->nombre} {$this->hora->hora_inicio} - {$this->hora->hora_fin}";
    }
}
