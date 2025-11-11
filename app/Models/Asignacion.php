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
        'qr_token',
        'qr_generado_en',
        'qr_duracion_minutos',
    ];

    protected $casts = [
        'qr_generado_en' => 'datetime',
        'qr_duracion_minutos' => 'integer',
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

    /**
     * Generar un token QR único para esta asignación
     */
    public function generarTokenQR(int $duracionMinutos = 180): string
    {
        $this->qr_token = bin2hex(random_bytes(32));
        $this->qr_generado_en = now();
        $this->qr_duracion_minutos = $duracionMinutos;
        $this->save();

        return $this->qr_token;
    }

    /**
     * Verificar si el token QR es válido (no expirado)
     */
    public function qrEsValido(): bool
    {
        if (!$this->qr_token || !$this->qr_generado_en) {
            return false;
        }

        $expiracion = $this->qr_generado_en->addMinutes($this->qr_duracion_minutos);
        return now()->lessThanOrEqualTo($expiracion);
    }

    /**
     * Obtener minutos restantes de validez del QR
     */
    public function minutosRestantesQR(): int
    {
        if (!$this->qrEsValido()) {
            return 0;
        }

        $expiracion = $this->qr_generado_en->addMinutes($this->qr_duracion_minutos);
        return now()->diffInMinutes($expiracion, false);
    }
}
