<?php

namespace App\Http\Controllers;

use App\Models\Asignacion;
use App\Models\Asistencia;
use App\Models\Docente;
use App\Services\AsistenciaService;
use App\Helpers\BitacoraHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRAsistenciaController extends Controller
{
    protected $asistenciaService;

    public function __construct(AsistenciaService $asistenciaService)
    {
        $this->asistenciaService = $asistenciaService;
    }

    /**
     * Generar código QR para una asignación
     */
    public function generar(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignacion,id',
            'duracion_minutos' => 'nullable|integer|min:30|max:360', // Entre 30 min y 6 horas
        ]);

        $user = auth()->user();
        $docente = Docente::where('id_usuario', $user->id)->firstOrFail();
        
        $asignacion = Asignacion::with(['grupoMateria.materia', 'grupoMateria.grupo', 'horario.dia', 'horario.hora'])
            ->findOrFail($request->id_asignacion);

        // Verificar que el docente sea dueño de esta asignación
        if ($asignacion->id_docente !== $docente->codigo) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para generar QR de esta asignación.',
            ], 403);
        }

        // Generar token QR
        $duracion = $request->duracion_minutos ?? 180; // 3 horas por defecto
        $token = $asignacion->generarTokenQR($duracion);

        // Crear URL para el QR (incluye el token)
        $url = route('qr.verificar', ['token' => $token]);

        // Generar imagen QR en base64
        $qrImage = base64_encode(QrCode::format('png')
            ->size(300)
            ->errorCorrection('H')
            ->generate($url));

        BitacoraHelper::registrar(
            'QR Generado',
            'asignacion',
            $asignacion->id,
            "QR generado para asignación #{$asignacion->id}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Código QR generado exitosamente.',
            'qr_image' => 'data:image/png;base64,' . $qrImage,
            'qr_url' => $url,
            'token' => $token,
            'expira_en' => now()->addMinutes($duracion)->toDateTimeString(),
            'minutos_validez' => $duracion,
        ]);
    }

    /**
     * Verificar y registrar asistencia mediante QR escaneado
     */
    public function verificar(Request $request, string $token)
    {
        try {
            // Buscar asignación por token
            $asignacion = Asignacion::where('qr_token', $token)
                ->with(['grupoMateria.materia', 'grupoMateria.grupo', 'horario.dia', 'horario.hora', 'docente.usuario'])
                ->first();

            if (!$asignacion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Código QR inválido.',
                ], 404);
            }

            // Verificar si el QR está vigente
            if (!$asignacion->qrEsValido()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El código QR ha expirado. Solicite uno nuevo al docente.',
                ], 410);
            }

            // Verificar que sea el docente correcto quien escanea
            $user = auth()->user();
            $docente = Docente::where('id_usuario', $user->id)->first();

            if (!$docente || $docente->codigo !== $asignacion->id_docente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Este código QR no pertenece a sus asignaciones.',
                ], 403);
            }

            // Registrar asistencia
            $resultado = $this->asistenciaService->registrarAsistencia(
                $docente->codigo,
                $asignacion->id,
                null,
                'Presente',
                true
            );

            if ($resultado['success']) {
                BitacoraHelper::registrar(
                    'Asistencia por QR',
                    'asistencia',
                    $resultado['asistencia']->id,
                    "Asistencia registrada vía QR para asignación #{$asignacion->id}"
                );

                return response()->json([
                    'success' => true,
                    'message' => $resultado['message'],
                    'asistencia' => $resultado['asistencia'],
                    'clase' => [
                        'materia' => $asignacion->grupoMateria->materia->nombre,
                        'grupo' => $asignacion->grupoMateria->grupo->numero,
                        'hora' => $asignacion->horario->hora->hora_inicio,
                        'dia' => $asignacion->horario->dia->nombre,
                    ],
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $resultado['message'],
            ], 400);

        } catch (\Exception $e) {
            Log::error('Error al verificar QR de asistencia: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el código QR.',
            ], 500);
        }
    }

    /**
     * Obtener información del QR sin registrar asistencia (preview)
     */
    public function info(string $token)
    {
        $asignacion = Asignacion::where('qr_token', $token)
            ->with(['grupoMateria.materia', 'grupoMateria.grupo', 'horario.dia', 'horario.hora', 'docente.usuario', 'aula'])
            ->first();

        if (!$asignacion) {
            return response()->json([
                'success' => false,
                'message' => 'Código QR inválido.',
            ], 404);
        }

        $esValido = $asignacion->qrEsValido();
        $minutosRestantes = $asignacion->minutosRestantesQR();

        return response()->json([
            'success' => true,
            'clase' => [
                'materia' => $asignacion->grupoMateria->materia->nombre,
                'codigo_materia' => $asignacion->grupoMateria->materia->codigo,
                'grupo' => $asignacion->grupoMateria->grupo->numero,
                'hora' => $asignacion->horario->hora->hora_inicio,
                'dia' => $asignacion->horario->dia->nombre,
                'aula' => $asignacion->aula->nombre,
                'docente' => $asignacion->docente->nombre . ' ' . $asignacion->docente->apellidos,
            ],
            'qr_valido' => $esValido,
            'minutos_restantes' => $minutosRestantes,
            'expira_en' => $asignacion->qr_generado_en->addMinutes($asignacion->qr_duracion_minutos)->toDateTimeString(),
        ]);
    }

    /**
     * Invalidar/eliminar QR de una asignación
     */
    public function invalidar(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignacion,id',
        ]);

        $user = auth()->user();
        $docente = Docente::where('id_usuario', $user->id)->firstOrFail();
        
        $asignacion = Asignacion::findOrFail($request->id_asignacion);

        // Verificar que el docente sea dueño de esta asignación
        if ($asignacion->id_docente !== $docente->codigo) {
            return response()->json([
                'success' => false,
                'message' => 'No tiene permiso para invalidar este QR.',
            ], 403);
        }

        $asignacion->qr_token = null;
        $asignacion->qr_generado_en = null;
        $asignacion->save();

        BitacoraHelper::registrar(
            'QR Invalidado',
            'asignacion',
            $asignacion->id,
            "QR invalidado manualmente para asignación #{$asignacion->id}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Código QR invalidado correctamente.',
        ]);
    }
}
