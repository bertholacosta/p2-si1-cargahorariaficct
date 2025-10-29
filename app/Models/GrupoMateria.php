<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrupoMateria extends Model
{
    use HasFactory;

    protected $table = 'grupo_materia';

    protected $fillable = [
        'id_grupo',
        'id_materia',
    ];

    /**
     * Relación con grupo
     */
    public function grupo(): BelongsTo
    {
        return $this->belongsTo(Grupo::class, 'id_grupo');
    }

    /**
     * Relación con materia
     */
    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'id_materia');
    }

    /**
     * Relación con asignaciones
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(Asignacion::class, 'id_grupo_materia');
    }

    /**
     * Accessor para obtener el nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->materia->nombre} - {$this->grupo->nombre}";
    }
}
