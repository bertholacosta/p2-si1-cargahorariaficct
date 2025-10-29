<?php

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MateriaHabilitadaController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\GrupoMateriaController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\GestionController;
use App\Http\Controllers\DiaController;
use App\Http\Controllers\HoraController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\AsignacionController;
use App\Http\Controllers\BitacoraController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect('/login');
    }
    
    return Inertia::render('Dashboard', [
        'user' => auth()->user()->load('rol.permisos'),
    ]);
})->name('home');

Route::get('/login', function () {
    if (auth()->check()) {
        return redirect('/');
    }
    return Inertia::render('Login');
})->name('login');

Route::post('/login', function () {
    request()->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    // Intentar autenticar con email y password
    if (auth()->attempt(request()->only('email', 'password'), request()->boolean('remember'))) {
        request()->session()->regenerate();
        
        // Registrar en bitácora
        \App\Helpers\BitacoraHelper::loginExitoso(auth()->id(), request()->ip());
        
        return redirect()->intended('/');
    }

    // Registrar intento fallido
    \App\Helpers\BitacoraHelper::loginFallido(request()->email, request()->ip());

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
});

Route::post('/logout', function () {
    // Registrar logout antes de cerrar sesión
    \App\Helpers\BitacoraHelper::logout(auth()->id(), request()->ip());
    
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    // Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'index'])->middleware('permiso:usuarios.ver')->name('usuarios.index');
    Route::post('/usuarios', [UsuarioController::class, 'store'])->middleware('permiso:usuarios.crear')->name('usuarios.store');
    Route::put('/usuarios/{usuario}', [UsuarioController::class, 'update'])->middleware('permiso:usuarios.editar')->name('usuarios.update');
    Route::delete('/usuarios/{usuario}', [UsuarioController::class, 'destroy'])->middleware('permiso:usuarios.eliminar')->name('usuarios.destroy');
    
    // Roles y permisos
    Route::get('/roles', [RolController::class, 'index'])->middleware('permiso:roles.ver')->name('roles.index');
    Route::post('/roles', [RolController::class, 'store'])->middleware('permiso:roles.crear')->name('roles.store');
    Route::put('/roles/{rol}', [RolController::class, 'update'])->middleware('permiso:roles.editar')->name('roles.update');
    Route::delete('/roles/{rol}', [RolController::class, 'destroy'])->middleware('permiso:roles.eliminar')->name('roles.destroy');
    Route::get('/roles/{rol}/permisos', [RolController::class, 'permisos'])->middleware('permiso:roles.ver')->name('roles.permisos');
    
    Route::get('/permisos', [PermisoController::class, 'index'])->middleware('permiso:permisos.ver')->name('permisos.index');
    Route::post('/permisos', [PermisoController::class, 'store'])->middleware('permiso:permisos.crear')->name('permisos.store');
    Route::put('/permisos/{permiso}', [PermisoController::class, 'update'])->middleware('permiso:permisos.editar')->name('permisos.update');
    Route::delete('/permisos/{permiso}', [PermisoController::class, 'destroy'])->middleware('permiso:permisos.eliminar')->name('permisos.destroy');
    
    // Docentes
    Route::get('/docentes', [DocenteController::class, 'index'])->middleware('permiso:docentes.ver')->name('docentes.index');
    Route::post('/docentes', [DocenteController::class, 'store'])->middleware('permiso:docentes.crear')->name('docentes.store');
    Route::put('/docentes/{docente}', [DocenteController::class, 'update'])->middleware('permiso:docentes.editar')->name('docentes.update');
    Route::delete('/docentes/{docente}', [DocenteController::class, 'destroy'])->middleware('permiso:docentes.eliminar')->name('docentes.destroy');
    
    // Materias
    Route::get('/materias', [MateriaController::class, 'index'])->middleware('permiso:materias.ver')->name('materias.index');
    Route::post('/materias', [MateriaController::class, 'store'])->middleware('permiso:materias.crear')->name('materias.store');
    Route::put('/materias/{materia}', [MateriaController::class, 'update'])->middleware('permiso:materias.editar')->name('materias.update');
    Route::delete('/materias/{materia}', [MateriaController::class, 'destroy'])->middleware('permiso:materias.eliminar')->name('materias.destroy');
    
    // Grupos
    Route::get('/grupos', [GrupoController::class, 'index'])->middleware('permiso:grupos.ver')->name('grupos.index');
    Route::post('/grupos', [GrupoController::class, 'store'])->middleware('permiso:grupos.crear')->name('grupos.store');
    Route::put('/grupos/{grupo}', [GrupoController::class, 'update'])->middleware('permiso:grupos.editar')->name('grupos.update');
    Route::delete('/grupos/{grupo}', [GrupoController::class, 'destroy'])->middleware('permiso:grupos.eliminar')->name('grupos.destroy');
    
    // Módulos
    Route::get('/modulos', [ModuloController::class, 'index'])->middleware('permiso:modulos.ver')->name('modulos.index');
    Route::post('/modulos', [ModuloController::class, 'store'])->middleware('permiso:modulos.crear')->name('modulos.store');
    Route::put('/modulos/{modulo}', [ModuloController::class, 'update'])->middleware('permiso:modulos.editar')->name('modulos.update');
    Route::delete('/modulos/{modulo}', [ModuloController::class, 'destroy'])->middleware('permiso:modulos.eliminar')->name('modulos.destroy');
    
    // Aulas
    Route::get('/aulas', [AulaController::class, 'index'])->middleware('permiso:aulas.ver')->name('aulas.index');
    Route::post('/aulas', [AulaController::class, 'store'])->middleware('permiso:aulas.crear')->name('aulas.store');
    Route::put('/aulas/{aula}', [AulaController::class, 'update'])->middleware('permiso:aulas.editar')->name('aulas.update');
    Route::delete('/aulas/{aula}', [AulaController::class, 'destroy'])->middleware('permiso:aulas.eliminar')->name('aulas.destroy');
    
    // Gestiones
    Route::get('/gestiones', [GestionController::class, 'index'])->middleware('permiso:gestiones.ver')->name('gestiones.index');
    Route::post('/gestiones', [GestionController::class, 'store'])->middleware('permiso:gestiones.crear')->name('gestiones.store');
    Route::put('/gestiones/{gestion}', [GestionController::class, 'update'])->middleware('permiso:gestiones.editar')->name('gestiones.update');
    Route::delete('/gestiones/{gestion}', [GestionController::class, 'destroy'])->middleware('permiso:gestiones.eliminar')->name('gestiones.destroy');
    
    // Días
    Route::get('/dias', [DiaController::class, 'index'])->middleware('permiso:dias.ver')->name('dias.index');
    Route::post('/dias', [DiaController::class, 'store'])->middleware('permiso:dias.crear')->name('dias.store');
    Route::put('/dias/{dia}', [DiaController::class, 'update'])->middleware('permiso:dias.editar')->name('dias.update');
    Route::delete('/dias/{dia}', [DiaController::class, 'destroy'])->middleware('permiso:dias.eliminar')->name('dias.destroy');
    
    // Horas
    Route::get('/horas', [HoraController::class, 'index'])->middleware('permiso:horas.ver')->name('horas.index');
    Route::post('/horas', [HoraController::class, 'store'])->middleware('permiso:horas.crear')->name('horas.store');
    Route::put('/horas/{hora}', [HoraController::class, 'update'])->middleware('permiso:horas.editar')->name('horas.update');
    Route::delete('/horas/{hora}', [HoraController::class, 'destroy'])->middleware('permiso:horas.eliminar')->name('horas.destroy');
    
    // Horarios
    Route::get('/horarios', [HorarioController::class, 'index'])->middleware('permiso:horarios.ver')->name('horarios.index');
    Route::post('/horarios', [HorarioController::class, 'store'])->middleware('permiso:horarios.crear')->name('horarios.store');
    Route::put('/horarios/{horario}', [HorarioController::class, 'update'])->middleware('permiso:horarios.editar')->name('horarios.update');
    Route::delete('/horarios/{horario}', [HorarioController::class, 'destroy'])->middleware('permiso:horarios.eliminar')->name('horarios.destroy');
    
    // Asignaciones
    Route::get('/asignaciones', [AsignacionController::class, 'index'])->middleware('permiso:asignaciones.ver')->name('asignaciones.index');
    Route::post('/asignaciones', [AsignacionController::class, 'store'])->middleware('permiso:asignaciones.crear')->name('asignaciones.store');
    Route::delete('/asignaciones/{asignacion}', [AsignacionController::class, 'destroy'])->middleware('permiso:asignaciones.eliminar')->name('asignaciones.destroy');
    
    // Rutas adicionales para asignaciones
    Route::get('/asignaciones/docente/{codigo}', [AsignacionController::class, 'horarioDocente'])
        ->middleware('permiso:asignaciones.ver')
        ->name('asignaciones.docente');
    Route::get('/asignaciones/aula/{id}', [AsignacionController::class, 'horarioAula'])
        ->middleware('permiso:asignaciones.ver')
        ->name('asignaciones.aula');
    
    // Materias habilitadas por docente
    Route::get('/docentes/{codigo}/materias-habilitadas', [MateriaHabilitadaController::class, 'index'])
        ->middleware('permiso:docentes.materias')
        ->name('materias-habilitadas.index');
    Route::put('/docentes/{codigo}/materias-habilitadas', [MateriaHabilitadaController::class, 'update'])
        ->middleware('permiso:docentes.materias')
        ->name('materias-habilitadas.update');
    
    // Grupos por materia
    Route::get('/materias/{materia}/grupos', [GrupoMateriaController::class, 'index'])
        ->middleware('permiso:materias.grupos')
        ->name('grupos-materia.index');
    Route::put('/materias/{materia}/grupos', [GrupoMateriaController::class, 'update'])
        ->middleware('permiso:materias.grupos')
        ->name('grupos-materia.update');
    
    // Bitácora
    Route::get('/bitacora', [BitacoraController::class, 'index'])
        ->middleware('permiso:bitacora.ver')
        ->name('bitacora.index');
    Route::get('/bitacora/exportar', [BitacoraController::class, 'exportar'])
        ->middleware('permiso:bitacora.exportar')
        ->name('bitacora.exportar');
});
