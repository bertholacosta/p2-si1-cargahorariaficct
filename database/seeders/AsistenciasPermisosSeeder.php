<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsistenciasPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permisos = [
            [
                'nombre' => 'Ver Asistencias',
                'slug' => 'asistencias.ver',
                'modulo' => 'Asistencias',
                'descripcion' => 'Ver asistencias propias',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Registrar Asistencia',
                'slug' => 'asistencias.registrar',
                'modulo' => 'Asistencias',
                'descripcion' => 'Registrar asistencia',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Justificar Faltas',
                'slug' => 'asistencias.justificar',
                'modulo' => 'Asistencias',
                'descripcion' => 'Justificar faltas',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Gestionar Asistencias',
                'slug' => 'asistencias.gestionar',
                'modulo' => 'Asistencias',
                'descripcion' => 'Gestionar todas las asistencias (Admin)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($permisos as $permiso) {
            // Verificar si ya existe
            $existe = DB::table('permiso')->where('slug', $permiso['slug'])->exists();
            
            if (!$existe) {
                DB::table('permiso')->insert($permiso);
                $this->command->info("✓ Permiso creado: {$permiso['slug']}");
            } else {
                $this->command->warn("⚠ Permiso ya existe: {$permiso['slug']}");
            }
        }

        $this->command->info("\n=== Asignando permisos a roles ===\n");

        // Asignar permisos al rol Administrador (asumiendo que es el primer rol)
        $rolAdmin = DB::table('rol')->where('nombre', 'Administrador')->first();
        
        if ($rolAdmin) {
            $permisosAsistencias = DB::table('permiso')
                ->where('modulo', 'Asistencias')
                ->pluck('id');
            
            foreach ($permisosAsistencias as $permisoId) {
                $existe = DB::table('rol_permiso')
                    ->where('id_rol', $rolAdmin->id)
                    ->where('id_permiso', $permisoId)
                    ->exists();
                
                if (!$existe) {
                    DB::table('rol_permiso')->insert([
                        'id_rol' => $rolAdmin->id,
                        'id_permiso' => $permisoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info("✓ Permisos asignados al rol Administrador");
        }

        // Asignar permisos básicos al rol Docente (si existe)
        $rolDocente = DB::table('rol')->where('nombre', 'Docente')->first();
        
        if ($rolDocente) {
            $permisosDocente = DB::table('permiso')
                ->whereIn('slug', ['asistencias.ver', 'asistencias.registrar', 'asistencias.justificar'])
                ->pluck('id');
            
            foreach ($permisosDocente as $permisoId) {
                $existe = DB::table('rol_permiso')
                    ->where('id_rol', $rolDocente->id)
                    ->where('id_permiso', $permisoId)
                    ->exists();
                
                if (!$existe) {
                    DB::table('rol_permiso')->insert([
                        'id_rol' => $rolDocente->id,
                        'id_permiso' => $permisoId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            
            $this->command->info("✓ Permisos básicos asignados al rol Docente");
        }

        $this->command->info("\n✅ Permisos de Asistencias configurados correctamente");
    }
}
