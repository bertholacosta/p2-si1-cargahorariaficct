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
            'ver_usuarios',
            'crear_usuarios',
            'editar_usuarios',
            'eliminar_usuarios',
            'ver_horarios',
            'crear_horarios',
            'editar_horarios',
            'eliminar_horarios',
        ];

        foreach ($permisos as $permiso) {
            Permiso::create(['nombre' => $permiso]);
        }

        // Crear Roles
        $adminRol = Rol::create(['nombre' => 'Administrador']);
        $docenteRol = Rol::create(['nombre' => 'Docente']);
        $estudianteRol = Rol::create(['nombre' => 'Estudiante']);

        // Asignar todos los permisos al Administrador
        $adminRol->permisos()->attach(Permiso::all());

        // Asignar permisos específicos al Docente
        $docenteRol->permisos()->attach(
            Permiso::whereIn('nombre', ['ver_horarios', 'crear_horarios', 'editar_horarios'])->get()
        );

        // Asignar permisos específicos al Estudiante
        $estudianteRol->permisos()->attach(
            Permiso::where('nombre', 'ver_horarios')->get()
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
