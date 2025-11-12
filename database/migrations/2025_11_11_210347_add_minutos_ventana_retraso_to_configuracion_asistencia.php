<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar configuración para ventana de retraso
        DB::table('configuracion_asistencia')->insert([
            'clave' => 'minutos_ventana_retraso',
            'valor' => '60',
            'descripcion' => 'Minutos después de la tolerancia en los que el docente puede registrar asistencia como "Retraso"',
            'tipo' => 'integer',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar configuración de ventana de retraso
        DB::table('configuracion_asistencia')->where('clave', 'minutos_ventana_retraso')->delete();
    }
};
