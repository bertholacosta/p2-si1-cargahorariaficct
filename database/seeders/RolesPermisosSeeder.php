<?php

namespace Database\Seeders;

use App\Models\Permiso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;

class RolesPermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Permisos adicionales (usar updateOrCreate para evitar duplicados)
        $permisos = [
            ['nombre' => 'Ver usuarios', 'slug' => 'ver_usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Crear usuarios', 'slug' => 'crear_usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Editar usuarios', 'slug' => 'editar_usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Eliminar usuarios', 'slug' => 'eliminar_usuarios', 'modulo' => 'Usuarios'],
            ['nombre' => 'Ver horarios', 'slug' => 'ver_horarios', 'modulo' => 'Horarios'],
            ['nombre' => 'Crear horarios', 'slug' => 'crear_horarios', 'modulo' => 'Horarios'],
            ['nombre' => 'Editar horarios', 'slug' => 'editar_horarios', 'modulo' => 'Horarios'],
            ['nombre' => 'Eliminar horarios', 'slug' => 'eliminar_horarios', 'modulo' => 'Horarios'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::updateOrCreate(
                ['slug' => $permiso['slug']],
                $permiso
            );
        }

        // Crear Roles (usar firstOrCreate para evitar duplicados)
        $adminRol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Acceso total al sistema']
        );
        
        $docenteRol = Rol::firstOrCreate(
            ['nombre' => 'Docente'],
            ['descripcion' => 'Acceso para docentes']
        );
        
        $estudianteRol = Rol::firstOrCreate(
            ['nombre' => 'Estudiante'],
            ['descripcion' => 'Acceso para estudiantes']
        );

        // Asignar permisos básicos (sync reemplaza, no duplica)
        $adminRol->permisos()->sync(Permiso::all()->pluck('id'));

        // Asignar permisos específicos al Docente
        $docenteRol->permisos()->sync(
            Permiso::whereIn('slug', ['ver_horarios', 'crear_horarios', 'editar_horarios'])->pluck('id')
        );

        // Asignar permisos específicos al Estudiante
        $estudianteRol->permisos()->sync(
            Permiso::where('slug', 'ver_horarios')->pluck('id')
        );

        // Crear usuarios de prueba (usar firstOrCreate para evitar duplicados)
        Usuario::firstOrCreate(
            ['email' => 'admin@ficct.edu.bo'],
            [
                'username' => 'admin',
                'password' => 'password123', // Será hasheado automáticamente
                'id_rol' => $adminRol->id,
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'docente@ficct.edu.bo'],
            [
                'username' => 'docente',
                'password' => 'password123',
                'id_rol' => $docenteRol->id,
            ]
        );

        Usuario::firstOrCreate(
            ['email' => 'estudiante@ficct.edu.bo'],
            [
                'username' => 'estudiante',
                'password' => 'password123',
                'id_rol' => $estudianteRol->id,
            ]
        );
        
        $this->command->info('✓ Roles y usuarios creados exitosamente');
    }
}
