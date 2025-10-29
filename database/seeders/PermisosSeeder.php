<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permiso;
use Illuminate\Support\Facades\DB;

class PermisosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar permisos existentes
        DB::table('rol_permiso')->truncate();
        Permiso::truncate();

        $permisos = [
            // Usuarios
            ['nombre' => 'Ver usuarios', 'slug' => 'usuarios.ver', 'modulo' => 'Usuarios', 'descripcion' => 'Puede ver la lista de usuarios'],
            ['nombre' => 'Crear usuarios', 'slug' => 'usuarios.crear', 'modulo' => 'Usuarios', 'descripcion' => 'Puede crear nuevos usuarios'],
            ['nombre' => 'Editar usuarios', 'slug' => 'usuarios.editar', 'modulo' => 'Usuarios', 'descripcion' => 'Puede modificar usuarios existentes'],
            ['nombre' => 'Eliminar usuarios', 'slug' => 'usuarios.eliminar', 'modulo' => 'Usuarios', 'descripcion' => 'Puede eliminar usuarios'],

            // Roles
            ['nombre' => 'Ver roles', 'slug' => 'roles.ver', 'modulo' => 'Roles', 'descripcion' => 'Puede ver la lista de roles'],
            ['nombre' => 'Crear roles', 'slug' => 'roles.crear', 'modulo' => 'Roles', 'descripcion' => 'Puede crear nuevos roles'],
            ['nombre' => 'Editar roles', 'slug' => 'roles.editar', 'modulo' => 'Roles', 'descripcion' => 'Puede modificar roles existentes'],
            ['nombre' => 'Eliminar roles', 'slug' => 'roles.eliminar', 'modulo' => 'Roles', 'descripcion' => 'Puede eliminar roles'],
            ['nombre' => 'Asignar permisos', 'slug' => 'roles.permisos', 'modulo' => 'Roles', 'descripcion' => 'Puede asignar permisos a roles'],

            // Permisos
            ['nombre' => 'Ver permisos', 'slug' => 'permisos.ver', 'modulo' => 'Permisos', 'descripcion' => 'Puede ver la lista de permisos'],
            ['nombre' => 'Crear permisos', 'slug' => 'permisos.crear', 'modulo' => 'Permisos', 'descripcion' => 'Puede crear nuevos permisos'],
            ['nombre' => 'Editar permisos', 'slug' => 'permisos.editar', 'modulo' => 'Permisos', 'descripcion' => 'Puede modificar permisos existentes'],
            ['nombre' => 'Eliminar permisos', 'slug' => 'permisos.eliminar', 'modulo' => 'Permisos', 'descripcion' => 'Puede eliminar permisos'],

            // Docentes
            ['nombre' => 'Ver docentes', 'slug' => 'docentes.ver', 'modulo' => 'Docentes', 'descripcion' => 'Puede ver la lista de docentes'],
            ['nombre' => 'Crear docentes', 'slug' => 'docentes.crear', 'modulo' => 'Docentes', 'descripcion' => 'Puede registrar nuevos docentes'],
            ['nombre' => 'Editar docentes', 'slug' => 'docentes.editar', 'modulo' => 'Docentes', 'descripcion' => 'Puede modificar datos de docentes'],
            ['nombre' => 'Eliminar docentes', 'slug' => 'docentes.eliminar', 'modulo' => 'Docentes', 'descripcion' => 'Puede eliminar docentes'],
            ['nombre' => 'Asignar materias a docentes', 'slug' => 'docentes.materias', 'modulo' => 'Docentes', 'descripcion' => 'Puede asignar materias habilitadas a docentes'],

            // Materias
            ['nombre' => 'Ver materias', 'slug' => 'materias.ver', 'modulo' => 'Materias', 'descripcion' => 'Puede ver la lista de materias'],
            ['nombre' => 'Crear materias', 'slug' => 'materias.crear', 'modulo' => 'Materias', 'descripcion' => 'Puede crear nuevas materias'],
            ['nombre' => 'Editar materias', 'slug' => 'materias.editar', 'modulo' => 'Materias', 'descripcion' => 'Puede modificar materias existentes'],
            ['nombre' => 'Eliminar materias', 'slug' => 'materias.eliminar', 'modulo' => 'Materias', 'descripcion' => 'Puede eliminar materias'],
            ['nombre' => 'Asignar grupos a materias', 'slug' => 'materias.grupos', 'modulo' => 'Materias', 'descripcion' => 'Puede asignar grupos a materias'],

            // Grupos
            ['nombre' => 'Ver grupos', 'slug' => 'grupos.ver', 'modulo' => 'Grupos', 'descripcion' => 'Puede ver la lista de grupos'],
            ['nombre' => 'Crear grupos', 'slug' => 'grupos.crear', 'modulo' => 'Grupos', 'descripcion' => 'Puede crear nuevos grupos'],
            ['nombre' => 'Editar grupos', 'slug' => 'grupos.editar', 'modulo' => 'Grupos', 'descripcion' => 'Puede modificar grupos existentes'],
            ['nombre' => 'Eliminar grupos', 'slug' => 'grupos.eliminar', 'modulo' => 'Grupos', 'descripcion' => 'Puede eliminar grupos'],

            // Aulas
            ['nombre' => 'Ver aulas', 'slug' => 'aulas.ver', 'modulo' => 'Aulas', 'descripcion' => 'Puede ver la lista de aulas'],
            ['nombre' => 'Crear aulas', 'slug' => 'aulas.crear', 'modulo' => 'Aulas', 'descripcion' => 'Puede registrar nuevas aulas'],
            ['nombre' => 'Editar aulas', 'slug' => 'aulas.editar', 'modulo' => 'Aulas', 'descripcion' => 'Puede modificar aulas existentes'],
            ['nombre' => 'Eliminar aulas', 'slug' => 'aulas.eliminar', 'modulo' => 'Aulas', 'descripcion' => 'Puede eliminar aulas'],

            // Módulos
            ['nombre' => 'Ver módulos', 'slug' => 'modulos.ver', 'modulo' => 'Módulos', 'descripcion' => 'Puede ver la lista de módulos'],
            ['nombre' => 'Crear módulos', 'slug' => 'modulos.crear', 'modulo' => 'Módulos', 'descripcion' => 'Puede crear nuevos módulos'],
            ['nombre' => 'Editar módulos', 'slug' => 'modulos.editar', 'modulo' => 'Módulos', 'descripcion' => 'Puede modificar módulos existentes'],
            ['nombre' => 'Eliminar módulos', 'slug' => 'modulos.eliminar', 'modulo' => 'Módulos', 'descripcion' => 'Puede eliminar módulos'],

            // Gestiones
            ['nombre' => 'Ver gestiones', 'slug' => 'gestiones.ver', 'modulo' => 'Gestiones', 'descripcion' => 'Puede ver gestiones académicas'],
            ['nombre' => 'Crear gestiones', 'slug' => 'gestiones.crear', 'modulo' => 'Gestiones', 'descripcion' => 'Puede crear nuevas gestiones'],
            ['nombre' => 'Editar gestiones', 'slug' => 'gestiones.editar', 'modulo' => 'Gestiones', 'descripcion' => 'Puede modificar gestiones'],
            ['nombre' => 'Eliminar gestiones', 'slug' => 'gestiones.eliminar', 'modulo' => 'Gestiones', 'descripcion' => 'Puede eliminar gestiones'],

            // Días
            ['nombre' => 'Ver días', 'slug' => 'dias.ver', 'modulo' => 'Horarios', 'descripcion' => 'Puede ver días de la semana'],
            ['nombre' => 'Crear días', 'slug' => 'dias.crear', 'modulo' => 'Horarios', 'descripcion' => 'Puede crear días'],
            ['nombre' => 'Editar días', 'slug' => 'dias.editar', 'modulo' => 'Horarios', 'descripcion' => 'Puede modificar días'],
            ['nombre' => 'Eliminar días', 'slug' => 'dias.eliminar', 'modulo' => 'Horarios', 'descripcion' => 'Puede eliminar días'],

            // Horas (Bloques Horarios)
            ['nombre' => 'Ver bloques horarios', 'slug' => 'horas.ver', 'modulo' => 'Horarios', 'descripcion' => 'Puede ver bloques horarios'],
            ['nombre' => 'Crear bloques horarios', 'slug' => 'horas.crear', 'modulo' => 'Horarios', 'descripcion' => 'Puede crear bloques horarios'],
            ['nombre' => 'Editar bloques horarios', 'slug' => 'horas.editar', 'modulo' => 'Horarios', 'descripcion' => 'Puede modificar bloques horarios'],
            ['nombre' => 'Eliminar bloques horarios', 'slug' => 'horas.eliminar', 'modulo' => 'Horarios', 'descripcion' => 'Puede eliminar bloques horarios'],

            // Horarios
            ['nombre' => 'Ver horarios', 'slug' => 'horarios.ver', 'modulo' => 'Horarios', 'descripcion' => 'Puede ver horarios'],
            ['nombre' => 'Crear horarios', 'slug' => 'horarios.crear', 'modulo' => 'Horarios', 'descripcion' => 'Puede crear horarios'],
            ['nombre' => 'Editar horarios', 'slug' => 'horarios.editar', 'modulo' => 'Horarios', 'descripcion' => 'Puede modificar horarios'],
            ['nombre' => 'Eliminar horarios', 'slug' => 'horarios.eliminar', 'modulo' => 'Horarios', 'descripcion' => 'Puede eliminar horarios'],

            // Asignaciones
            ['nombre' => 'Ver asignaciones', 'slug' => 'asignaciones.ver', 'modulo' => 'Asignaciones', 'descripcion' => 'Puede ver asignaciones de clases'],
            ['nombre' => 'Crear asignaciones', 'slug' => 'asignaciones.crear', 'modulo' => 'Asignaciones', 'descripcion' => 'Puede crear asignaciones de clases'],
            ['nombre' => 'Editar asignaciones', 'slug' => 'asignaciones.editar', 'modulo' => 'Asignaciones', 'descripcion' => 'Puede modificar asignaciones'],
            ['nombre' => 'Eliminar asignaciones', 'slug' => 'asignaciones.eliminar', 'modulo' => 'Asignaciones', 'descripcion' => 'Puede eliminar asignaciones'],
            ['nombre' => 'Exportar horarios', 'slug' => 'asignaciones.exportar', 'modulo' => 'Asignaciones', 'descripcion' => 'Puede exportar horarios a PDF/CSV'],

            // Bitácora
            ['nombre' => 'Ver bitácora', 'slug' => 'bitacora.ver', 'modulo' => 'Sistema', 'descripcion' => 'Puede ver el registro de actividades'],
            ['nombre' => 'Exportar bitácora', 'slug' => 'bitacora.exportar', 'modulo' => 'Sistema', 'descripcion' => 'Puede exportar la bitácora'],

            // Reportes
            ['nombre' => 'Ver reportes', 'slug' => 'reportes.ver', 'modulo' => 'Reportes', 'descripcion' => 'Puede ver reportes del sistema'],
            ['nombre' => 'Generar reportes', 'slug' => 'reportes.generar', 'modulo' => 'Reportes', 'descripcion' => 'Puede generar reportes personalizados'],
            ['nombre' => 'Exportar reportes', 'slug' => 'reportes.exportar', 'modulo' => 'Reportes', 'descripcion' => 'Puede exportar reportes'],

            // Configuración
            ['nombre' => 'Ver configuración', 'slug' => 'configuracion.ver', 'modulo' => 'Sistema', 'descripcion' => 'Puede ver la configuración del sistema'],
            ['nombre' => 'Editar configuración', 'slug' => 'configuracion.editar', 'modulo' => 'Sistema', 'descripcion' => 'Puede modificar la configuración del sistema'],
        ];

        foreach ($permisos as $permiso) {
            Permiso::create($permiso);
        }

        $this->command->info('Permisos creados exitosamente: ' . count($permisos));
    }
}
