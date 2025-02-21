<?php

use App\Http\Controllers\MantenimientoController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('mantenimientos')->group(function () {
        Route::get('/', [MantenimientoController::class, 'listadoMantenimientos']);
        Route::get('/sin-paginacion', [MantenimientoController::class, 'listadoMantenimientosSelect']);
        Route::get('/reportes', [MantenimientoController::class, 'obtenerReportes']);
        Route::get('/{id}', [MantenimientoController::class, 'obtenerMantenimientoById']);
        Route::post('/', [MantenimientoController::class, 'crearMantenimiento']);
        Route::put('/{id}', [MantenimientoController::class, 'editarMantenimiento']);
        Route::delete('/{id}', [MantenimientoController::class, 'eliminarMantenimiento']);
    });
});

