<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Usuario;
use App\Models\Permiso;

class VerificarPermisosUsuario extends Command
{
    protected $signature = 'permisos:verificar {email?}';
    protected $description = 'Verificar permisos de un usuario';

    public function handle()
    {
        $email = $this->argument('email');
        
        if (!$email) {
            $email = $this->ask('Ingresa el email del usuario');
        }

        $usuario = Usuario::where('email', $email)->first();

        if (!$usuario) {
            $this->error("Usuario con email {$email} no encontrado");
            return 1;
        }

        $this->info("\n=== Información del Usuario ===");
        $this->info("Email: {$usuario->email}");
        $this->info("Username: {$usuario->username}");
        $this->info("Rol: {$usuario->rol->nombre}");

        $this->info("\n=== Permisos del Rol ===");
        
        $permisos = $usuario->rol->permisos;
        
        if ($permisos->isEmpty()) {
            $this->warn("El usuario no tiene permisos asignados");
        } else {
            foreach ($permisos as $permiso) {
                $this->line("✓ {$permiso->nombre} ({$permiso->slug})");
            }
        }

        // Verificar específicamente el permiso de reportes
        $tieneReportes = $permisos->contains('slug', 'asistencias.reportes');
        
        $this->info("\n=== Permiso de Reportes ===");
        if ($tieneReportes) {
            $this->info("✓ El usuario TIENE acceso a reportes de asistencias");
        } else {
            $this->error("✗ El usuario NO TIENE acceso a reportes de asistencias");
        }

        return 0;
    }
}
