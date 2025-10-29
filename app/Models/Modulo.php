<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $table = 'modulo';
    protected $primaryKey = 'numero';
    public $incrementing = false;
    protected $keyType = 'integer';

    protected $fillable = [
        'numero',
        'facultad',
    ];

    /**
     * Relación con Aulas
     */
    public function aulas(): HasMany
    {
        return $this->hasMany(Aula::class, 'numero_modulo', 'numero');
    }
}
