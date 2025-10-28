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
        Schema::create('usuario', function (Blueprint $table) {
            $table->id();
            $table->string('username', 30)->unique();
            $table->string('password', 255); // Laravel usa bcrypt, necesita más de 50 caracteres
            $table->string('email', 100)->unique(); // Aumentado para emails más largos
            $table->foreignId('id_rol')->nullable()->constrained('rol')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
