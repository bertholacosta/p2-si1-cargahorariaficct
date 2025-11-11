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
        
        // Obtener IP real del cliente y fecha/hora del cliente
        $ipReal = \App\Helpers\BitacoraHelper::obtenerIpReal();
        $fechaCliente = request()->header('X-Client-Time') ?? request()->input('client_time');
        
        // Registrar en bitácora con IP y fecha del cliente
        \App\Models\Bitacora::create([
            'accion' => 'Usuario inició sesión en el sistema',
            'ip' => $ipReal,
            'id_usuario' => auth()->id(),
            'fecha_cliente' => $fechaCliente,
        ]);
        
        // Crear notificación de inicio de sesión
        $notificacionService = app(\App\Services\NotificacionService::class);
        $notificacionService->crearNotificacionInicioSesion(
            auth()->id(),
            $ipReal,
            $fechaCliente ? \Carbon\Carbon::parse($fechaCliente) : now()
        );
        
        return redirect()->intended('/');
    }

    // Obtener IP real del cliente
    $ipReal = \App\Helpers\BitacoraHelper::obtenerIpReal();
    $fechaCliente = request()->header('X-Client-Time') ?? request()->input('client_time');
    
    // Registrar intento fallido
    \App\Models\Bitacora::create([
        'accion' => "Intento de inicio de sesión fallido con email: " . request()->email,
        'ip' => $ipReal,
        'id_usuario' => null,
        'fecha_cliente' => $fechaCliente,
    ]);

    return back()->withErrors([
        'email' => 'Las credenciales no coinciden con nuestros registros.',
    ]);
});

