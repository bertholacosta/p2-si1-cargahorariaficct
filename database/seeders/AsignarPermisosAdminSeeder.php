<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Support\Facades\DB;

class AsignarPermisosAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar o crear el rol de Administrador
        $adminRol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total al sistema']
        );

        // Obtener todos los permisos
        $todosLosPermisos = Permiso::all()->pluck('id')->toArray();

        // Asignar todos los permisos al rol de Administrador
        $adminRol->permisos()->sync($todosLosPermisos);

        $this->command->info("✓ Rol 'Administrador' creado/actualizado");
        $this->command->info("✓ {$adminRol->permisos()->count()} permisos asignados al Administrador");

        // Opcional: Crear otros roles básicos
        $docenteRol = Rol::firstOrCreate(
            ['nombre' => 'Docente'],
            ['descripcion' => 'Acceso limitado para docentes']
        );

        // Asignar permisos básicos al Docente (solo lectura)
        $permisosDocente = Permiso::whereIn('slug', [
            'asignaciones.ver',
            'horarios.ver',
            'materias.ver',
            'grupos.ver',
            'aulas.ver',
            'dias.ver',
            'horas.ver',
        ])->pluck('id')->toArray();

        $docenteRol->permisos()->sync($permisosDocente);
        $this->command->info("✓ Rol 'Docente' creado/actualizado con permisos básicos");

        // Asistente con permisos de gestión pero sin eliminar
        $asistenteRol = Rol::firstOrCreate(
            ['nombre' => 'Asistente'],
            ['descripcion' => 'Gestión de horarios y asignaciones']
        );

        $permisosAsistente = Permiso::where(function($query) {
            $query->where('slug', 'like', '%.ver')
                  ->orWhere('slug', 'like', '%.crear')
                  ->orWhere('slug', 'like', '%.editar');
        })
        ->whereNotIn('slug', [
            'usuarios.ver', 'usuarios.crear', 'usuarios.editar',
            'roles.ver', 'roles.crear', 'roles.editar',
            'permisos.ver', 'permisos.crear', 'permisos.editar',
        ])
        ->pluck('id')->toArray();

        $asistenteRol->permisos()->sync($permisosAsistente);
        $this->command->info("✓ Rol 'Asistente' creado/actualizado con permisos de gestión");
    }
}
