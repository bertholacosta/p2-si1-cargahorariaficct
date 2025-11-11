<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Orden importante para evitar conflictos de claves foráneas
        $this->call([
            // 1. Primero crear todos los permisos base
            PermisosSeeder::class,
            
            // 2. Luego crear roles y asignar permisos básicos
            RolesPermisosSeeder::class,
            
            // 3. Agregar permisos adicionales de asistencias
            AsistenciasPermisosSeeder::class,
            
            // 4. Finalmente asignar todos los permisos al admin
            AsignarPermisosAdminSeeder::class,
        ]);
    }
}
