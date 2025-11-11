<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AsistenciaService;
use Carbon\Carbon;

class RegistrarFaltasAutomaticas extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'asistencias:registrar-faltas {--fecha= : Fecha específica (Y-m-d)} {--gestion= : ID de gestión}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Registrar automáticamente faltas para clases no marcadas después del tiempo de tolerancia';

    protected $asistenciaService;

    public function __construct(AsistenciaService $asistenciaService)
    {
        parent::__construct();
        $this->asistenciaService = $asistenciaService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $fecha = $this->option('fecha') 
            ? Carbon::parse($this->option('fecha')) 
            : Carbon::now();
        
        $idGestion = $this->option('gestion');
        
        $this->info("Registrando faltas automáticas para: {$fecha->format('Y-m-d')}");
        
        $faltasRegistradas = $this->asistenciaService->registrarFaltasAutomaticas($fecha, $idGestion);
        
        $this->info("✓ Se registraron {$faltasRegistradas} faltas automáticas.");
        
        return Command::SUCCESS;
    }
}
