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
        Schema::create('notificacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuario')->constrained('usuario')->onDelete('cascade');
            $table->string('tipo', 50); // 'recordatorio_clase', 'mensaje_admin', 'inicio_sesion', 'sistema'
            $table->string('titulo', 200);
            $table->text('mensaje');
            $table->json('datos')->nullable(); // Datos adicionales en formato JSON
            $table->boolean('leida')->default(false);
            $table->timestamp('fecha_lectura')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento
            $table->index(['id_usuario', 'leida']);
            $table->index(['id_usuario', 'tipo']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacion');
    }
};
