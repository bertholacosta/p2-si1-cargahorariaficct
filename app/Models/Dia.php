<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dia extends Model
{
    use HasFactory;

    protected $table = 'dia';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Relación con horarios
     */
    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'id_dia');
    }
}
