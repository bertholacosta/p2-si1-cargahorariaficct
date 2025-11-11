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
        // Tabla para gestionar días no laborables (feriados, vacaciones, etc.)
        Schema::create('dia_no_laborable', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('descripcion'); // Ej: "Día del Trabajo", "Vacaciones de Invierno"
            $table->enum('tipo', ['Feriado', 'Vacación', 'Suspensión', 'Otro'])->default('Feriado');
            $table->foreignId('id_gestion')->nullable()->constrained('gestion')->onDelete('cascade'); // Si aplica a una gestión específica
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index('fecha');
            $table->index(['id_gestion', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dia_no_laborable');
    }
};
