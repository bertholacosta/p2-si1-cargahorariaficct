<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Docente extends Model
{
    protected $table = 'docente';
    protected $primaryKey = 'codigo';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'codigo',
        'id_usuario',
        'nombre',
        'apellidos',
        'ci',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id');
    }

    /**
     * Relación con Materias habilitadas
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(
            Materia::class,
            'materia_habilitada',
            'id_docente',
            'id_materia',
            'codigo',
            'id'
        )->withTimestamps();
    }

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellidos}";
    }
}
