<?php

use App\Http\Controllers\AuditoriaReservaController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('auditoria-reserva')->group(function () {
        Route::get('/', [AuditoriaReservaController::class, 'listadoAuditoriaReserva']);
    });
});
