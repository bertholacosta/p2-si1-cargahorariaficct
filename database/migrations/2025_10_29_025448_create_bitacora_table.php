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
        Schema::create('bitacora', function (Blueprint $table) {
            $table->id();
            $table->text('accion');
            $table->timestamp('fecha')->useCurrent();
            $table->string('ip', 45)->nullable();
            $table->unsignedBigInteger('id_usuario')->nullable();
            
            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('usuario')
                  ->onDelete('set null');
            
            $table->index('id_usuario');
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bitacora');
    }
};
