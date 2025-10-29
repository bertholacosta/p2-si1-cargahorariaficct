<?php

namespace App\Http\Controllers;

use App\Models\Dia;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $dias = Dia::withCount('horarios')
            ->orderBy('id')
            ->get();

        return Inertia::render('Dias/Index', [
            'dias' => $dias,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:20|unique:dia,nombre',
        ], [
            'nombre.required' => 'El nombre del día es requerido',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres',
            'nombre.unique' => 'Este día ya existe',
        ]);

        Dia::create($validated);

        return redirect()->route('dias.index')
            ->with('success', 'Día creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Dia $dia)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:20|unique:dia,nombre,' . $dia->id,
        ], [
            'nombre.required' => 'El nombre del día es requerido',
            'nombre.string' => 'El nombre debe ser una cadena de texto',
            'nombre.max' => 'El nombre no puede tener más de 20 caracteres',
            'nombre.unique' => 'Este día ya existe',
        ]);

        $dia->update($validated);

        return redirect()->route('dias.index')
            ->with('success', 'Día actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dia $dia)
    {
        $dia->delete();

        return redirect()->route('dias.index')
            ->with('success', 'Día eliminado exitosamente');
    }
}
