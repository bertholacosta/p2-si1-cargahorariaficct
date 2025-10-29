<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ModuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $modulos = Modulo::withCount('aulas')->orderBy('numero')->get();

        return Inertia::render('Modulos/Index', [
            'modulos' => $modulos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => ['required', 'integer', 'unique:modulo,numero'],
            'facultad' => ['nullable', 'string', 'max:255'],
        ]);

        Modulo::create($validated);

        return redirect()->route('modulos.index')
                        ->with('success', 'Módulo creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $numero)
    {
        $modulo = Modulo::findOrFail($numero);

        $validated = $request->validate([
            'numero' => ['required', 'integer', Rule::unique('modulo', 'numero')->ignore($numero, 'numero')],
            'facultad' => ['nullable', 'string', 'max:255'],
        ]);

        $modulo->update($validated);

        return redirect()->route('modulos.index')
                        ->with('success', 'Módulo actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $numero)
    {
        $modulo = Modulo::findOrFail($numero);
        $modulo->delete();

        return redirect()->route('modulos.index')
                        ->with('success', 'Módulo eliminado exitosamente');
    }
}
