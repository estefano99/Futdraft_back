<?php

use App\Http\Controllers\AuditoriaMantenimientoController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('auditoria-mantenimiento')->group(function () {
        Route::get('/', [AuditoriaMantenimientoController::class, 'listadoAuditoriaMantenimiento']);
    });
});
