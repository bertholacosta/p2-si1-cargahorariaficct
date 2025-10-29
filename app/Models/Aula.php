<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aula extends Model
{
    protected $table = 'aula';

    protected $fillable = [
        'numero',
        'numero_modulo',
    ];

    protected $casts = [
        'numero' => 'integer',
        'numero_modulo' => 'integer',
    ];

    /**
     * Relación con Modulo
     */
    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'numero_modulo', 'numero');
    }

    /**
     * Accessor para el nombre completo del aula
     */
    public function getNombreCompletoAttribute(): string
    {
        return "Módulo {$this->numero_modulo} - Aula {$this->numero}";
    }
}
