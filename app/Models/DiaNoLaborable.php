<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiaNoLaborable extends Model
{
    protected $table = 'dia_no_laborable';

    protected $fillable = [
        'fecha',
        'descripcion',
        'tipo',
        'id_gestion',
        'activo',
    ];

    protected $casts = [
        'fecha' => 'date',
        'activo' => 'boolean',
    ];

    /**
     * Relación con Gestión
     */
    public function gestion(): BelongsTo
    {
        return $this->belongsTo(Gestion::class, 'id_gestion');
    }

    /**
     * Scope para días activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeEnFecha($query, $fecha)
    {
        return $query->whereDate('fecha', $fecha);
    }

    /**
     * Scope para filtrar por gestión
     */
    public function scopeDeGestion($query, $idGestion)
    {
        return $query->where(function ($q) use ($idGestion) {
            $q->where('id_gestion', $idGestion)
              ->orWhereNull('id_gestion'); // Incluir días no laborables globales
        });
    }

    /**
     * Verificar si una fecha es día no laborable
     */
    public static function esDiaNoLaborable($fecha, $idGestion = null): bool
    {
        $query = static::activos()->enFecha($fecha);
        
        if ($idGestion) {
            $query->deGestion($idGestion);
        }
        
        return $query->exists();
    }

    /**
     * Obtener días no laborables de un mes
     */
    public static function delMes($año, $mes, $idGestion = null)
    {
        $query = static::activos()
            ->whereYear('fecha', $año)
            ->whereMonth('fecha', $mes);
            
        if ($idGestion) {
            $query->deGestion($idGestion);
        }
        
        return $query->get();
    }
}
