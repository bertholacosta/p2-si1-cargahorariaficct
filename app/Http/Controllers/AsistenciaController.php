<?php

namespace App\Http\Controllers;

use App\Models\Asistencia;
use App\Models\Gestion;
use App\Models\Docente;
use App\Services\AsistenciaService;
use App\Helpers\BitacoraHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class AsistenciaController extends Controller
{
    protected $asistenciaService;

    public function __construct(AsistenciaService $asistenciaService)
    {
        $this->asistenciaService = $asistenciaService;
    }

    /**
     * Mostrar vista principal de asistencias
     * Docentes ven solo sus asistencias, Admin ve todo
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $esAdmin = $user->rol->nombre === 'Administrador';
        
        // Obtener gestión activa o última
        $gestionActual = Gestion::orderBy('año', 'desc')
            ->orderBy('semestre', 'desc')
            ->first();
        
        $idGestion = $request->input('id_gestion', $gestionActual->id ?? null);
        
        if ($esAdmin) {
            // Admin puede ver de todos los docentes
            $docentes = Docente::with('usuario')->get();
            $codigoDocente = $request->input('codigo_docente');
            
            // Formatear gestiones con label y fechas
            $gestiones = Gestion::orderBy('año', 'desc')
                ->orderBy('semestre', 'desc')
                ->get()
                ->map(function ($gestion) {
                    return [
                        'id' => $gestion->id,
                        'label' => $gestion->nombre_completo,
                        'fecha_inicio' => $gestion->fecha_inicio->format('Y-m-d'),
                        'fecha_fin' => $gestion->fecha_fin->format('Y-m-d'),
                        'año' => $gestion->año,
                        'semestre' => $gestion->semestre,
                    ];
                });
            
            return Inertia::render('Asistencias/IndexAdmin', [
                'docentes' => $docentes,
                'gestiones' => $gestiones,
                'gestionActual' => $gestionActual,
            ]);
        } else {
            // Docente solo ve sus propias asistencias
            $docente = Docente::where('id_usuario', $user->id)->first();
            
            if (!$docente) {
                return redirect()->back()->with('error', 'No se encontró información del docente.');
            }
            
            // Obtener clases del día actual
            $clasesHoy = $this->asistenciaService->obtenerClasesDelDia($docente->codigo);
            
            // Obtener estadísticas
            $estadisticas = $this->asistenciaService->generarEstadisticas(
                $docente->codigo,
                $idGestion
            );
            
            // Obtener historial de asistencias del mes actual
            $fechaInicio = Carbon::now()->startOfMonth();
            $fechaFin = Carbon::now()->endOfMonth();
            
            $asistencias = $this->asistenciaService->obtenerAsistenciasDocente(
                $docente->codigo,
                $fechaInicio,
                $fechaFin,
                $idGestion
            );
            
            // Formatear gestiones con label para el dropdown
            $gestiones = Gestion::orderBy('año', 'desc')
                ->orderBy('semestre', 'desc')
                ->get()
                ->map(function ($gestion) {
                    return [
                        'id' => $gestion->id,
                        'label' => $gestion->nombre_completo,
                        'fecha_inicio' => $gestion->fecha_inicio->format('Y-m-d'),
                        'fecha_fin' => $gestion->fecha_fin->format('Y-m-d'),
                        'año' => $gestion->año,
                        'semestre' => $gestion->semestre,
                    ];
                });
            
            return Inertia::render('Asistencias/IndexDocente', [
                'docente' => $docente,
                'clasesHoy' => $clasesHoy,
                'estadisticas' => $estadisticas,
                'asistencias' => $asistencias,
                'gestionActual' => $gestionActual,
                'gestiones' => $gestiones,
            ]);
        }
    }

    /**
     * Registrar asistencia (Docente)
     */
    public function registrar(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required_without:ids_asignaciones',
            'ids_asignaciones' => 'required_without:id_asignacion|array',
            'ids_asignaciones.*' => 'exists:asignacion,id',
            'estado' => 'nullable|in:Presente,Retraso',
        ]);
        
        $user = auth()->user();
        $docente = Docente::where('id_usuario', $user->id)->firstOrFail();
        
        $estado = $request->estado ?? 'Presente';
        
        // Determinar si es una sola asignación o múltiples (grupo)
        $idsAsignaciones = $request->ids_asignaciones ?? [$request->id_asignacion];
        
        $errores = [];
        $exitosos = 0;
        $asistenciaIds = [];
        
        foreach ($idsAsignaciones as $idAsignacion) {
            $resultado = $this->asistenciaService->registrarAsistencia(
                $docente->codigo,
                $idAsignacion,
                null,
                $estado,
                true // Es docente quien registra
            );
            
            if ($resultado['success']) {
                $exitosos++;
                $asistenciaIds[] = $resultado['asistencia']->id;
            } else {
                $errores[] = $resultado['message'];
            }
        }
        
        if ($exitosos > 0) {
            $mensaje = $exitosos === 1 
                ? "Asistencia registrada como '{$estado}' correctamente." 
                : "Asistencia registrada como '{$estado}' para {$exitosos} bloques de clase.";
            
            BitacoraHelper::registrar(
                'Asistencia Registrada',
                'asistencia',
                $asistenciaIds[0],
                $mensaje . " IDs: " . implode(', ', $idsAsignaciones)
            );
            
            return redirect()->back()->with('success', $mensaje);
        }
        
        return redirect()->back()->with('error', 'Error al registrar: ' . implode(', ', $errores));
    }

    /**
     * Obtener reporte de asistencias (Admin)
     */
    public function reporte(Request $request)
    {
        $request->validate([
            'id_gestion' => 'required|exists:gestion,id',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'codigo_docente' => 'nullable|exists:docente,codigo',
        ]);
        
        $fechaInicio = $request->fecha_inicio ? Carbon::parse($request->fecha_inicio) : null;
        $fechaFin = $request->fecha_fin ? Carbon::parse($request->fecha_fin) : null;
        
        $asistencias = $this->asistenciaService->obtenerReporteGeneral(
            $request->id_gestion,
            $fechaInicio,
            $fechaFin,
            $request->codigo_docente
        );
        
        return response()->json([
            'asistencias' => $asistencias,
        ]);
    }

    /**
     * Actualizar asistencia manualmente (Admin)
     */
    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'estado' => 'required|in:Presente,Falta,Licencia,Justificada',
            'observacion' => 'nullable|string|max:500',
        ]);
        
        $asistencia = Asistencia::findOrFail($id);
        
        $asistencia->update([
            'estado' => $request->estado,
            'observacion' => $request->observacion,
        ]);
        
        BitacoraHelper::registrar(
            'Asistencia Actualizada',
            'asistencia',
            $asistencia->id,
            "Estado cambiado a: {$request->estado}"
        );
        
        return redirect()->back()->with('success', 'Asistencia actualizada correctamente.');
    }

    /**
     * Justificar falta
     */
    public function justificar(Request $request)
    {
        $request->validate([
            'id_asistencia' => 'required|exists:asistencia,id',
            'descripcion' => 'required|string|max:1000',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB máximo
        ]);
        
        // Verificar que la asistencia pertenece al docente actual (si no es admin)
        $user = auth()->user();
        $asistencia = Asistencia::findOrFail($request->id_asistencia);
        $esAdmin = $user->rol->permisos->contains('slug', 'asistencias.gestionar');
        
        if (!$esAdmin) {
            $docente = Docente::where('id_usuario', $user->id)->first();
            if (!$docente || $asistencia->id_docente !== $docente->codigo) {
                return redirect()->back()->with('error', 'No puedes justificar la falta de otro docente.');
            }
        }
        
        $archivo = null;
        if ($request->hasFile('archivo')) {
            $archivo = $request->file('archivo')->store('justificaciones', 'public');
        }
        
        $resultado = $this->asistenciaService->justificarFalta(
            $request->id_asistencia,
            $request->descripcion,
            auth()->id(),
            $archivo
        );
        
        if ($resultado['success']) {
            BitacoraHelper::registrar(
                'Falta Justificada',
                'justificacion',
                $resultado['justificacion']->id,
                "Justificación registrada para asistencia #{$request->id_asistencia}"
            );
            
            return redirect()->back()->with('success', $resultado['message']);
        }
        
        return redirect()->back()->with('error', $resultado['message']);
    }

    /**
     * Obtener estadísticas de un docente
     */
    public function estadisticas(Request $request, $codigoDocente)
    {
        $request->validate([
            'id_gestion' => 'required|exists:gestion,id',
        ]);
        
        $user = auth()->user();
        $esAdmin = $user->rol->permisos->contains('slug', 'asistencias.ver_todas');
        
        // Si no es admin, solo puede ver sus propias estadísticas
        if (!$esAdmin) {
            $docente = Docente::where('id_usuario', $user->id)->first();
            if (!$docente || $docente->codigo !== $codigoDocente) {
                return response()->json([
                    'error' => 'No tienes permiso para ver las estadísticas de otro docente.'
                ], 403);
            }
        }
        
        $estadisticas = $this->asistenciaService->generarEstadisticas(
            $codigoDocente,
            $request->id_gestion
        );
        
        return response()->json($estadisticas);
    }

    /**
     * Registrar faltas automáticas (Comando programado)
     */
    public function registrarFaltasAutomaticas(Request $request)
    {
        $fecha = $request->input('fecha') ? Carbon::parse($request->input('fecha')) : Carbon::now();
        $idGestion = $request->input('id_gestion');
        
        $faltasRegistradas = $this->asistenciaService->registrarFaltasAutomaticas($fecha, $idGestion);
        
        BitacoraHelper::registrar(
            'Faltas Automáticas Registradas',
            'asistencia',
            null,
            "Se registraron {$faltasRegistradas} faltas automáticas para la fecha {$fecha->format('Y-m-d')}"
        );
        
        return response()->json([
            'success' => true,
            'faltas_registradas' => $faltasRegistradas,
            'message' => "Se registraron {$faltasRegistradas} faltas automáticas.",
        ]);
    }
}
