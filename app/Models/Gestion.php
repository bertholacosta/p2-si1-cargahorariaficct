<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gestion extends Model
{
    use HasFactory;

    protected $table = 'gestion';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'semestre',
        'año',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'semestre' => 'integer',
        'año' => 'integer',
    ];

    /**
     * Accessor para obtener el nombre completo de la gestión
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->año} - Semestre {$this->semestre}";
    }

    /**
     * Accessor para obtener el periodo completo
     */
    public function getPeriodoAttribute(): string
    {
        $inicio = $this->fecha_inicio->format('d/m/Y');
        $fin = $this->fecha_fin->format('d/m/Y');
        return "{$inicio} - {$fin}";
    }
}
