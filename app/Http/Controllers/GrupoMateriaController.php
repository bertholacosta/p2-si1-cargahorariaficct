<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Materia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GrupoMateriaController extends Controller
{
    /**
     * Display the resource for a specific materia.
     */
    public function index(Materia $materia)
    {
        $materia->load('grupos');
        $todosGrupos = Grupo::orderBy('nombre')->get();

        return Inertia::render('GruposMateria/Index', [
            'materia' => $materia,
            'todosGrupos' => $todosGrupos,
        ]);
    }

    /**
     * Update the grupos for a materia.
     */
    public function update(Request $request, Materia $materia)
    {
        $validated = $request->validate([
            'grupos' => ['required', 'array'],
            'grupos.*' => ['exists:grupo,id'],
        ]);

        // Sincronizar grupos de la materia
        $materia->grupos()->sync($validated['grupos']);

        return redirect()->route('grupos-materia.index', $materia->id)
                        ->with('success', 'Grupos de la materia actualizados exitosamente');
    }
}
