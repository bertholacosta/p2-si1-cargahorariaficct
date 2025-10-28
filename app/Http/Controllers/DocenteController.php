<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DocenteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $docentes = Docente::with('usuario')->get();
        $usuarios = Usuario::whereDoesntHave('docente')->get();

        return Inertia::render('Docentes/Index', [
            'docentes' => $docentes,
            'usuarios' => $usuarios,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:20', 'unique:docente,codigo'],
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:20', 'unique:docente,ci'],
            'id_usuario' => ['nullable', 'integer', 'exists:usuario,id', 'unique:docente,id_usuario'],
        ]);

        Docente::create($validated);

        return redirect()->route('docentes.index')
                        ->with('success', 'Docente creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $codigo)
    {
        $docente = Docente::findOrFail($codigo);

        $validated = $request->validate([
            'codigo' => ['required', 'string', 'max:20', Rule::unique('docente', 'codigo')->ignore($codigo, 'codigo')],
            'nombre' => ['required', 'string', 'max:255'],
            'apellidos' => ['required', 'string', 'max:255'],
            'ci' => ['required', 'string', 'max:20', Rule::unique('docente', 'ci')->ignore($codigo, 'codigo')],
            'id_usuario' => ['nullable', 'integer', 'exists:usuario,id', Rule::unique('docente', 'id_usuario')->ignore($codigo, 'codigo')],
        ]);

        $docente->update($validated);

        return redirect()->route('docentes.index')
                        ->with('success', 'Docente actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $codigo)
    {
        $docente = Docente::findOrFail($codigo);
        $docente->delete();

        return redirect()->route('docentes.index')
                        ->with('success', 'Docente eliminado exitosamente');
    }
}
