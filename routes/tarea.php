<?php

use App\Http\Controllers\TareaController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('tareas')->group(function () {
        Route::get('/', [TareaController::class, 'listadoTareas']);
        Route::post('/', [TareaController::class, 'crearTarea']);
        Route::put('/{id}', [TareaController::class, 'editarTarea']);
        Route::delete('/{id}', [TareaController::class, 'eliminarTarea']);
    });
});
