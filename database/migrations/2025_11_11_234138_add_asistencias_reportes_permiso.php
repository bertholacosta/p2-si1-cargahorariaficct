<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Verificar si el permiso ya existe
        $existe = DB::table('permiso')->where('slug', 'asistencias.reportes')->exists();
        
        if (!$existe) {
            // Insertar el permiso
            $permisoId = DB::table('permiso')->insertGetId([
                'nombre' => 'Generar Reportes de Asistencias',
                'slug' => 'asistencias.reportes',
                'modulo' => 'Asistencias',
                'descripcion' => 'Generar reportes de asistencias con filtros avanzados',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Asignar el permiso al rol Administrador
            $rolAdmin = DB::table('rol')->where('nombre', 'Administrador')->first();
            
            if ($rolAdmin) {
                // Verificar si la relación ya existe
                $relacionExiste = DB::table('rol_permiso')
                    ->where('id_rol', $rolAdmin->id)
                    ->where('id_permiso', $permisoId)
                    ->exists();
                
                if (!$relacionExiste) {
                    DB::table('rol_permiso')->insert([
                        'id_rol' => $rolAdmin->id,
                        'id_permiso' => $permisoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar la relación rol-permiso
        $permiso = DB::table('permiso')->where('slug', 'asistencias.reportes')->first();
        
        if ($permiso) {
            DB::table('rol_permiso')->where('id_permiso', $permiso->id)->delete();
            DB::table('permiso')->where('id', $permiso->id)->delete();
        }
    }
};
