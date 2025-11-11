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
        Schema::table('asignacion', function (Blueprint $table) {
            $table->string('qr_token', 64)->unique()->nullable()->after('id_horario');
            $table->timestamp('qr_generado_en')->nullable()->after('qr_token');
            $table->integer('qr_duracion_minutos')->default(180)->after('qr_generado_en'); // 3 horas por defecto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignacion', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_generado_en', 'qr_duracion_minutos']);
        });
    }
};
