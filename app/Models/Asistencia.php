<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Asistencia extends Model
{
    protected $table = 'asistencia';

    protected $fillable = [
        'id_docente',
        'id_asignacion',
        'fecha',
        'fecha_asistencia',
        'estado',
        'observacion',
        'registrada_por_docente',
    ];

    protected $casts = [
        'fecha' => 'date',
        'fecha_asistencia' => 'datetime',
        'registrada_por_docente' => 'boolean',
    ];

    /**
     * Relación con Docente
     */
    public function docente(): BelongsTo
    {
        return $this->belongsTo(Docente::class, 'id_docente', 'codigo');
    }

    /**
     * Relación con Asignación
     */
    public function asignacion(): BelongsTo
    {
        return $this->belongsTo(Asignacion::class, 'id_asignacion');
    }

    /**
     * Relación con Justificación (uno a uno)
     */
    public function justificacion(): HasOne
    {
        return $this->hasOne(Justificacion::class, 'id_asistencia');
    }

    /**
     * Scope para filtrar por docente
     */
    public function scopeDelDocente($query, string $codigoDocente)
    {
        return $query->where('id_docente', $codigoDocente);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeEnFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar por rango de fechas
     */
    public function scopeEntreFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeConEstado($query, string $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Verificar si la asistencia está justificada
     */
    public function estaJustificada(): bool
    {
        return $this->estado === 'Justificada' || $this->justificacion()->exists();
    }

    /**
     * Verificar si es una falta
     */
    public function esFalta(): bool
    {
        return $this->estado === 'Falta' && !$this->estaJustificada();
    }
}
