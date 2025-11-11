<?php

namespace App\Http\Controllers;

use App\Exports\PlantillaAsignacionesExport;
use App\Imports\AsignacionesImport;
use App\Models\Gestion;
use App\Models\Materia;
use App\Models\Grupo;
use App\Models\Docente;
use App\Models\Aula;
use App\Models\Dia;
use App\Models\Hora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ImportarAsignacionesController extends Controller
{
    /**
     * Mostrar la vista de importación
     */
    public function index(): Response
    {
        $gestiones = Gestion::orderBy('año', 'desc')
            ->orderBy('semestre', 'desc')
            ->get();

        return Inertia::render('Asignaciones/Importar', [
            'gestiones' => $gestiones,
        ]);
    }

    /**
     * Descargar plantilla Excel
     */
    public function descargarPlantilla(Request $request)
    {
        try {
            $request->validate([
                'id_gestion' => 'required|exists:gestion,id',
            ]);

            $gestion = Gestion::findOrFail($request->id_gestion);
            $nombreArchivo = "Plantilla_Asignaciones_{$gestion->año}_S{$gestion->semestre}.xlsx";

            // Solo pasamos la gestión, la plantilla usará ejemplos estáticos
            return Excel::download(
                new PlantillaAsignacionesExport($gestion),
                $nombreArchivo
            );
        } catch (\Exception $e) {
            \Log::error('Error al descargar plantilla: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al generar la plantilla: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Procesar archivo y mostrar preview
     */
    public function procesarArchivo(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'id_gestion' => 'required|exists:gestion,id',
            'auto_asignar' => 'boolean',
        ]);

        $idGestion = $request->id_gestion;
        $autoAsignar = $request->boolean('auto_asignar', false);

        try {
            $import = new AsignacionesImport($idGestion, $autoAsignar);
            Excel::import($import, $request->file('archivo'));

            // Guardar el import en sesión para usarlo después
            session(['import_asignaciones' => serialize($import)]);

            return response()->json([
                'success' => true,
                'tiene_errores' => $import->tieneErrores(),
                'errores' => $import->getErrores(),
                'advertencias' => $import->getAdvertencias(),
                'preview' => $import->getAsignacionesPreview(),
                'total_filas' => count($import->getAsignacionesPreview()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirmar e importar las asignaciones
     */
    public function confirmarImportacion(Request $request): JsonResponse
    {
        if (!session()->has('import_asignaciones')) {
            return response()->json([
                'success' => false,
                'message' => 'No hay datos de importación en sesión. Por favor, suba el archivo nuevamente.',
            ], 400);
        }

        try {
            $import = unserialize(session('import_asignaciones'));
            
            if ($import->tieneErrores()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede importar porque hay errores en el archivo.',
                ], 400);
            }

            $resultado = $import->importar();

            // Limpiar sesión
            session()->forget('import_asignaciones');

            if ($resultado['success']) {
                // Registrar en bitácora
                \App\Helpers\BitacoraHelper::registrar(
                    "Importación masiva de {$resultado['importadas']} asignaciones",
                    auth()->id()
                );
            }

            return response()->json($resultado);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancelar importación
     */
    public function cancelarImportacion(): JsonResponse
    {
        session()->forget('import_asignaciones');

        return response()->json([
            'success' => true,
            'message' => 'Importación cancelada',
        ]);
    }
}
