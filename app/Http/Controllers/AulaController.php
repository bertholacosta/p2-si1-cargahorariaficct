<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AulaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $aulas = Aula::with('modulo')->orderBy('numero_modulo')->orderBy('numero')->get();
        $modulos = Modulo::orderBy('numero')->get();

        return Inertia::render('Aulas/Index', [
            'aulas' => $aulas,
            'modulos' => $modulos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numero' => ['required', 'integer'],
            'numero_modulo' => ['required', 'integer', 'exists:modulo,numero'],
        ]);

        // Validar unicidad manual
        $exists = Aula::where('numero', $validated['numero'])
                      ->where('numero_modulo', $validated['numero_modulo'])
                      ->exists();

        if ($exists) {
            return back()->withErrors([
                'numero' => 'Ya existe un aula con este número en el módulo seleccionado.'
            ]);
        }

        Aula::create($validated);

        return redirect()->route('aulas.index')
                        ->with('success', 'Aula creada exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Aula $aula)
    {
        $validated = $request->validate([
            'numero' => ['required', 'integer'],
            'numero_modulo' => ['required', 'integer', 'exists:modulo,numero'],
        ]);

        // Validar unicidad manual (excepto el registro actual)
        $exists = Aula::where('numero', $validated['numero'])
                      ->where('numero_modulo', $validated['numero_modulo'])
                      ->where('id', '!=', $aula->id)
                      ->exists();

        if ($exists) {
            return back()->withErrors([
                'numero' => 'Ya existe un aula con este número en el módulo seleccionado.'
            ]);
        }

        $aula->update($validated);

        return redirect()->route('aulas.index')
                        ->with('success', 'Aula actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Aula $aula)
    {
        $aula->delete();

        return redirect()->route('aulas.index')
                        ->with('success', 'Aula eliminada exitosamente');
    }
}
