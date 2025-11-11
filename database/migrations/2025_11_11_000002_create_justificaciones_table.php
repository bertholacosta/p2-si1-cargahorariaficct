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
        Schema::create('justificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_asistencia')->unique()->constrained('asistencia')->onDelete('cascade');
            $table->text('descripcion');
            $table->string('archivo')->nullable(); // Ruta del documento de respaldo
            $table->timestamp('fecha_justificacion')->useCurrent();
            $table->foreignId('registrado_por')->nullable()->constrained('usuario')->onDelete('set null'); // Quién registró
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('justificacion');
    }
};
