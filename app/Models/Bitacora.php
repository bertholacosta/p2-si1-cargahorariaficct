<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bitacora extends Model
{
    protected $table = 'bitacora';
    
    public $timestamps = false; // Usamos 'fecha' en lugar de created_at/updated_at
    
    protected $fillable = [
        'accion',
        'fecha',
        'ip',
        'id_usuario',
    ];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    /**
     * Relación con Usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'id_usuario');
    }

    /**
     * Registrar una acción en la bitácora
     */
    public static function registrar(string $accion, ?int $usuarioId = null, ?string $ip = null): void
    {
        self::create([
            'accion' => $accion,
            'ip' => $ip ?? \App\Helpers\BitacoraHelper::obtenerIpReal(),
            'id_usuario' => $usuarioId ?? auth()->id(),
        ]);
    }
}
