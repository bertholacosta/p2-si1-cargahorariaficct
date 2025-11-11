<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificacion extends Model
{
    protected $table = 'justificacion';

    protected $fillable = [
        'id_asistencia',
        'descripcion',
        'archivo',
        'fecha_justificacion',
        'registrado_por',
    ];

    protected $casts = [
        'fecha_justificacion' => 'datetime',
    ];

    /**
     * Relación con Asistencia
     */
    public function asistencia(): BelongsTo
    {
        return $this->belongsTo(Asistencia::class, 'id_asistencia');
    }

    /**
     * Relación con el usuario que registró la justificación
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'registrado_por');
    }

    /**
     * Verificar si tiene archivo adjunto
     */
    public function tieneArchivo(): bool
    {
        return !empty($this->archivo);
    }

    /**
     * Obtener URL del archivo
     */
    public function getArchivoUrlAttribute(): ?string
    {
        if ($this->tieneArchivo()) {
            return asset('storage/' . $this->archivo);
        }
        return null;
    }
}
