<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grupo extends Model
{
    protected $table = 'grupo';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación con Materias
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'grupo_materia',
            'id_grupo',
            'id_materia'
        )->withTimestamps();
    }
}
