<?php

namespace App\Http\Controllers;

use App\Models\Gestion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GestionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $gestiones = Gestion::orderBy('año', 'desc')
            ->orderBy('semestre', 'desc')
            ->get();

        return Inertia::render('Gestiones/Index', [
            'gestiones' => $gestiones,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'semestre' => 'required|integer|min:1|max:2',
            'año' => 'required|integer|min:2000|max:2100',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es requerida',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'semestre.required' => 'El semestre es requerido',
            'semestre.integer' => 'El semestre debe ser un número entero',
            'semestre.min' => 'El semestre debe ser 1 o 2',
            'semestre.max' => 'El semestre debe ser 1 o 2',
            'año.required' => 'El año es requerido',
            'año.integer' => 'El año debe ser un número entero',
            'año.min' => 'El año debe ser mayor o igual a 2000',
            'año.max' => 'El año debe ser menor o igual a 2100',
        ]);

        // Verificar si ya existe una gestión para ese año y semestre
        $exists = Gestion::where('año', $validated['año'])
            ->where('semestre', $validated['semestre'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'semestre' => 'Ya existe una gestión para el año ' . $validated['año'] . ' semestre ' . $validated['semestre']
            ]);
        }

        Gestion::create($validated);

        return redirect()->route('gestiones.index')
            ->with('success', 'Gestión creada exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Gestion $gestione)
    {
        $validated = $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after:fecha_inicio',
            'semestre' => 'required|integer|min:1|max:2',
            'año' => 'required|integer|min:2000|max:2100',
        ], [
            'fecha_inicio.required' => 'La fecha de inicio es requerida',
            'fecha_inicio.date' => 'La fecha de inicio debe ser una fecha válida',
            'fecha_fin.required' => 'La fecha de fin es requerida',
            'fecha_fin.date' => 'La fecha de fin debe ser una fecha válida',
            'fecha_fin.after' => 'La fecha de fin debe ser posterior a la fecha de inicio',
            'semestre.required' => 'El semestre es requerido',
            'semestre.integer' => 'El semestre debe ser un número entero',
            'semestre.min' => 'El semestre debe ser 1 o 2',
            'semestre.max' => 'El semestre debe ser 1 o 2',
            'año.required' => 'El año es requerido',
            'año.integer' => 'El año debe ser un número entero',
            'año.min' => 'El año debe ser mayor o igual a 2000',
            'año.max' => 'El año debe ser menor o igual a 2100',
        ]);

        // Verificar si ya existe otra gestión para ese año y semestre
        $exists = Gestion::where('año', $validated['año'])
            ->where('semestre', $validated['semestre'])
            ->where('id', '!=', $gestione->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'semestre' => 'Ya existe una gestión para el año ' . $validated['año'] . ' semestre ' . $validated['semestre']
            ]);
        }

        $gestione->update($validated);

        return redirect()->route('gestiones.index')
            ->with('success', 'Gestión actualizada exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Gestion $gestione)
    {
        $gestione->delete();

        return redirect()->route('gestiones.index')
            ->with('success', 'Gestión eliminada exitosamente');
    }
}
