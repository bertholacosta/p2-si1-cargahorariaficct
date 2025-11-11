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
        'fecha_cliente',
        'ip',
        'id_usuario',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'fecha_cliente' => 'datetime',
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
    public static function registrar(string $accion, ?int $usuarioId = null, ?string $ip = null, $fechaCliente = null): void
    {
        $data = [
            'accion' => $accion,
            'fecha' => now(), // Establecer explícitamente la fecha actual en zona horaria configurada
            'ip' => $ip ?? \App\Helpers\BitacoraHelper::obtenerIpReal(),
            'id_usuario' => $usuarioId ?? auth()->id(),
        ];

        // Si se proporciona fecha del cliente, guardarla
        if ($fechaCliente !== null) {
            $data['fecha_cliente'] = $fechaCliente;
        }

        self::create($data);
    }
}
