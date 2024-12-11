<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HorarioController;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::prefix('horarios')->group(function () {
        Route::get('/', [HorarioController::class, 'listadoHorarios']);
        Route::get('/fechas/{cancha_id}', [HorarioController::class, 'listadoFechasConHorarios']);
        Route::get('{id}/{fecha}', [HorarioController::class, 'listadoHorario']);
        Route::post('/', [HorarioController::class, 'crearHorario']);
        Route::put('{id}', [HorarioController::class, 'editarHorario']);
        Route::delete('{id}', [HorarioController::class, 'eliminarHorario']);
    });
});
