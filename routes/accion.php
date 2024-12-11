<?php

use App\Http\Controllers\AccionController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('acciones')->group(function () {
        Route::post('/', [AccionController::class, 'crearAccion']);
        Route::get('/', [AccionController::class, 'listadoAcciones']);
        Route::get('/{id}', [AccionController::class, 'detalleAccion']);
        Route::put('/{id}', [AccionController::class, 'editarAccion']);
        Route::delete('/{id}', [AccionController::class, 'eliminarAccion']);
    });
});
