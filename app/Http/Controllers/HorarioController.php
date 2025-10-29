<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\Dia;
use App\Models\Hora;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HorarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $horarios = Horario::with(['dia', 'hora'])
            ->get()
            ->map(function ($horario) {
                return [
                    'id' => $horario->id,
                    'id_dia' => $horario->id_dia,
                    'id_hora' => $horario->id_hora,
                    'dia_nombre' => $horario->dia->nombre,
                    'hora_inicio' => $horario->hora->hora_inicio,
                    'hora_fin' => $horario->hora->hora_fin,
                ];
            });

        $dias = Dia::orderBy('id')->get();
        $horas = Hora::orderBy('hora_inicio')->get();

        return Inertia::render('Horarios/Index', [
            'horarios' => $horarios,
            'dias' => $dias,
            'horas' => $horas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_dia' => 'required|exists:dia,id',
            'id_hora' => 'required|exists:hora,id',
        ], [
            'id_dia.required' => 'El día es requerido',
            'id_dia.exists' => 'El día seleccionado no existe',
            'id_hora.required' => 'La hora es requerida',
            'id_hora.exists' => 'La hora seleccionada no existe',
        ]);

        // Verificar si ya existe un horario para este día y hora
        $exists = Horario::where('id_dia', $validated['id_dia'])
            ->where('id_hora', $validated['id_hora'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'id_hora' => 'Ya existe un horario para este día y hora'
            ]);
        }

        Horario::create($validated);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Horario $horario)
    {
        $validated = $request->validate([
            'id_dia' => 'required|exists:dia,id',
            'id_hora' => 'required|exists:hora,id',
        ], [
            'id_dia.required' => 'El día es requerido',
            'id_dia.exists' => 'El día seleccionado no existe',
            'id_hora.required' => 'La hora es requerida',
            'id_hora.exists' => 'La hora seleccionada no existe',
        ]);

        // Verificar si ya existe otro horario para este día y hora
        $exists = Horario::where('id_dia', $validated['id_dia'])
            ->where('id_hora', $validated['id_hora'])
            ->where('id', '!=', $horario->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'id_hora' => 'Ya existe un horario para este día y hora'
            ]);
        }

        $horario->update($validated);

        return redirect()->route('horarios.index')
            ->with('success', 'Horario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Horario $horario)
    {
        $horario->delete();

        return redirect()->route('horarios.index')
            ->with('success', 'Horario eliminado exitosamente');
    }
}
