<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class MateriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $materias = Materia::orderBy('sigla')->get();

        return Inertia::render('Materias/Index', [
            'materias' => $materias,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sigla' => ['required', 'string', 'max:10', 'unique:materia,sigla'],
            'nombre' => ['required', 'string', 'max:255'],
            'carga_horaria' => ['nullable', 'integer', 'min:0'],
            'creditos' => ['nullable', 'integer', 'min:0'],
        ]);

        Materia::create($validated);

        return redirect()->route('materias.index')
                        ->with('success', 'Materia creada exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Materia $materia)
    {
        $validated = $request->validate([
            'sigla' => ['required', 'string', 'max:10', Rule::unique('materia', 'sigla')->ignore($materia->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'carga_horaria' => ['nullable', 'integer', 'min:0'],
            'creditos' => ['nullable', 'integer', 'min:0'],
        ]);

        $materia->update($validated);

        return redirect()->route('materias.index')
                        ->with('success', 'Materia actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Materia $materia)
    {
        $materia->delete();

        return redirect()->route('materias.index')
                        ->with('success', 'Materia eliminada exitosamente');
    }
}
