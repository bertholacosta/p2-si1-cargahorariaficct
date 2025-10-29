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
        Schema::create('materia_habilitada', function (Blueprint $table) {
            $table->string('id_docente', 20);
            $table->integer('id_materia');
            $table->timestamps();

            $table->primary(['id_docente', 'id_materia']);

            $table->foreign('id_docente')
                  ->references('codigo')
                  ->on('docente')
                  ->onDelete('cascade');

            $table->foreign('id_materia')
                  ->references('id')
                  ->on('materia')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_habilitada');
    }
};
