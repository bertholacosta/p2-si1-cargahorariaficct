<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabla para configuraciones del sistema de asistencia
        Schema::create('configuracion_asistencia', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique(); // Ej: 'minutos_tolerancia', 'auto_falta_enabled'
            $table->string('valor'); // Valor de la configuración
            $table->string('descripcion')->nullable(); // Descripción para el admin
            $table->string('tipo')->default('integer'); // 'integer', 'boolean', 'string', 'time'
            $table->timestamps();
        });
        
        // Insertar configuraciones por defecto
        DB::table('configuracion_asistencia')->insert([
            [
                'clave' => 'minutos_tolerancia',
                'valor' => '15',
                'descripcion' => 'Minutos después del inicio de clase en los que el docente puede registrar asistencia',
                'tipo' => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'auto_falta_enabled',
                'valor' => 'true',
                'descripcion' => 'Registrar automáticamente faltas después del tiempo de tolerancia',
                'tipo' => 'boolean',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'clave' => 'hora_limite_registro',
                'valor' => '23:59',
                'descripcion' => 'Hora límite del día para registrar asistencia (formato HH:MM)',
                'tipo' => 'time',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_asistencia');
    }
};
