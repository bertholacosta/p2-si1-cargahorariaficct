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
        Schema::table('bitacora', function (Blueprint $table) {
            // Agregar columna para guardar la fecha/hora del cliente
            $table->timestamp('fecha_cliente')->nullable()->after('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bitacora', function (Blueprint $table) {
            $table->dropColumn('fecha_cliente');
        });
    }
};
