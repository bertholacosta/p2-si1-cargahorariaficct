<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Materia extends Model
{
    protected $table = 'materia';

    protected $fillable = [
        'sigla',
        'nombre',
        'carga_horaria',
        'creditos',
    ];

    protected $casts = [
        'carga_horaria' => 'integer',
        'creditos' => 'integer',
    ];

    /**
     * Relación con Docentes habilitados
     */
    public function docentes(): BelongsToMany
    {
        return $this->belongsToMany(
            Docente::class,
            'materia_habilitada',
            'id_materia',
            'id_docente',
            'id',
            'codigo'
        )->withTimestamps();
    }

    /**
     * Relación con Grupos
     */
    public function grupos(): BelongsToMany
    {
        return $this->belongsToMany(
            Grupo::class,
            'grupo_materia',
            'id_materia',
            'id_grupo'
        )->withTimestamps();
    }
}
