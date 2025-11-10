<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Docente;
use App\Models\GrupoMateria;
use App\Models\Horario;
use App\Models\Aula;
use App\Models\Gestion;
use App\Models\Dia;
use App\Models\Hora;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class AsignacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtener gestión seleccionada o la más reciente
        $gestionId = $request->input('gestion_id');
        
        if (!$gestionId) {
            $gestionActual = Gestion::orderBy('año', 'desc')
                ->orderBy('semestre', 'desc')
                ->first();
            $gestionId = $gestionActual?->id;
        }

        // Cargar datos necesarios
        $gestiones = Gestion::orderBy('año', 'desc')
            ->orderBy('semestre', 'desc')
            ->get();

        $docentes = Docente::with('usuario')
            ->orderBy('apellidos')
            ->get()
            ->map(function ($docente) {
                return [
                    'codigo' => $docente->codigo,
                    'nombre_completo' => $docente->apellidos . ' ' . $docente->nombre,
                ];
            });

        $aulas = Aula::with('modulo')
            ->get()
            ->map(function ($aula) {
                return [
                    'id' => $aula->id,
                    'nombre' => "Módulo {$aula->numero_modulo} - Aula {$aula->numero}",
                    'numero_modulo' => $aula->numero_modulo,
                    'numero' => $aula->numero,
                ];
            });

        // Obtener días y horas ordenados
        $dias = Dia::orderBy('id')->get();
        $horas = Hora::orderBy('hora_inicio')->get();

        // Obtener horarios (combinación día-hora)
        $horarios = Horario::with(['dia', 'hora'])
            ->get()
            ->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'id_dia' => $horario->id_dia,
                    'id_hora' => $horario->id_hora,
                    'dia_nombre' => $horario->dia->nombre,
                    'hora_inicio' => $horario->hora->hora_inicio,
                    'hora_fin' => $horario->hora->hora_fin,
                ];
            });

        // Obtener grupos-materias con información completa
        $gruposMaterias = GrupoMateria::with(['grupo', 'materia'])
            ->get()
            ->map(function ($gm) {
                return [
                    'id' => $gm->id,
                    'nombre_completo' => "{$gm->materia->nombre} - {$gm->grupo->nombre}",
                    'materia_nombre' => $gm->materia->nombre,
                    'grupo_nombre' => $gm->grupo->nombre,
                    'carga_horaria' => $gm->materia->carga_horaria,
                ];
            });

        // Obtener asignaciones de la gestión seleccionada
        $asignaciones = [];
        if ($gestionId) {
            $asignaciones = Asignacion::with([
                    'docente',
                    'grupoMateria.grupo',
                    'grupoMateria.materia',
                    'horario.dia',
                    'horario.hora',
                    'aula.modulo',
                    'gestion'
                ])
                ->where('id_gestion', $gestionId)
                ->get()
                ->map(function ($asignacion) {
                    return [
                        'id' => $asignacion->id,
                        'id_docente' => $asignacion->id_docente,
                        'id_grupo_materia' => $asignacion->id_grupo_materia,
                        'id_horario' => $asignacion->id_horario,
                        'id_aula' => $asignacion->id_aula,
                        'id_gestion' => $asignacion->id_gestion,
                        'docente_nombre' => $asignacion->docente->apellidos . ' ' . $asignacion->docente->nombre,
                        'materia_nombre' => $asignacion->grupoMateria->materia->nombre,
                        'grupo_nombre' => $asignacion->grupoMateria->grupo->nombre,
                        'dia_id' => $asignacion->horario->id_dia,
                        'dia_nombre' => $asignacion->horario->dia->nombre,
                        'hora_id' => $asignacion->horario->id_hora,
                        'hora_inicio' => $asignacion->horario->hora->hora_inicio,
                        'hora_fin' => $asignacion->horario->hora->hora_fin,
                        'aula_nombre' => "M{$asignacion->aula->numero_modulo}-A{$asignacion->aula->numero}",
                        'carga_horaria' => $asignacion->grupoMateria->materia->carga_horaria,
                    ];
                });
        }

        return Inertia::render('Asignaciones/Index', [
            'gestiones' => $gestiones,
            'gestionSeleccionada' => $gestionId,
            'docentes' => $docentes,
            'aulas' => $aulas,
            'dias' => $dias,
            'horas' => $horas,
            'horarios' => $horarios,
            'gruposMaterias' => $gruposMaterias,
            'asignaciones' => $asignaciones,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_docente' => 'required|exists:docente,codigo',
            'id_grupo_materia' => 'required|exists:grupo_materia,id',
            'id_horario' => 'required|exists:horario,id',
            'id_aula' => 'required|exists:aula,id',
            'id_gestion' => 'required|exists:gestion,id',
        ]);

        // Verificar conflictos de horario
        $conflictos = $this->verificarConflictos($validated);
        
        if (!empty($conflictos)) {
            return back()->withErrors(['conflicto' => $conflictos[0]]);
        }

        Asignacion::create($validated);

        return redirect()->route('asignaciones.index', ['gestion_id' => $validated['id_gestion']])
            ->with('success', 'Asignación creada exitosamente');
    }

    /**
     * Crear múltiples asignaciones con rango de horarios
     */
    public function storeMultiple(Request $request)
    {
        $validated = $request->validate([
            'id_docente' => 'required|exists:docente,codigo',
            'id_grupo_materia' => 'required|exists:grupo_materia,id',
            'id_gestion' => 'required|exists:gestion,id',
            'bloques' => 'required|array|min:1',
            'bloques.*.id_dia' => 'required|exists:dia,id',
            'bloques.*.id_hora_inicio' => 'required|exists:hora,id',
            'bloques.*.id_hora_fin' => 'required|exists:hora,id',
            'bloques.*.id_aula' => 'required|exists:aula,id',
        ]);

        $asignacionesCreadas = 0;
        $errores = [];

        DB::beginTransaction();
        try {
            foreach ($validated['bloques'] as $index => $bloque) {
                // Obtener las horas de inicio y fin
                $horaInicio = Hora::findOrFail($bloque['id_hora_inicio']);
                $horaFin = Hora::findOrFail($bloque['id_hora_fin']);

                // Validar que la hora de fin sea posterior a la de inicio
                if ($horaFin->hora_inicio <= $horaInicio->hora_inicio) {
                    $errores[] = "Bloque " . ($index + 1) . ": La hora de fin debe ser posterior a la hora de inicio";
                    continue;
                }

                // Obtener todos los bloques horarios consecutivos entre inicio y fin
                $horasConsecutivas = Hora::where('hora_inicio', '>=', $horaInicio->hora_inicio)
                    ->where('hora_fin', '<=', $horaFin->hora_fin)
                    ->orderBy('hora_inicio')
                    ->get();

                if ($horasConsecutivas->isEmpty()) {
                    $errores[] = "Bloque " . ($index + 1) . ": No se encontraron bloques horarios en el rango especificado";
                    continue;
                }

                // Crear asignaciones para cada bloque horario consecutivo
                foreach ($horasConsecutivas as $hora) {
                    // Buscar o crear el horario (día + hora)
                    $horario = Horario::firstOrCreate([
                        'id_dia' => $bloque['id_dia'],
                        'id_hora' => $hora->id,
                    ]);

                    // Verificar conflictos
                    $datosAsignacion = [
                        'id_docente' => $validated['id_docente'],
                        'id_grupo_materia' => $validated['id_grupo_materia'],
                        'id_horario' => $horario->id,
                        'id_aula' => $bloque['id_aula'],
                        'id_gestion' => $validated['id_gestion'],
                    ];

                    $conflictos = $this->verificarConflictos($datosAsignacion);

                    if (!empty($conflictos)) {
                        $dia = Dia::find($bloque['id_dia']);
                        $errores[] = "Bloque " . ($index + 1) . " ({$dia->nombre} {$hora->hora_inicio}-{$hora->hora_fin}): " . $conflictos[0];
                        DB::rollBack();
                        return back()->withErrors(['conflicto' => implode(' | ', $errores)]);
                    }

                    // Crear la asignación
                    Asignacion::create($datosAsignacion);
                    $asignacionesCreadas++;
                }
            }

            if (!empty($errores)) {
                DB::rollBack();
                return back()->withErrors(['conflicto' => implode(' | ', $errores)]);
            }

            DB::commit();

            return redirect()->route('asignaciones.index', ['gestion_id' => $validated['id_gestion']])
                ->with('success', "$asignacionesCreadas asignaciones creadas exitosamente");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['conflicto' => 'Error al crear las asignaciones: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asignacion $asignacione)
    {
        $gestionId = $asignacione->id_gestion;
        $asignacione->delete();

        return redirect()->route('asignaciones.index', ['gestion_id' => $gestionId])
            ->with('success', 'Asignación eliminada exitosamente');
    }

    /**
     * Verificar conflictos de horario
     */
    private function verificarConflictos(array $data): array
    {
        $conflictos = [];

        // Conflicto 1: Docente ya tiene clase en ese horario
        $conflictoDocente = Asignacion::where('id_docente', $data['id_docente'])
            ->where('id_horario', $data['id_horario'])
            ->where('id_gestion', $data['id_gestion'])
            ->with(['grupoMateria.materia', 'grupoMateria.grupo'])
            ->first();

        if ($conflictoDocente) {
            $conflictos[] = "El docente ya tiene asignada la materia {$conflictoDocente->grupoMateria->materia->nombre} - {$conflictoDocente->grupoMateria->grupo->nombre} en este horario";
        }

        // Conflicto 2: Aula ya está ocupada en ese horario
        $conflictoAula = Asignacion::where('id_aula', $data['id_aula'])
            ->where('id_horario', $data['id_horario'])
            ->where('id_gestion', $data['id_gestion'])
            ->with(['grupoMateria.materia', 'grupoMateria.grupo', 'docente'])
            ->first();

        if ($conflictoAula) {
            $conflictos[] = "El aula ya está ocupada con {$conflictoAula->grupoMateria->materia->nombre} - {$conflictoAula->grupoMateria->grupo->nombre}";
        }

        // Conflicto 3: Grupo ya tiene clase en ese horario
        $conflictoGrupo = Asignacion::where('id_grupo_materia', $data['id_grupo_materia'])
            ->where('id_horario', $data['id_horario'])
            ->where('id_gestion', $data['id_gestion'])
            ->first();

        if ($conflictoGrupo) {
            $conflictos[] = "Este grupo-materia ya tiene asignado este horario";
        }

        return $conflictos;
    }

    /**
     * Obtener horario de un docente
     */
    public function horarioDocente(Request $request, $codigo)
    {
        $gestionId = $request->input('gestion_id');
        
        $asignaciones = Asignacion::with([
                'grupoMateria.grupo',
                'grupoMateria.materia',
                'horario.dia',
                'horario.hora',
                'aula.modulo'
            ])
            ->where('id_docente', $codigo)
            ->where('id_gestion', $gestionId)
            ->get();

        return response()->json($asignaciones);
    }

    /**
     * Obtener horario de un aula
     */
    public function horarioAula(Request $request, $id)
    {
        $gestionId = $request->input('gestion_id');
        
        $asignaciones = Asignacion::with([
                'docente',
                'grupoMateria.grupo',
                'grupoMateria.materia',
                'horario.dia',
                'horario.hora'
            ])
            ->where('id_aula', $id)
            ->where('id_gestion', $gestionId)
            ->get();

        return response()->json($asignaciones);
    }
}
