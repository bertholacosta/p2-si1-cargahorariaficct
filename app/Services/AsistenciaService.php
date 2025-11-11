<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Asignacion;
use App\Models\AgrupacionClase;
use App\Models\DiaNoLaborable;
use App\Models\ConfiguracionAsistencia;
use App\Models\Gestion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AsistenciaService
{
    /**
     * Convertir número de día (0-6) a nombre del día
     */
    private function obtenerNombreDia(int $dayOfWeek): string
    {
        $dias = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];
        
        return $dias[$dayOfWeek] ?? 'Lunes';
    }

    /**
     * Registrar asistencia de un docente para una clase específica
     */
    public function registrarAsistencia(
        string $codigoDocente,
        int $idAsignacion,
        ?Carbon $fecha = null,
        string $estado = 'Presente',
        bool $esDocente = true
    ): array {
        $fecha = $fecha ?? Carbon::now();
        
        // Validar que no sea día no laborable
        $asignacion = Asignacion::findOrFail($idAsignacion);
        if ($this->esDiaNoLaborable($fecha, $asignacion->id_gestion)) {
            return [
                'success' => false,
                'message' => 'No se puede registrar asistencia en un día no laborable.',
            ];
        }
        
        // Validar ventana de tiempo si es el docente quien registra
        if ($esDocente && $estado === 'Presente') {
            $validacion = $this->validarVentanaTiempo($asignacion, $fecha);
            if (!$validacion['valido']) {
                return [
                    'success' => false,
                    'message' => $validacion['mensaje'],
                ];
            }
        }
        
        // Registrar asistencia
        try {
            $asistencia = Asistencia::updateOrCreate(
                [
                    'id_docente' => $codigoDocente,
                    'id_asignacion' => $idAsignacion,
                    'fecha' => $fecha->format('Y-m-d'),
                ],
                [
                    'fecha_asistencia' => now(),
                    'estado' => $estado,
                    'registrada_por_docente' => $esDocente,
                ]
            );
            
            return [
                'success' => true,
                'message' => 'Asistencia registrada correctamente.',
                'asistencia' => $asistencia,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al registrar asistencia: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validar ventana de tiempo para registro
     */
    private function validarVentanaTiempo(Asignacion $asignacion, Carbon $fecha): array
    {
        $ahora = Carbon::now();
        
        // Obtener horario de la asignación
        $horario = $asignacion->horario;
        $hora = $horario->hora;
        
        $horaClase = Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora->hora_inicio);
        $minutosTolerancia = ConfiguracionAsistencia::minutosTolerancia();
        $horaLimite = $horaClase->copy()->addMinutes($minutosTolerancia);
        
        // Verificar si está dentro del rango
        if ($ahora->lt($horaClase)) {
            return [
                'valido' => false,
                'mensaje' => 'Aún no es hora de registrar asistencia. La clase inicia a las ' . $hora->hora_inicio,
            ];
        }
        
        if ($ahora->gt($horaLimite)) {
            return [
                'valido' => false,
                'mensaje' => 'El tiempo para registrar asistencia ha expirado. Límite: ' . 
                             $minutosTolerancia . ' minutos después del inicio.',
            ];
        }
        
        return ['valido' => true];
    }

    /**
     * Verificar si una fecha es día no laborable
     */
    public function esDiaNoLaborable(Carbon $fecha, ?int $idGestion = null): bool
    {
        return DiaNoLaborable::esDiaNoLaborable($fecha, $idGestion);
    }

    /**
     * Obtener asistencias de un docente en un rango de fechas
     */
    public function obtenerAsistenciasDocente(
        string $codigoDocente,
        Carbon $fechaInicio,
        Carbon $fechaFin,
        ?int $idGestion = null
    ) {
        $query = Asistencia::with(['asignacion.horario.hora', 'asignacion.horario.dia', 'asignacion.grupoMateria', 'justificacion'])
            ->delDocente($codigoDocente)
            ->entreFechas($fechaInicio, $fechaFin);
        
        if ($idGestion) {
            $query->whereHas('asignacion', function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion);
            });
        }
        
        return $query->orderBy('fecha', 'desc')
                    ->orderBy('fecha_asistencia', 'desc')
                    ->get();
    }

    /**
     * Generar reporte de asistencias para administrador
     */
    public function obtenerReporteGeneral(
        int $idGestion,
        ?Carbon $fechaInicio = null,
        ?Carbon $fechaFin = null,
        ?string $codigoDocente = null
    ) {
        $gestion = Gestion::findOrFail($idGestion);
        
        $fechaInicio = $fechaInicio ?? Carbon::parse($gestion->fecha_inicio);
        $fechaFin = $fechaFin ?? Carbon::parse($gestion->fecha_fin);
        
        $query = Asistencia::with([
                'docente.usuario',
                'asignacion.grupoMateria.grupo',
                'asignacion.grupoMateria.materia',
                'justificacion'
            ])
            ->entreFechas($fechaInicio, $fechaFin)
            ->whereHas('asignacion', function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion);
            });
        
        if ($codigoDocente) {
            $query->delDocente($codigoDocente);
        }
        
        return $query->orderBy('fecha', 'desc')->get();
    }

    /**
     * Generar estadísticas de asistencia de un docente
     */
    public function generarEstadisticas(string $codigoDocente, int $idGestion): array
    {
        $gestion = Gestion::findOrFail($idGestion);
        
        $asistencias = Asistencia::delDocente($codigoDocente)
            ->whereHas('asignacion', function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion);
            })
            ->get();
        
        $total = $asistencias->count();
        $presentes = $asistencias->where('estado', 'Presente')->count();
        $faltas = $asistencias->where('estado', 'Falta')->count();
        $justificadas = $asistencias->where('estado', 'Justificada')->count();
        $licencias = $asistencias->where('estado', 'Licencia')->count();
        
        return [
            'total' => $total,
            'presentes' => $presentes,
            'faltas' => $faltas,
            'justificadas' => $justificadas,
            'licencias' => $licencias,
            'porcentaje_asistencia' => $total > 0 ? round(($presentes / $total) * 100, 2) : 0,
            'porcentaje_faltas' => $total > 0 ? round(($faltas / $total) * 100, 2) : 0,
        ];
    }

    /**
     * Registrar automáticamente faltas para clases no marcadas
     */
    public function registrarFaltasAutomaticas(Carbon $fecha, ?int $idGestion = null): int
    {
        if (!ConfiguracionAsistencia::autoFaltaHabilitado()) {
            return 0;
        }
        
        // Verificar que no sea día no laborable
        if ($this->esDiaNoLaborable($fecha, $idGestion)) {
            return 0;
        }
        
        $faltasRegistradas = 0;
        
        // Obtener todas las asignaciones del día
        $nombreDia = $this->obtenerNombreDia($fecha->dayOfWeek);
        
        $query = Asignacion::with(['horario.hora', 'horario.dia'])
            ->whereHas('horario.dia', function ($q) use ($nombreDia) {
                $q->where('nombre', $nombreDia);
            });
        
        if ($idGestion) {
            $query->where('id_gestion', $idGestion);
        }
        
        $asignaciones = $query->get();
        
        foreach ($asignaciones as $asignacion) {
            $hora = $asignacion->horario->hora;
            $horaClase = Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora->hora_inicio);
            $minutosTolerancia = ConfiguracionAsistencia::minutosTolerancia();
            $horaLimite = $horaClase->copy()->addMinutes($minutosTolerancia);
            
            // Solo registrar falta si ya pasó el tiempo de tolerancia
            if (now()->gt($horaLimite)) {
                // Verificar si ya existe registro de asistencia
                $existe = Asistencia::where('id_docente', $asignacion->id_docente)
                    ->where('id_asignacion', $asignacion->id)
                    ->where('fecha', $fecha->format('Y-m-d'))
                    ->exists();
                
                if (!$existe) {
                    Asistencia::create([
                        'id_docente' => $asignacion->id_docente,
                        'id_asignacion' => $asignacion->id,
                        'fecha' => $fecha->format('Y-m-d'),
                        'estado' => 'Falta',
                        'registrada_por_docente' => false,
                        'observacion' => 'Falta registrada automáticamente por el sistema',
                    ]);
                    
                    $faltasRegistradas++;
                }
            }
        }
        
        return $faltasRegistradas;
    }

    /**
     * Justificar una falta
     */
    public function justificarFalta(
        int $idAsistencia,
        string $descripcion,
        ?int $idUsuario = null,
        ?string $archivo = null
    ): array {
        try {
            DB::beginTransaction();
            
            $asistencia = Asistencia::findOrFail($idAsistencia);
            
            // Actualizar estado de asistencia
            $asistencia->update(['estado' => 'Justificada']);
            
            // Crear o actualizar justificación
            $justificacion = $asistencia->justificacion()->updateOrCreate(
                ['id_asistencia' => $idAsistencia],
                [
                    'descripcion' => $descripcion,
                    'archivo' => $archivo,
                    'registrado_por' => $idUsuario,
                    'fecha_justificacion' => now(),
                ]
            );
            
            DB::commit();
            
            return [
                'success' => true,
                'message' => 'Falta justificada correctamente.',
                'justificacion' => $justificacion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            
            return [
                'success' => false,
                'message' => 'Error al justificar falta: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Obtener clases del día para un docente
     */
    public function obtenerClasesDelDia(string $codigoDocente, ?Carbon $fecha = null): array
    {
        $fecha = $fecha ?? Carbon::now();
        $nombreDia = $this->obtenerNombreDia($fecha->dayOfWeek);
        
        $asignaciones = Asignacion::with([
                'horario.hora',
                'horario.dia',
                'grupoMateria.grupo',
                'grupoMateria.materia',
                'aula'
            ])
            ->where('id_docente', $codigoDocente)
            ->whereHas('horario.dia', function ($q) use ($nombreDia) {
                $q->where('nombre', $nombreDia);
            })
            ->get()
            ->map(function ($asignacion) use ($fecha, $codigoDocente) {
                $hora = $asignacion->horario->hora;
                $horaInicio = Carbon::parse($fecha->format('Y-m-d') . ' ' . $hora->hora_inicio);
                $minutosTolerancia = ConfiguracionAsistencia::minutosTolerancia();
                $horaLimite = $horaInicio->copy()->addMinutes($minutosTolerancia);
                
                // Verificar si ya registró asistencia
                $asistencia = Asistencia::where('id_docente', $codigoDocente)
                    ->where('id_asignacion', $asignacion->id)
                    ->where('fecha', $fecha->format('Y-m-d'))
                    ->first();
                
                return [
                    'asignacion' => $asignacion,
                    'puede_registrar' => now()->between($horaInicio, $horaLimite) && !$asistencia,
                    'ya_registrada' => (bool) $asistencia,
                    'asistencia' => $asistencia,
                    'hora_inicio' => $hora->hora_inicio,
                    'hora_fin' => $hora->hora_fin,
                    'hora_limite_registro' => $horaLimite->format('H:i'),
                ];
            })
            ->sortBy('hora_inicio')
            ->values()
            ->toArray();
        
        return $asignaciones;
    }
}
