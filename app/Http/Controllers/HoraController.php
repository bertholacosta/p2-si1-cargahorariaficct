<?php

namespace App\Http\Controllers;

use App\Models\Hora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HoraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $horas = Hora::withCount('horarios')
            ->orderBy('hora_inicio')
            ->get();

        return Inertia::render('Horas/Index', [
            'horas' => $horas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ], [
            'hora_inicio.required' => 'La hora de inicio es requerida',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM',
            'hora_fin.required' => 'La hora de fin es requerida',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
        ]);

        Hora::create($validated);

        return redirect()->route('horas.index')
            ->with('success', 'Bloque horario creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hora $hora)
    {
        $validated = $request->validate([
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ], [
            'hora_inicio.required' => 'La hora de inicio es requerida',
            'hora_inicio.date_format' => 'La hora de inicio debe tener formato HH:MM',
            'hora_fin.required' => 'La hora de fin es requerida',
            'hora_fin.date_format' => 'La hora de fin debe tener formato HH:MM',
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio',
        ]);

        $hora->update($validated);

        return redirect()->route('horas.index')
            ->with('success', 'Bloque horario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Hora $hora)
    {
        $hora->delete();

        return redirect()->route('horas.index')
            ->with('success', 'Bloque horario eliminado exitosamente');
    }
}