Route::post('/logout', function () {
    // Obtener IP real del cliente y fecha/hora del cliente
    $ipReal = \App\Helpers\BitacoraHelper::obtenerIpReal();
    $fechaCliente = request()->header('X-Client-Time') ?? request()->input('client_time');
    
    // Registrar logout antes de cerrar sesión
    \App\Models\Bitacora::create([
        'accion' => 'Usuario cerró sesión',
        'ip' => $ipReal,
        'id_usuario' => auth()->id(),
        'fecha_cliente' => $fechaCliente,
    ]);
    
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
    Route::get('/usuarios/plantilla/descargar', [UsuarioController::class, 'descargarPlantilla'])->middleware('permiso:usuarios.crear')->name('usuarios.plantilla');
    Route::post('/usuarios/importar', [UsuarioController::class, 'importar'])->middleware('permiso:usuarios.crear')->name('usuarios.importar');
    Route::get('/usuarios/importar/resultados', [UsuarioController::class, 'resultadosImportacion'])->middleware('permiso:usuarios.ver')->name('usuarios.resultados');
    
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
    Route::post('/asignaciones/multiple', [AsignacionController::class, 'storeMultiple'])->middleware('permiso:asignaciones.crear')->name('asignaciones.storeMultiple');
    Route::delete('/asignaciones/{asignacion}', [AsignacionController::class, 'destroy'])->middleware('permiso:asignaciones.eliminar')->name('asignaciones.destroy');
    
    // Rutas adicionales para asignaciones
    Route::get('/asignaciones/docente/{codigo}', [AsignacionController::class, 'horarioDocente'])
        ->middleware('permiso:asignaciones.ver')
        ->name('asignaciones.docente');
    Route::get('/asignaciones/aula/{id}', [AsignacionController::class, 'horarioAula'])
        ->middleware('permiso:asignaciones.ver')
        ->name('asignaciones.aula');
    
    // Importación masiva de asignaciones (rutas específicas primero)
    Route::get('/asignaciones/importar/plantilla', [\App\Http\Controllers\ImportarAsignacionesController::class, 'descargarPlantilla'])
        ->middleware('permiso:asignaciones.crear')
        ->name('asignaciones.importar.plantilla');
    Route::post('/asignaciones/importar/procesar', [\App\Http\Controllers\ImportarAsignacionesController::class, 'procesarArchivo'])
        ->middleware('permiso:asignaciones.crear')
        ->name('asignaciones.importar.procesar');
    Route::post('/asignaciones/importar/confirmar', [\App\Http\Controllers\ImportarAsignacionesController::class, 'confirmarImportacion'])
        ->middleware('permiso:asignaciones.crear')
        ->name('asignaciones.importar.confirmar');
    Route::post('/asignaciones/importar/cancelar', [\App\Http\Controllers\ImportarAsignacionesController::class, 'cancelarImportacion'])
        ->middleware('permiso:asignaciones.crear')
        ->name('asignaciones.importar.cancelar');
    Route::get('/asignaciones/importar', [\App\Http\Controllers\ImportarAsignacionesController::class, 'index'])
        ->middleware('permiso:asignaciones.crear')
        ->name('asignaciones.importar');
    
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
    
    // Asistencias
    Route::get('/asistencias', [\App\Http\Controllers\AsistenciaController::class, 'index'])
        ->name('asistencias.index');
    Route::post('/asistencias/registrar', [\App\Http\Controllers\AsistenciaController::class, 'registrar'])
        ->name('asistencias.registrar');
    Route::get('/asistencias/reporte', [\App\Http\Controllers\AsistenciaController::class, 'reporte'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('asistencias.reporte');
    Route::put('/asistencias/{id}', [\App\Http\Controllers\AsistenciaController::class, 'actualizar'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('asistencias.actualizar');
    Route::post('/asistencias/justificar', [\App\Http\Controllers\AsistenciaController::class, 'justificar'])
        ->name('asistencias.justificar');
    Route::get('/asistencias/estadisticas/{codigoDocente}', [\App\Http\Controllers\AsistenciaController::class, 'estadisticas'])
        ->name('asistencias.estadisticas');
    Route::post('/asistencias/faltas-automaticas', [\App\Http\Controllers\AsistenciaController::class, 'registrarFaltasAutomaticas'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('asistencias.faltas-automaticas');
    
    // QR Asistencias
    Route::post('/asistencias/qr/generar', [\App\Http\Controllers\QRAsistenciaController::class, 'generar'])
        ->name('qr.generar');
    Route::post('/asistencias/qr/verificar/{token}', [\App\Http\Controllers\QRAsistenciaController::class, 'verificar'])
        ->name('qr.verificar');
    Route::get('/asistencias/qr/info/{token}', [\App\Http\Controllers\QRAsistenciaController::class, 'info'])
        ->name('qr.info');
    Route::post('/asistencias/qr/invalidar', [\App\Http\Controllers\QRAsistenciaController::class, 'invalidar'])
        ->name('qr.invalidar');
    
    // Días No Laborables
    Route::get('/dias-no-laborables', [\App\Http\Controllers\DiaNoLaborableController::class, 'index'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('dias-no-laborables.index');
    Route::post('/dias-no-laborables', [\App\Http\Controllers\DiaNoLaborableController::class, 'store'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('dias-no-laborables.store');
    Route::put('/dias-no-laborables/{id}', [\App\Http\Controllers\DiaNoLaborableController::class, 'update'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('dias-no-laborables.update');
    Route::delete('/dias-no-laborables/{id}', [\App\Http\Controllers\DiaNoLaborableController::class, 'destroy'])
        ->middleware('permiso:asistencias.gestionar')
        ->name('dias-no-laborables.destroy');
    Route::get('/dias-no-laborables/mes', [\App\Http\Controllers\DiaNoLaborableController::class, 'delMes'])
        ->name('dias-no-laborables.mes');
    
    // Notificaciones
    Route::get('/notificaciones', [\App\Http\Controllers\NotificacionController::class, 'index'])
        ->name('notificaciones.index');
    Route::get('/notificaciones/contador', [\App\Http\Controllers\NotificacionController::class, 'contarNoLeidas'])
        ->name('notificaciones.contador');
    Route::post('/notificaciones/{id}/leer', [\App\Http\Controllers\NotificacionController::class, 'marcarComoLeida'])
        ->name('notificaciones.leer');
    Route::post('/notificaciones/leer-todas', [\App\Http\Controllers\NotificacionController::class, 'marcarTodasComoLeidas'])
        ->name('notificaciones.leer-todas');
    Route::delete('/notificaciones/{id}', [\App\Http\Controllers\NotificacionController::class, 'eliminar'])
        ->name('notificaciones.eliminar');
    
    // Gestión de notificaciones (Admin)
    Route::get('/notificaciones/gestion', [\App\Http\Controllers\NotificacionController::class, 'gestionAdmin'])
        ->middleware('permiso:usuarios.gestionar')
        ->name('notificaciones.gestion');
    Route::post('/notificaciones/mensaje-masivo', [\App\Http\Controllers\NotificacionController::class, 'enviarMensajeMasivo'])
        ->middleware('permiso:usuarios.gestionar')
        ->name('notificaciones.mensaje-masivo');
});
