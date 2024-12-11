<?php

use App\Http\Controllers\formularioController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('formularios')->group(function () {
        Route::get('/', [formularioController::class, 'listadoFormularios']);
        Route::post('/', [FormularioController::class, 'crearFormulario']);
        Route::put('/{id}', [FormularioController::class, 'editarFormulario']);
        Route::delete('/{id}', [FormularioController::class, 'eliminarFormulario']);
    });
});
