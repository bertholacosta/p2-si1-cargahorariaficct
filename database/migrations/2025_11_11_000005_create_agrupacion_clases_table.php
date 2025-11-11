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
        // Tabla para agrupar asignaciones que corresponden a una misma clase
        // (Resuelve el problema de clases de 7:00 a 9:15 que ocupan 3 bloques de 45 min)
        Schema::create('agrupacion_clase', function (Blueprint $table) {
            $table->id();
            $table->string('id_docente', 20);
            $table->foreignId('id_grupo_materia')->constrained('grupo_materia')->onDelete('cascade');
            $table->foreignId('id_dia')->constrained('dia')->onDelete('cascade');
            $table->foreignId('id_gestion')->constrained('gestion')->onDelete('cascade');
            $table->time('hora_inicio'); // Calculada del primer bloque
            $table->time('hora_fin'); // Calculada del último bloque
            $table->integer('cantidad_bloques')->default(1); // Número de bloques de 45 min
            $table->timestamps();
            
            $table->foreign('id_docente')->references('codigo')->on('docente')->onDelete('cascade');
            
            // Índices
            $table->index(['id_docente', 'id_gestion']);
            $table->index(['id_grupo_materia', 'id_dia']);
        });
        
        // Tabla intermedia para relacionar asignaciones con su agrupación
        Schema::create('asignacion_agrupacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_agrupacion_clase')->constrained('agrupacion_clase')->onDelete('cascade');
            $table->foreignId('id_asignacion')->constrained('asignacion')->onDelete('cascade');
            $table->integer('orden')->default(1); // Orden dentro de la clase (1er bloque, 2do bloque, etc.)
            $table->timestamps();
            
            $table->unique(['id_agrupacion_clase', 'id_asignacion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_agrupacion');
        Schema::dropIfExists('agrupacion_clase');
    }
};
