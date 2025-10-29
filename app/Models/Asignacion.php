<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asignacion extends Model
{
    use HasFactory;

    protected $table = 'asignacion';

    protected $fillable = [
        'id_docente',
        'id_grupo_materia',
        'id_horario',
        'id_aula',
        'id_gestion',
    ];

    /**
     * Relación con docente
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'codigo');
    }

    /**
     * Relación con grupo_materia
     */
    public function grupoMateria(): BelongsTo
    {
        return $this->belongsTo(GrupoMateria::class, 'id_grupo_materia');
    }

    /**
     * Relación con horario
     */
    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class, 'id_horario');
    }

    /**
     * Relación con aula
     */
    public function aula(): BelongsTo
    {
        return $this->belongsTo(Aula::class, 'id_aula');
    }

    /**
     * Relación con gestión
     */
    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'id_gestion');
    }
}
