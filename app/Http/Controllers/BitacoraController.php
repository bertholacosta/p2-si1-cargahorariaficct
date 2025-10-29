<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BitacoraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Bitacora::with('usuario');

        // Filtros
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->where('fecha', '<=', $request->fecha_fin . ' 23:59:59');
        }

        if ($request->has('usuario') && $request->usuario) {
            $query->where('id_usuario', $request->usuario);
        }

        if ($request->has('busqueda') && $request->busqueda) {
            $query->where('accion', 'ILIKE', '%' . $request->busqueda . '%');
        }

        // Ordenar por fecha descendente
        $registros = $query->orderBy('fecha', 'desc')
            ->paginate(50)
            ->through(function ($registro) {
                return [
                    'id' => $registro->id,
                    'accion' => $registro->accion,
                    'fecha' => $registro->fecha->format('d/m/Y H:i:s'),
                    'ip' => $registro->ip,
                    'usuario_nombre' => $registro->usuario ? $registro->usuario->nombre : 'Usuario eliminado',
                ];
            });

        return Inertia::render('Bitacora/Index', [
            'registros' => $registros,
            'filtros' => [
                'fecha_inicio' => $request->fecha_inicio,
                'fecha_fin' => $request->fecha_fin,
                'usuario' => $request->usuario,
                'busqueda' => $request->busqueda,
            ],
        ]);
    }

    /**
     * Exportar bitácora a CSV
     */
    public function exportar(Request $request)
    {
        $query = Bitacora::with('usuario');

        // Aplicar los mismos filtros que en index
        if ($request->has('fecha_inicio') && $request->fecha_inicio) {
            $query->where('fecha', '>=', $request->fecha_inicio);
        }

        if ($request->has('fecha_fin') && $request->fecha_fin) {
            $query->where('fecha', '<=', $request->fecha_fin . ' 23:59:59');
        }

        if ($request->has('usuario') && $request->usuario) {
            $query->where('id_usuario', $request->usuario);
        }

        if ($request->has('busqueda') && $request->busqueda) {
            $query->where('accion', 'ILIKE', '%' . $request->busqueda . '%');
        }

        $registros = $query->orderBy('fecha', 'desc')->get();

        $csv = "ID,Fecha,Usuario,IP,Acción\n";
        
        foreach ($registros as $registro) {
            $csv .= sprintf(
                "%d,%s,%s,%s,\"%s\"\n",
                $registro->id,
                $registro->fecha->format('d/m/Y H:i:s'),
                $registro->usuario ? $registro->usuario->nombre : 'Usuario eliminado',
                $registro->ip ?? '',
                str_replace('"', '""', $registro->accion)
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="bitacora_' . date('Y-m-d_His') . '.csv"',
        ]);
    }
}
