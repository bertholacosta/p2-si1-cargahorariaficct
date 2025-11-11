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
        // Crear Permisos
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
            Permiso::create($permiso);
        }

        // Crear Roles
        $adminRol = Rol::create(['nombre' => 'Administrador']);
        $docenteRol = Rol::create(['nombre' => 'Docente']);
        $estudianteRol = Rol::create(['nombre' => 'Estudiante']);

        // Asignar todos los permisos al Administrador
        $adminRol->permisos()->attach(Permiso::all());

        // Asignar permisos específicos al Docente
        $docenteRol->permisos()->attach(
            Permiso::whereIn('slug', ['ver_horarios', 'crear_horarios', 'editar_horarios'])->get()
        );

        // Asignar permisos específicos al Estudiante
        $estudianteRol->permisos()->attach(
            Permiso::where('slug', 'ver_horarios')->get()
        );

        // Crear usuario administrador por defecto
        Usuario::create([
            'username' => 'admin',
            'email' => 'admin@ficct.edu.bo',
            'password' => 'password123', // Será hasheado automáticamente
            'id_rol' => $adminRol->id,
        ]);

        // Crear usuario docente de prueba
        Usuario::create([
            'username' => 'docente',
            'email' => 'docente@ficct.edu.bo',
            'password' => 'password123',
            'id_rol' => $docenteRol->id,
        ]);

        // Crear usuario estudiante de prueba
        Usuario::create([
            'username' => 'estudiante',
            'email' => 'estudiante@ficct.edu.bo',
            'password' => 'password123',
            'id_rol' => $estudianteRol->id,
        ]);
    }
}
