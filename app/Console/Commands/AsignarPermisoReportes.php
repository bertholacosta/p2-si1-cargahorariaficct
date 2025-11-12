<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Permiso;
use App\Models\Rol;

class AsignarPermisoReportes extends Command
{
    protected $signature = 'permisos:asignar-reportes';
    protected $description = 'Asignar permiso de reportes al rol Administrador';

    public function handle()
    {
        $permiso = Permiso::where('slug', 'asistencias.reportes')->first();
        $rol = Rol::where('nombre', 'Administrador')->first();

        if (!$permiso) {
            $this->error('El permiso asistencias.reportes no existe');
            return 1;
        }

        if (!$rol) {
            $this->error('El rol Administrador no existe');
            return 1;
        }

        // Verificar si ya está asignado
        if ($rol->permisos->contains($permiso->id)) {
            $this->info('✓ El permiso ya está asignado al rol Administrador');
            return 0;
        }

        // Asignar el permiso
        $rol->permisos()->attach($permiso->id);
        
        $this->info('✓ Permiso asistencias.reportes asignado correctamente al rol Administrador');
        
        return 0;
    }
}
