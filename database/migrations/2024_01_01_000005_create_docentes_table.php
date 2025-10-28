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
        Schema::create('docente', function (Blueprint $table) {
            $table->string('codigo', 20)->primary();
            $table->integer('id_usuario')->unique()->nullable();
            $table->string('nombre', 255);
            $table->string('apellidos', 255);
            $table->string('ci', 20)->unique();
            $table->timestamps();

            $table->foreign('id_usuario')
                  ->references('id')
                  ->on('usuario')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('docente');
    }
};
