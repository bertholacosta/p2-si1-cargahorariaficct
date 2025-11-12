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
        if ($esDocente) {
            $validacion = $this->validarVentanaTiempo($asignacion, $fecha);
            
            // Si es Presente, debe estar en ventana normal
            if ($estado === 'Presente' && !$validacion['valido']) {
                return [
                    'success' => false,
                    'message' => $validacion['mensaje'],
                ];
            }
            
            // Si es Retraso, debe estar en ventana de retraso
            if ($estado === 'Retraso' && !$validacion['puede_marcar_retraso']) {
                return [
                    'success' => false,
                    'message' => 'No está en la ventana de tiempo válida para marcar retraso.',
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
        $minutosVentanaRetraso = ConfiguracionAsistencia::minutosVentanaRetraso();
        
        $horaLimite = $horaClase->copy()->addMinutes($minutosTolerancia);
        $horaLimiteRetraso = $horaLimite->copy()->addMinutes($minutosVentanaRetraso);
        
        // Verificar si está dentro del rango
        if ($ahora->lt($horaClase)) {
            return [
                'valido' => false,
                'puede_marcar_retraso' => false,
                'mensaje' => 'Aún no es hora de registrar asistencia. La clase inicia a las ' . $hora->hora_inicio,
            ];
        }
        
        // Si pasó la hora límite de tolerancia pero está dentro de la ventana de retraso
        if ($ahora->gt($horaLimite) && $ahora->lte($horaLimiteRetraso)) {
            return [
                'valido' => false,
                'puede_marcar_retraso' => true,
                'mensaje' => 'El tiempo para registrar asistencia normal ha expirado, pero puede marcar retraso.',
                'hora_limite_retraso' => $horaLimiteRetraso->format('H:i'),
            ];
        }
        
        // Si ya pasó también la ventana de retraso
        if ($ahora->gt($horaLimiteRetraso)) {
            return [
                'valido' => false,
                'puede_marcar_retraso' => false,
                'mensaje' => 'El tiempo para registrar asistencia ha expirado completamente. Límite de retraso: ' . 
                             $horaLimiteRetraso->format('H:i'),
            ];
        }
        
        // Dentro de la ventana normal
        return [
            'valido' => true,
            'puede_marcar_retraso' => false,
        ];
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
        $retrasos = $asistencias->where('estado', 'Retraso')->count();
        $faltas = $asistencias->where('estado', 'Falta')->count();
        $justificadas = $asistencias->where('estado', 'Justificada')->count();
        $licencias = $asistencias->where('estado', 'Licencia')->count();
        
        return [
            'total' => $total,
            'presentes' => $presentes,
            'retrasos' => $retrasos,
            'faltas' => $faltas,
            'justificadas' => $justificadas,
            'licencias' => $licencias,
            'porcentaje_asistencia' => $total > 0 ? round(($presentes / $total) * 100, 2) : 0,
            'porcentaje_retrasos' => $total > 0 ? round(($retrasos / $total) * 100, 2) : 0,
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
        
        // Obtener todas las asignaciones del día
        $asignaciones = Asignacion::with([
                'horario.hora',
                'horario.dia',
                'grupoMateria.grupo',
                'grupoMateria.materia',
                'aula',
                'gestion'
            ])
            ->where('id_docente', $codigoDocente)
            ->whereHas('horario.dia', function ($q) use ($nombreDia) {
                $q->where('nombre', $nombreDia);
            })
            // Filtrar por gestión activa (fecha dentro del rango)
            ->whereHas('gestion', function ($q) use ($fecha) {
                $q->where('fecha_inicio', '<=', $fecha)
                  ->where('fecha_fin', '>=', $fecha);
            })
            ->get()
            ->sortBy(function ($asignacion) {
                return $asignacion->horario->hora->hora_inicio;
            });
        
        // Agrupar clases consecutivas por materia y grupo
        $clasesAgrupadas = [];
        $asignacionesAgrupadas = [];
        
        foreach ($asignaciones as $asignacion) {
            $idGrupoMateria = $asignacion->id_grupo_materia;
            $idAula = $asignacion->id_aula;
            $horaInicio = $asignacion->horario->hora->hora_inicio;
            $horaFin = $asignacion->horario->hora->hora_fin;
            
            // Buscar si hay un grupo al que se pueda agregar
            $agregado = false;
            foreach ($asignacionesAgrupadas as $key => &$grupo) {
                // Verificar si es la misma materia, grupo y aula
                if ($grupo['id_grupo_materia'] === $idGrupoMateria && 
                    $grupo['id_aula'] === $idAula) {
                    
                    // Verificar si es consecutiva (la hora de inicio coincide con la hora fin del grupo)
                    if ($horaInicio === $grupo['hora_fin']) {
                        // Extender el grupo
                        $grupo['hora_fin'] = $horaFin;
                        $grupo['asignaciones'][] = $asignacion;
                        $agregado = true;
                        break;
                    }
                }
            }
            
            // Si no se agregó a ningún grupo, crear uno nuevo
            if (!$agregado) {
                $asignacionesAgrupadas[] = [
                    'id_grupo_materia' => $idGrupoMateria,
                    'id_aula' => $idAula,
                    'hora_inicio' => $horaInicio,
                    'hora_fin' => $horaFin,
                    'asignaciones' => [$asignacion],
                ];
            }
        }
        
        // Convertir grupos a formato de respuesta
        foreach ($asignacionesAgrupadas as $grupo) {
            $primeraAsignacion = $grupo['asignaciones'][0];
            $hora = $primeraAsignacion->horario->hora;
            
            $horaInicio = Carbon::parse($fecha->format('Y-m-d') . ' ' . $grupo['hora_inicio']);
            $minutosTolerancia = ConfiguracionAsistencia::minutosTolerancia();
            $minutosVentanaRetraso = ConfiguracionAsistencia::minutosVentanaRetraso();
            $horaLimite = $horaInicio->copy()->addMinutes($minutosTolerancia);
            $horaLimiteRetraso = $horaLimite->copy()->addMinutes($minutosVentanaRetraso);
            
            // Verificar si ya registró asistencia para alguna de las asignaciones del grupo
            $asistencias = [];
            $todasRegistradas = true;
            $algunaRegistrada = false;
            
            foreach ($grupo['asignaciones'] as $asig) {
                $asistencia = Asistencia::where('id_docente', $codigoDocente)
                    ->where('id_asignacion', $asig->id)
                    ->where('fecha', $fecha->format('Y-m-d'))
                    ->first();
                
                if ($asistencia) {
                    $asistencias[] = $asistencia;
                    $algunaRegistrada = true;
                } else {
                    $todasRegistradas = false;
                }
            }
            
            $ahora = Carbon::now();
            $puedeRegistrar = $ahora->between($horaInicio, $horaLimite) && !$algunaRegistrada;
            $puedeMarcarRetraso = $ahora->gt($horaLimite) && 
                                  $ahora->lte($horaLimiteRetraso) && 
                                  !$algunaRegistrada;
            
            $clasesAgrupadas[] = [
                'asignaciones' => collect($grupo['asignaciones'])->map(fn($a) => [
                    'id' => $a->id,
                    'id_grupo_materia' => $a->id_grupo_materia,
                    'id_aula' => $a->id_aula,
                ])->toArray(),
                'asignacion' => $primeraAsignacion, // Para compatibilidad con el frontend
                'es_grupo' => count($grupo['asignaciones']) > 1,
                'cantidad_bloques' => count($grupo['asignaciones']),
                'puede_registrar' => $puedeRegistrar,
                'puede_marcar_retraso' => $puedeMarcarRetraso,
                'ya_registrada' => $algunaRegistrada,
                'todas_registradas' => $todasRegistradas,
                'asistencia' => $asistencias[0] ?? null, // Primera asistencia encontrada
                'hora_inicio' => $grupo['hora_inicio'],
                'hora_fin' => $grupo['hora_fin'],
                'hora_limite_registro' => $horaLimite->format('H:i'),
                'hora_limite_retraso' => $horaLimiteRetraso->format('H:i'),
            ];
        }
        
        return $clasesAgrupadas;
    }

    /**
     * Obtener reporte avanzado de horarios y asistencias
     * Con filtros por aula, docente, grupo, gestión y rango de fechas
     */
    public function obtenerReporteHorarios(
        ?int $idGestion = null,
        ?Carbon $fechaInicio = null,
        ?Carbon $fechaFin = null,
        ?string $codigoDocente = null,
        ?int $idAula = null,
        ?int $idGrupo = null
    ) {
        // Construir query base con relaciones
        $query = Asistencia::with([
            'docente',
            'asignacion.grupoMateria.materia',
            'asignacion.grupoMateria.grupo',
            'asignacion.aula',
            'asignacion.horario.hora',
            'asignacion.horario.dia',
            'asignacion.gestion',
            'justificacion'
        ]);

        // Filtro por gestión
        if ($idGestion) {
            $query->whereHas('asignacion', function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion);
            });
        }

        // Filtro por rango de fechas
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->where('fecha', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $query->where('fecha', '<=', $fechaFin);
        }

        // Filtro por docente
        if ($codigoDocente) {
            $query->where('id_docente', $codigoDocente);
        }

        // Filtro por aula
        if ($idAula) {
            $query->whereHas('asignacion', function ($q) use ($idAula) {
                $q->where('id_aula', $idAula);
            });
        }

        // Filtro por grupo
        if ($idGrupo) {
            $query->whereHas('asignacion.grupoMateria', function ($q) use ($idGrupo) {
                $q->where('id_grupo', $idGrupo);
            });
        }

        // Ordenar por fecha descendente y hora
        $asistencias = $query->get()->sortByDesc(function ($asistencia) {
            return $asistencia->fecha->format('Y-m-d') . ' ' . 
                   ($asistencia->asignacion->horario->hora->hora_inicio ?? '00:00:00');
        });

        return $asistencias;
    }
}
