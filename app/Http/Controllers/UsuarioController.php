<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Rol;
use App\Imports\DocentesImport;
use App\Exports\PlantillaDocentesExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use App\Helpers\BitacoraHelper;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = Usuario::with('rol')->orderBy('created_at', 'desc')->get();
        $roles = Rol::all();
        
        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
            'user' => auth()->user()->load('rol.permisos'),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:30|unique:usuario,username',
            'email' => 'required|email|max:100|unique:usuario,email',
            'password' => 'required|string|min:6',
            'id_rol' => 'required|exists:rol,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        Usuario::create($validated);

        return redirect()->back()->with('success', 'Usuario creado exitosamente.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $usuario)
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'max:30', Rule::unique('usuario', 'username')->ignore($usuario->id)],
            'email' => ['required', 'email', 'max:100', Rule::unique('usuario', 'email')->ignore($usuario->id)],
            'id_rol' => 'required|exists:rol,id',
        ]);

        // Solo actualizar password si se proporciona
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'string|min:6',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $usuario->update($validated);

        return redirect()->back()->with('success', 'Usuario actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $usuario)
    {
        // No permitir eliminar al usuario actual
        if ($usuario->id === auth()->id()) {
            return redirect()->back()->withErrors(['error' => 'No puedes eliminar tu propio usuario.']);
        }

        $usuario->delete();

        return redirect()->back()->with('success', 'Usuario eliminado exitosamente.');
    }

    /**
     * Descargar plantilla de Excel para importación de docentes
     */
    public function descargarPlantilla()
    {
        return Excel::download(
            new PlantillaDocentesExport(), 
            'plantilla_docentes_' . date('Y-m-d') . '.xlsx'
        );
    }

    /**
     * Importar docentes desde archivo Excel/CSV
     */
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            $import = new DocentesImport();
            Excel::import($import, $request->file('archivo'));

            $resultados = $import->getResultados();
            
            // Contar resultados
            $creados = collect($resultados)->where('estado', 'creado')->count();
            $omitidos = collect($resultados)->where('estado', 'omitido')->count();
            $errores = collect($resultados)->where('estado', 'error')->count();

            // Registrar en bitácora
            BitacoraHelper::registrar(
                "Importó {$creados} docentes desde archivo Excel/CSV. Omitidos: {$omitidos}, Errores: {$errores}"
            );

            // Guardar resultados en sesión para mostrarlos
            session(['resultados_importacion' => $resultados]);

            return redirect()->route('usuarios.index')
                ->with('success', "Importación completada: {$creados} creados, {$omitidos} omitidos, {$errores} errores");

        } catch (\Exception $e) {
            return redirect()->route('usuarios.index')
                ->with('error', 'Error al importar el archivo: ' . $e->getMessage());
        }
    }

    /**
     * Obtener resultados de la última importación
     */
    public function resultadosImportacion()
    {
        $resultados = session('resultados_importacion', []);
        session()->forget('resultados_importacion');
        
        return response()->json($resultados);
    }
}
