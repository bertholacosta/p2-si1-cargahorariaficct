<?php

namespace App\Http\Controllers;

use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PermisoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permisos = Permiso::orderBy('modulo')
            ->orderBy('nombre')
            ->get()
            ->map(function ($permiso) {
                return [
                    'id' => $permiso->id,
                    'nombre' => $permiso->nombre,
                    'slug' => $permiso->slug,
                    'modulo' => $permiso->modulo,
                    'descripcion' => $permiso->descripcion,
                ];
            });

        return Inertia::render('Permisos/Index', [
            'permisos' => $permisos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:permiso,slug',
            'modulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        Permiso::create($validated);

        return redirect()->back()->with('success', 'Permiso creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permiso $permiso)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:permiso,slug,' . $permiso->id,
            'modulo' => 'required|string|max:100',
            'descripcion' => 'nullable|string|max:255',
        ]);

        $permiso->update($validated);

        return redirect()->back()->with('success', 'Permiso actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permiso $permiso)
    {
        // Verificar si hay roles con este permiso
        if ($permiso->roles()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar el permiso porque está asignado a roles'
            ]);
        }

        $permiso->delete();

        return redirect()->back()->with('success', 'Permiso eliminado exitosamente');
    }
}
