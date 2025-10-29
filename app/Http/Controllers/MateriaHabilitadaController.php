<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Materia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MateriaHabilitadaController extends Controller
{
    /**
     * Display the resource for a specific docente.
     */
    public function index(string $codigoDocente)
    {
        $docente = Docente::with('materias')->findOrFail($codigoDocente);
        $todasMaterias = Materia::orderBy('sigla')->get();

        return Inertia::render('MateriasHabilitadas/Index', [
            'docente' => $docente,
            'todasMaterias' => $todasMaterias,
        ]);
    }

    /**
     * Update the materias habilitadas for a docente.
     */
    public function update(Request $request, string $codigoDocente)
    {
        $docente = Docente::findOrFail($codigoDocente);

        $validated = $request->validate([
            'materias' => ['required', 'array'],
            'materias.*' => ['exists:materia,id'],
        ]);

        // Sincronizar materias habilitadas
        $docente->materias()->sync($validated['materias']);

        return redirect()->route('materias-habilitadas.index', $codigoDocente)
                        ->with('success', 'Materias habilitadas actualizadas exitosamente');
    }
}
