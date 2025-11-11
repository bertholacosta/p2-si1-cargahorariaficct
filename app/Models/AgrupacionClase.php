<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AgrupacionClase extends Model
{
    protected $table = 'agrupacion_clase';

    protected $fillable = [
        'id_docente',
        'id_grupo_materia',
        'id_dia',
        'id_gestion',
        'hora_inicio',
        'hora_fin',
        'cantidad_bloques',
    ];

    protected $casts = [
        'hora_inicio' => 'datetime:H:i',
        'hora_fin' => 'datetime:H:i',
    ];

    /**
     * Relación con Docente
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'codigo');
    }

    /**
     * Relación con Grupo Materia
     */
    public function grupoMateria(): BelongsTo
    {
        return $this->belongsTo(GrupoMateria::class, 'id_grupo_materia');
    }

    /**
     * Relación con Día
     */
    public function dia(): BelongsTo
    {
        return $this->belongsTo(Dia::class, 'id_dia');
    }

    /**
     * Relación con Gestión
     */
    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'id_gestion');
    }

    /**
     * Relación con Asignaciones a través de la tabla intermedia
     */
    public function asignaciones(): BelongsToMany
    {
        return $this->belongsToMany(
            Asignacion::class,
            'asignacion_agrupacion',
            'id_agrupacion_clase',
            'id_asignacion'
        )
        ->withPivot('orden')
        ->orderByPivot('orden');
    }

    /**
     * Obtener primera asignación (para usar en registros de asistencia)
     */
    public function primeraAsignacion()
    {
        return $this->asignaciones()->first();
    }

    /**
     * Calcular duración en minutos
     */
    public function duracionMinutos(): int
    {
        return $this->cantidad_bloques * 45;
    }

    /**
     * Verificar si el registro está dentro del tiempo de tolerancia
     */
    public function dentroDeTolerancia(\Carbon\Carbon $ahora = null): bool
    {
        $ahora = $ahora ?? now();
        $minutosTolerancia = ConfiguracionAsistencia::minutosTolerancia();
        
        $horaInicio = \Carbon\Carbon::parse($this->hora_inicio);
        $horaLimite = $horaInicio->copy()->addMinutes($minutosTolerancia);
        
        return $ahora->between($horaInicio, $horaLimite);
    }

    /**
     * Scope para filtrar por docente
     */
    public function scopeDelDocente($query, string $codigoDocente)
    {
        return $query->where('id_docente', $codigoDocente);
    }

    /**
     * Scope para filtrar por gestión
     */
    public function scopeDeGestion($query, int $idGestion)
    {
        return $query->where('id_gestion', $idGestion);
    }
}
