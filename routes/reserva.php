<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\reservaController;

Route::prefix('cliente')->group(function () {
    Route::prefix('reservas')->group(function () {
        Route::get('/', [reservaController::class, 'listadoReservas']);
        Route::post('/', [reservaController::class, 'crearReserva']);
        Route::get('/{id}', [reservaController::class, 'obtenerReservas']);
        Route::put('/{id}', [reservaController::class, 'editarReserva']);
        Route::delete('/{id}', [reservaController::class, 'eliminarReserva']);
    });
});
