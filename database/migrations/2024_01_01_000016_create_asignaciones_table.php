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
        Schema::create('asignacion', function (Blueprint $table) {
            $table->id();
            $table->string('id_docente', 20);
            $table->foreignId('id_grupo_materia')->constrained('grupo_materia')->onDelete('cascade');
            $table->foreignId('id_horario')->constrained('horario')->onDelete('cascade');
            $table->foreignId('id_aula')->constrained('aula')->onDelete('cascade');
            $table->foreignId('id_gestion')->constrained('gestion')->onDelete('cascade');
            $table->foreign('id_docente')->references('codigo')->on('docente')->onDelete('cascade');
            $table->timestamps();
            
            // Índices para búsquedas rápidas
            $table->index(['id_docente', 'id_gestion']);
            $table->index(['id_aula', 'id_gestion']);
            $table->index(['id_grupo_materia', 'id_gestion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion');
    }
};
