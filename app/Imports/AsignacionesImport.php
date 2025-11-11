<?php

namespace App\Imports;

use App\Models\Asignacion;
use App\Models\Materia;
use App\Models\Grupo;
use App\Models\GrupoMateria;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Dia;
use App\Models\Hora;
use App\Models\Horario;
use App\Models\Modulo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AsignacionesImport implements ToCollection, WithHeadingRow
{
    protected $idGestion;
    protected $errores = [];
    protected $advertencias = [];
    protected $asignacionesPreview = [];
    protected $autoAsignar = false;

    public function __construct(int $idGestion, bool $autoAsignar = false)
    {
        $this->idGestion = $idGestion;
        $this->autoAsignar = $autoAsignar;
    }

    public function collection(Collection $rows)
    {
        $fila = 5; // Comenzar desde la fila 5 (después de encabezados y ejemplos)

        foreach ($rows as $row) {
            // Saltar filas vacías
            if ($this->esFilaVacia($row)) {
                $fila++;
                continue;
            }

            try {
                $resultado = $this->procesarFila($row, $fila);
                
                if ($resultado['valido']) {
                    $this->asignacionesPreview[] = $resultado['datos'];
                    
                    if (!empty($resultado['advertencias'])) {
                        $this->advertencias[] = [
                            'fila' => $fila,
                            'mensajes' => $resultado['advertencias'],
                        ];
                    }
                } else {
                    $this->errores[] = [
                        'fila' => $fila,
                        'errores' => $resultado['errores'],
                    ];
                }
            } catch (\Exception $e) {
                $this->errores[] = [
                    'fila' => $fila,
                    'errores' => ["Error inesperado: {$e->getMessage()}"],
                ];
            }

            $fila++;
        }
    }

    /**
     * Procesar una fila individual
     */
    private function procesarFila($row, int $numeroFila): array
    {
        $errores = [];
        $advertencias = [];
        $datos = [
            'fila' => $numeroFila,
            'codigo_materia' => $row['codigo_materia'] ?? '',
            'numero_grupo' => $row['numero_grupo'] ?? '',
            'codigo_docente' => $row['codigo_docente'] ?? '',
            'dia' => $row['dia'] ?? '',
            'hora_inicio' => $row['hora_inicio'] ?? '',
            'hora_fin' => $row['hora_fin'] ?? '',
            'codigo_aula' => $row['codigo_aula'] ?? '',
        ];

        // Validar materia
        $materia = Materia::where('codigo', $datos['codigo_materia'])->first();
        if (!$materia) {
            $errores[] = "Materia '{$datos['codigo_materia']}' no encontrada";
        } else {
            $datos['materia'] = $materia;
        }

        // Validar grupo
        $grupo = Grupo::where('numero', $datos['numero_grupo'])->first();
        if (!$grupo) {
            $errores[] = "Grupo '{$datos['numero_grupo']}' no encontrado";
        } else {
            $datos['grupo'] = $grupo;
        }

        // Obtener o crear GrupoMateria
        if ($materia && $grupo) {
            $grupoMateria = GrupoMateria::where('id_materia', $materia->id)
                ->where('id_grupo', $grupo->id)
                ->where('id_gestion', $this->idGestion)
                ->first();

            if (!$grupoMateria) {
                $errores[] = "No existe la combinación Materia-Grupo para esta gestión";
            } else {
                $datos['grupo_materia'] = $grupoMateria;
            }
        }

        // Validar y asignar docente
        if (strtoupper($datos['codigo_docente']) === 'AUTO') {
            if ($this->autoAsignar && $materia) {
                $docente = $this->asignarDocenteAutomatico($materia);
                
                if ($docente) {
                    $datos['docente'] = $docente;
                    $datos['codigo_docente'] = $docente->codigo;
                    $advertencias[] = "Docente asignado automáticamente: {$docente->nombre} {$docente->apellidos}";
                } else {
                    $errores[] = "No se encontró docente habilitado para la materia '{$materia->nombre}'";
                }
            } else {
                $errores[] = "Asignación automática no habilitada. Especifique un código de docente";
            }
        } else {
            $docente = Docente::where('codigo', $datos['codigo_docente'])->first();
            
            if (!$docente) {
                $errores[] = "Docente '{$datos['codigo_docente']}' no encontrado";
            } else {
                // Verificar que el docente esté habilitado para la materia
                if ($materia && !$docente->materias->contains($materia->id)) {
                    $advertencias[] = "El docente no está habilitado para '{$materia->nombre}', pero se permitirá la asignación";
                }
                $datos['docente'] = $docente;
            }
        }

        // Procesar múltiples días (separados por comas)
        $diasTexto = $datos['dia'];
        $diasArray = array_map('trim', explode(',', $diasTexto));
        $diasObjetos = [];
        
        foreach ($diasArray as $nombreDia) {
            if (empty($nombreDia)) continue;
            
            $dia = Dia::where('nombre', $nombreDia)->first();
            if (!$dia) {
                $errores[] = "Día '{$nombreDia}' no válido. Use: Lunes, Martes, Miércoles, Jueves, Viernes, Sábado";
            } else {
                $diasObjetos[] = $dia;
            }
        }
        
        if (empty($diasObjetos) && empty($errores)) {
            $errores[] = "Debe especificar al menos un día válido";
        }
        
        $datos['dias'] = $diasObjetos;
        $datos['cantidad_dias'] = count($diasObjetos);

        // Validar hora inicio
        $hora = Hora::where('hora_inicio', $datos['hora_inicio'])->first();
        if (!$hora) {
            $errores[] = "Hora de inicio '{$datos['hora_inicio']}' no encontrada en el sistema";
        } else {
            $datos['hora'] = $hora;
        }

        // Validar hora fin y calcular módulos
        if (!empty($datos['hora_fin'])) {
            $horaFin = Hora::where('hora_inicio', $datos['hora_fin'])->first();
            if (!$horaFin) {
                $errores[] = "Hora de fin '{$datos['hora_fin']}' no encontrada en el sistema";
            } else {
                $datos['hora_fin_obj'] = $horaFin;
                
                // Calcular módulos desde hora_inicio hasta hora_fin
                if ($hora && $horaFin) {
                    try {
                        $inicio = \Carbon\Carbon::parse($datos['hora_inicio']);
                        $fin = \Carbon\Carbon::parse($datos['hora_fin']);
                        $minutos = $inicio->diffInMinutes($fin);
                        $modulos = ceil($minutos / 45);
                        
                        if ($modulos < 1 || $modulos > 8) {
                            $errores[] = "El rango de horas resulta en {$modulos} módulos. Debe ser entre 1 y 8";
                        } else {
                            $datos['modulos'] = $modulos;
                        }
                    } catch (\Exception $e) {
                        $errores[] = "Error al calcular módulos: formato de hora inválido";
                    }
                }
            }
        } else {
            $errores[] = "Debe especificar HORA_FIN";
        }

        // Validar aula
        $aula = Aula::where('nombre', $datos['codigo_aula'])->first();
        if (!$aula) {
            $errores[] = "Aula '{$datos['codigo_aula']}' no encontrada";
        } else {
            $datos['aula'] = $aula;
        }

        // Verificar disponibilidad de aula para cada día (solo si todo lo demás es válido)
        if (empty($errores) && $aula && !empty($diasObjetos) && $hora) {
            foreach ($diasObjetos as $dia) {
                if ($this->aulaOcupada($aula->id, $dia->id, $hora->id)) {
                    $advertencias[] = "El aula '{$aula->nombre}' ya está ocupada el {$dia->nombre} a las {$datos['hora_inicio']}";
                }
            }
        }

        return [
            'valido' => empty($errores),
            'errores' => $errores,
            'advertencias' => $advertencias,
            'datos' => $datos,
        ];
    }

    /**
     * Asignar docente automáticamente basado en materias habilitadas
     */
    private function asignarDocenteAutomatico(Materia $materia): ?Docente
    {
        // Buscar docentes que tengan esta materia habilitada
        $docentes = Docente::whereHas('materias', function ($query) use ($materia) {
            $query->where('materia.id', $materia->id);
        })->get();

        if ($docentes->isEmpty()) {
            return null;
        }

        // Si hay varios docentes, elegir el que tenga menos asignaciones en esta gestión
        $docenteOptimo = $docentes->sortBy(function ($docente) {
            return Asignacion::where('id_docente', $docente->codigo)
                ->where('id_gestion', $this->idGestion)
                ->count();
        })->first();

        return $docenteOptimo;
    }

    /**
     * Verificar si un aula está ocupada en un horario
     */
    private function aulaOcupada(int $idAula, int $idDia, int $idHora): bool
    {
        return Asignacion::where('id_aula', $idAula)
            ->where('id_gestion', $this->idGestion)
            ->whereHas('horario', function ($query) use ($idDia, $idHora) {
                $query->where('id_dia', $idDia)
                    ->where('id_hora', $idHora);
            })
            ->exists();
    }

    /**
     * Verificar si una fila está vacía
     */
    private function esFilaVacia($row): bool
    {
        $valores = array_values($row->toArray());
        return empty(array_filter($valores, fn($val) => !empty($val)));
    }

    /**
     * Importar las asignaciones validadas
     */
    public function importar(): array
    {
        $importadas = 0;
        $erroresImportacion = [];

        DB::beginTransaction();

        try {
            foreach ($this->asignacionesPreview as $datos) {
                // Crear asignación para cada día especificado
                foreach ($datos['dias'] as $dia) {
                    // Crear u obtener horario
                    $horario = Horario::firstOrCreate([
                        'id_dia' => $dia->id,
                        'id_hora' => $datos['hora']->id,
                        'id_modulo' => 1, // Por defecto módulo 1
                    ]);

                    // Crear asignación
                    Asignacion::create([
                        'id_gestion' => $this->idGestion,
                        'id_docente' => $datos['docente']->codigo,
                        'id_grupo_materia' => $datos['grupo_materia']->id,
                        'id_aula' => $datos['aula']->id,
                        'id_horario' => $horario->id,
                    ]);

                    $importadas++;
                }
            }

            DB::commit();

            return [
                'success' => true,
                'importadas' => $importadas,
                'errores' => $erroresImportacion,
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'importadas' => 0,
                'errores' => ["Error al importar: {$e->getMessage()}"],
            ];
        }
    }

    /**
     * Obtener errores de validación
     */
    public function getErrores(): array
    {
        return $this->errores;
    }

    /**
     * Obtener advertencias
     */
    public function getAdvertencias(): array
    {
        return $this->advertencias;
    }

    /**
     * Obtener preview de asignaciones
     */
    public function getAsignacionesPreview(): array
    {
        return $this->asignacionesPreview;
    }

    /**
     * Verificar si hay errores
     */
    public function tieneErrores(): bool
    {
        return !empty($this->errores);
    }

    public function headingRow(): int
    {
        return 4; // Los encabezados están en la fila 4
    }
}
