<?php

namespace App\Http\Controllers;

use App\Models\DiaNoLaborable;
use App\Models\Gestion;
use App\Helpers\BitacoraHelper;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiaNoLaborableController extends Controller
{
    /**
     * Mostrar lista de días no laborables
     */
    public function index(Request $request)
    {
        $idGestion = $request->input('id_gestion');
        
        $query = DiaNoLaborable::with('gestion')
            ->orderBy('fecha', 'desc');
        
        if ($idGestion) {
            $query->where(function ($q) use ($idGestion) {
                $q->where('id_gestion', $idGestion)
                  ->orWhereNull('id_gestion');
            });
        }
        
        $diasNoLaborables = $query->get();
        $gestiones = Gestion::orderBy('año', 'desc')->orderBy('semestre', 'desc')->get();
        
        return Inertia::render('DiasNoLaborables/Index', [
            'diasNoLaborables' => $diasNoLaborables,
            'gestiones' => $gestiones,
        ]);
    }

    /**
     * Crear nuevo día no laborable
     */
    public function store(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|in:Feriado,Vacación,Suspensión,Otro',
            'id_gestion' => 'nullable|exists:gestion,id',
            'activo' => 'boolean',
        ]);
        
        $diaNoLaborable = DiaNoLaborable::create($request->all());
        
        BitacoraHelper::registrar(
            'Día No Laborable Creado',
            'dia_no_laborable',
            $diaNoLaborable->id,
            "Día no laborable registrado: {$diaNoLaborable->descripcion} - {$diaNoLaborable->fecha}"
        );
        
        return redirect()->back()->with('success', 'Día no laborable registrado correctamente.');
    }

    /**
     * Actualizar día no laborable
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'fecha' => 'required|date',
            'descripcion' => 'required|string|max:255',
            'tipo' => 'required|in:Feriado,Vacación,Suspensión,Otro',
            'id_gestion' => 'nullable|exists:gestion,id',
            'activo' => 'boolean',
        ]);
        
        $diaNoLaborable = DiaNoLaborable::findOrFail($id);
        $diaNoLaborable->update($request->all());
        
        BitacoraHelper::registrar(
            'Día No Laborable Actualizado',
            'dia_no_laborable',
            $diaNoLaborable->id,
            "Día no laborable actualizado: {$diaNoLaborable->descripcion}"
        );
        
        return redirect()->back()->with('success', 'Día no laborable actualizado correctamente.');
    }

    /**
     * Eliminar día no laborable
     */
    public function destroy($id)
    {
        $diaNoLaborable = DiaNoLaborable::findOrFail($id);
        
        BitacoraHelper::registrar(
            'Día No Laborable Eliminado',
            'dia_no_laborable',
            $diaNoLaborable->id,
            "Día no laborable eliminado: {$diaNoLaborable->descripcion} - {$diaNoLaborable->fecha}"
        );
        
        $diaNoLaborable->delete();
        
        return redirect()->back()->with('success', 'Día no laborable eliminado correctamente.');
    }

    /**
     * Obtener días no laborables de un mes (para calendario)
     */
    public function delMes(Request $request)
    {
        $request->validate([
            'año' => 'required|integer|min:2020|max:2100',
            'mes' => 'required|integer|min:1|max:12',
            'id_gestion' => 'nullable|exists:gestion,id',
        ]);
        
        $diasNoLaborables = DiaNoLaborable::delMes(
            $request->año,
            $request->mes,
            $request->id_gestion
        );
        
        return response()->json([
            'dias' => $diasNoLaborables,
        ]);
    }
}
