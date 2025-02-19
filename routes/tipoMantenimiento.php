<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TipoMantenimientoController;

Route::prefix('admin')->group(function () {
    Route::prefix('tipos-mantenimiento')->group(function () {
        Route::get('/', [TipoMantenimientoController::class, 'listadoTiposMantenimiento']);
        Route::post('/', [TipoMantenimientoController::class, 'crearTipoMantenimiento']);
        Route::put('/{id}', [TipoMantenimientoController::class, 'editarTipoMantenimiento']);
        Route::delete('/{id}', [TipoMantenimientoController::class, 'eliminarTipoMantenimiento']);
    });
});
