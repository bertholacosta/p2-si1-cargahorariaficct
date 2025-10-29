<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $grupos = Grupo::withCount('materias')->orderBy('nombre')->get();

        return Inertia::render('Grupos/Index', [
            'grupos' => $grupos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:10', 'unique:grupo,nombre'],
        ]);

        Grupo::create($validated);

        return redirect()->route('grupos.index')
                        ->with('success', 'Grupo creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:10', Rule::unique('grupo', 'nombre')->ignore($grupo->id)],
        ]);

        $grupo->update($validated);

        return redirect()->route('grupos.index')
                        ->with('success', 'Grupo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Grupo $grupo)
    {
        $grupo->delete();

        return redirect()->route('grupos.index')
                        ->with('success', 'Grupo eliminado exitosamente');
    }
}
