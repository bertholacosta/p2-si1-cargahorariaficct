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
        // Para PostgreSQL, necesitamos modificar el CHECK constraint
        // Primero eliminamos el constraint existente
        DB::statement("ALTER TABLE asistencia DROP CONSTRAINT IF EXISTS asistencia_estado_check");
        
        // Luego agregamos el nuevo constraint con 'Retraso' incluido
        DB::statement("ALTER TABLE asistencia ADD CONSTRAINT asistencia_estado_check CHECK (estado IN ('Presente', 'Falta', 'Licencia', 'Justificada', 'Retraso'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al constraint original sin 'Retraso'
        DB::statement("ALTER TABLE asistencia DROP CONSTRAINT IF EXISTS asistencia_estado_check");
        DB::statement("ALTER TABLE asistencia ADD CONSTRAINT asistencia_estado_check CHECK (estado IN ('Presente', 'Falta', 'Licencia', 'Justificada'))");
    }
};
