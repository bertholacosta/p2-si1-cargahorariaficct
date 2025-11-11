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
        Schema::create('asistencia', function (Blueprint $table) {
            $table->id();
            $table->string('id_docente', 20);
            $table->foreignId('id_asignacion')->constrained('asignacion')->onDelete('cascade');
            $table->date('fecha'); // Fecha de la clase
            $table->timestamp('fecha_asistencia')->nullable(); // Momento exacto del registro
            $table->enum('estado', ['Presente', 'Falta', 'Licencia', 'Justificada'])->default('Falta');
            $table->text('observacion')->nullable(); // Notas del administrador
            $table->boolean('registrada_por_docente')->default(false); // Si la marcó el docente o admin
            $table->timestamps();
            
            $table->foreign('id_docente')->references('codigo')->on('docente')->onDelete('cascade');
            
            // Índices para búsquedas rápidas
            $table->index(['id_docente', 'fecha']);
            $table->index(['id_asignacion', 'fecha']);
            $table->index('estado');
            
            // Una asistencia por docente, asignación y fecha
            $table->unique(['id_docente', 'id_asignacion', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asistencia');
    }
};
