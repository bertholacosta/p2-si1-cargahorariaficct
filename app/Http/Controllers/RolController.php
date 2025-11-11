<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cachear permisos por 1 hora ya que no cambian frecuentemente
        $permisos = cache()->remember('permisos_agrupados', 3600, function () {
            return Permiso::select('id', 'nombre', 'slug', 'modulo', 'descripcion')
                ->orderBy('modulo')
                ->orderBy('nombre')
                ->get()
                ->groupBy('modulo');
        });

        $roles = Rol::select('id', 'nombre', 'descripcion')
            ->withCount('permisos')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
            'permisos' => $permisos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:rol,nombre',
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $rol = Rol::create([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        // Asignar permisos
        if (isset($validated['permisos'])) {
            $rol->permisos()->attach($validated['permisos']);
        }

        return redirect()->back()->with('success', 'Rol creado exitosamente');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Rol $rol)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:100|unique:rol,nombre,' . $rol->id,
            'descripcion' => 'nullable|string|max:255',
            'permisos' => 'array',
            'permisos.*' => 'exists:permiso,id',
        ]);

        $rol->update([
            'nombre' => $validated['nombre'],
            'descripcion' => $validated['descripcion'] ?? null,
        ]);

        // Sincronizar permisos
        if (isset($validated['permisos'])) {
            $rol->permisos()->sync($validated['permisos']);
        } else {
            $rol->permisos()->detach();
        }

        return redirect()->back()->with('success', 'Rol actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rol $rol)
    {
        // Verificar si hay usuarios con este rol
        if ($rol->usuarios()->count() > 0) {
            return redirect()->back()->withErrors([
                'error' => 'No se puede eliminar el rol porque tiene usuarios asignados'
            ]);
        }

        $rol->permisos()->detach();
        $rol->delete();

        return redirect()->back()->with('success', 'Rol eliminado exitosamente');
    }

    /**
     * Obtener permisos de un rol
     */
    public function permisos(Rol $rol)
    {
        return response()->json([
            'permisos' => $rol->permisos->pluck('id'),
        ]);
    }
}
