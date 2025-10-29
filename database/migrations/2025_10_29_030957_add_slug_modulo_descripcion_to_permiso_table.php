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
        Schema::table('permiso', function (Blueprint $table) {
            $table->string('slug', 100)->unique()->after('nombre');
            $table->string('modulo', 50)->after('slug');
            $table->text('descripcion')->nullable()->after('modulo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permiso', function (Blueprint $table) {
            $table->dropColumn(['slug', 'modulo', 'descripcion']);
        });
    }
};
