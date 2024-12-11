<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\reservaController;

Route::prefix('cliente')->middleware('auth:sanctum')->group(function () {
    Route::prefix('reservas')->group(function () {
        Route::get('/', [reservaController::class, 'listadoReservas']);
        Route::get('/reportes', [reservaController::class, 'obtenerReportes']);
        Route::get('/admin-reservas', [reservaController::class, 'listadoReservasConUsuarios']);
        Route::post('/', [reservaController::class, 'crearReserva']);
        Route::get('/{id}', [reservaController::class, 'obtenerReservasByIdUsuario']);
        Route::put('/{id}', [reservaController::class, 'editarReserva']);
        Route::delete('/{id}', [reservaController::class, 'eliminarReserva']);
    });
});
