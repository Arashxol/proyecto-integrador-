<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalisisHeuristicoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DeteccionAnalisisController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\ResultadoController;
use App\Http\Controllers\ReporteController;
use App\Http\Middleware\CheckRol;
use Illuminate\Support\Facades\Route;

// Rutas Públicas (Auth)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register')->middleware('guest');
Route::post('/register', [AuthController::class, 'register']);

// Rutas Protegidas del Sistema
Route::middleware(['auth'])->group(function () {
    
    // Panel General
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Recepción (Admisión)
    Route::middleware([CheckRol::class . ':Recepcionista,Administrador'])->group(function () {
        Route::post('/ordenes/registrar', [OrdenController::class, 'registrar'])->name('laboratorio.registrar');
        Route::get('/pacientes/check/{ci}', [OrdenController::class, 'checkCi'])->name('pacientes.check');
    });

    // Módulo Técnico (Laboratorio Clínico)
    Route::middleware([CheckRol::class . ':Bioquimico,Administrador'])->group(function () {
        Route::post('/resultados/guardar-orden/{orden_id}', [ResultadoController::class, 'guardarResultadosOrden'])->name('laboratorio.guardar_resultados');
        Route::post('/resultados/guardar-muestra/{muestra_id}', [ResultadoController::class, 'guardarResultado'])->name('laboratorio.guardar_resultado');
    });

    // Vista de Resultados con exportación PDF
    Route::middleware([CheckRol::class . ':Administrador,Recepcionista,Bioquimico'])->group(function () {
        Route::get('/resultados', [\App\Http\Controllers\ResultadosController::class, 'index'])->name('resultados.index');
        Route::get('/resultados/paciente/{id}', [\App\Http\Controllers\ResultadosController::class, 'paciente'])->name('resultados.paciente');
        Route::get('/resultados/exportar/{id}', [\App\Http\Controllers\ResultadosController::class, 'exportar'])->name('resultados.exportar');
    });

    // Detección de Anomalías (Machine Learning Simplificado)
    Route::middleware([CheckRol::class . ':Administrador,Bioquimico'])->group(function () {
        Route::get('/deteccion-analisis', [DeteccionAnalisisController::class, 'index'])->name('deteccion.index');
        Route::post('/deteccion-analisis/evaluar', [DeteccionAnalisisController::class, 'evaluar'])->name('deteccion.evaluar');
    });

    // Registro de Pacientes (todos los roles menos Paciente)
    Route::middleware([CheckRol::class . ':Administrador,Recepcionista,Bioquimico'])->group(function () {
        Route::get('/pacientes/nuevo', [\App\Http\Controllers\PacienteController::class, 'create'])->name('pacientes.crear');
        Route::post('/pacientes/guardar', [\App\Http\Controllers\PacienteController::class, 'store'])->name('pacientes.guardar');
    });

    // Módulo de Emisión y Pacientes (Compartido)
    Route::get('/resultados/imprimir/{muestra_id}', [ResultadoController::class, 'imprimirResultado'])->name('laboratorio.imprimir');

    // Reportes - disponible para personal autorizado (Administrador, Recepción y Bioquímico ven datos operativos; exportación y filtros auditados)
    Route::middleware([CheckRol::class . ':Administrador,Recepcionista,Bioquimico'])->group(function () {
        Route::get('/reportes', [ReporteController::class, 'index'])->name('laboratorio.reportes');
    });
    // Administración completa solo Administrador
    Route::middleware([CheckRol::class . ':Administrador'])->group(function () {
        Route::get('/admin/usuarios', [AdminController::class, 'usuarios'])->name('admin.usuarios');
        Route::post('/admin/usuarios', [AdminController::class, 'crearUsuario'])->name('admin.usuarios.crear');
        Route::post('/admin/usuarios/{id}/toggle', [AdminController::class, 'toggleActivo'])->name('admin.usuarios.toggle');
        Route::get('/admin/pacientes', [AdminController::class, 'pacientes'])->name('admin.pacientes');
        Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::get('/admin/backups', [AdminController::class, 'backups'])->name('admin.backups');
        Route::post('/admin/backups/generar', [AdminController::class, 'generarBackup'])->name('admin.backups.generar');
        Route::get('/admin/backups/descargar/{file}', [AdminController::class, 'descargarBackup'])->name('admin.backups.descargar');
    });
    // Módulo Técnico y Operativo (Todos los roles del personal)
    Route::middleware([CheckRol::class . ':Bioquimico,Administrador,Recepcionista'])->group(function () {
        // Ver la lista de órdenes pendientes
        Route::get('/ordenes/pendientes', [OrdenController::class, 'pendientes'])->name('ordenes.pendientes');
        // Módulo Muestras - Trazabilidad
        Route::get('/muestras', [\App\Http\Controllers\MuestraController::class, 'index'])->name('muestras.index');
        Route::get('/muestras/{id}', [\App\Http\Controllers\MuestraController::class, 'show'])->name('muestras.show');
    });
    // Actualización estado solo Bioquímico/Admin
    Route::middleware([CheckRol::class . ':Bioquimico,Administrador'])->group(function () {
        Route::post('/muestras/{id}/estado', [\App\Http\Controllers\MuestraController::class, 'actualizarEstado'])->name('muestras.estado');
    });

    // Portal Paciente - Mis análisis y Citas (solo rol Paciente)
    Route::middleware([CheckRol::class . ':Paciente'])->group(function () {
        Route::get('/paciente/citas', [\App\Http\Controllers\CitaController::class, 'misCitas'])->name('paciente.citas.index');
        Route::post('/paciente/citas', [\App\Http\Controllers\CitaController::class, 'agendar'])->name('paciente.citas.agendar');
        Route::post('/paciente/citas/{id}/cancelar', [\App\Http\Controllers\CitaController::class, 'cancelar'])->name('paciente.citas.cancelar');
    });

    // Gestión de Citas - Recepción y Administrador
    Route::middleware([CheckRol::class . ':Administrador,Recepcionista'])->group(function () {
        Route::get('/citas', [\App\Http\Controllers\CitaController::class, 'index'])->name('citas.index');
        Route::post('/citas/{id}/estado', [\App\Http\Controllers\CitaController::class, 'actualizarEstado'])->name('citas.estado');
    });

    // Plataforma QA – Automatización de pruebas
    Route::middleware([CheckRol::class . ':Administrador,Bioquimico,Recepcionista'])->group(function () {
        Route::get('/qa', [\App\Http\Controllers\QATestController::class, 'index'])->name('qa.index');
        Route::post('/qa/ejecutar', [\App\Http\Controllers\QATestController::class, 'ejecutar'])->name('qa.ejecutar');
    });

});