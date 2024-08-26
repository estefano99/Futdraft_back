<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CanchaController;

Route::prefix('admin')->group(function () {
    Route::prefix('canchas')->group(function () {
        Route::get('/', [CanchaController::class, 'listadoCanchas']);
        Route::post('/', [CanchaController::class, 'crearCancha']);
        Route::get('/{id}', [CanchaController::class, 'show']);
        Route::put('/{id}', [CanchaController::class, 'editarCancha']);
        Route::delete('/{id}', [CanchaController::class, 'destroy']);
    });
});
