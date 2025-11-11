<?php

namespace App\Console\Commands;

use App\Services\NotificacionService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GenerarRecordatoriosClase extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notificaciones:recordatorios-clase {--minutos=15}';

    /**
     * The console command description.
     */
    protected $description = 'Genera notificaciones de recordatorio de clase X minutos antes';

    /**
     * Execute the console command.
     */
    public function handle(NotificacionService $notificacionService): int
    {
        $minutosAntes = (int) $this->option('minutos');
        $fecha = Carbon::now();

        $this->info("Generando recordatorios de clase para el {$fecha->format('d/m/Y')}...");
        $this->info("Tiempo de anticipación: {$minutosAntes} minutos");

        $cantidad = $notificacionService->generarRecordatoriosDelDia($fecha, $minutosAntes);

        if ($cantidad > 0) {
            $this->info("✓ Se generaron {$cantidad} notificaciones de recordatorio");
        } else {
            $this->warn("No hay clases próximas para notificar");
        }

        return Command::SUCCESS;
    }
}
