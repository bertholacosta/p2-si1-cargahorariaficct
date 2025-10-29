<?php

use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\MateriaController;
use App\Http\Controllers\MateriaHabilitadaController;
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
        return redirect()->intended('/');
    }

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
});

Route::post('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Rutas protegidas
Route::middleware('auth')->group(function () {
    Route::resource('usuarios', UsuarioController::class)->except(['show', 'create', 'edit']);
    Route::resource('docentes', DocenteController::class)->except(['show', 'create', 'edit']);
    Route::resource('materias', MateriaController::class)->except(['show', 'create', 'edit']);
    
    // Materias habilitadas por docente
    Route::get('/docentes/{codigo}/materias-habilitadas', [MateriaHabilitadaController::class, 'index'])
        ->name('materias-habilitadas.index');
    Route::put('/docentes/{codigo}/materias-habilitadas', [MateriaHabilitadaController::class, 'update'])
        ->name('materias-habilitadas.update');
});
