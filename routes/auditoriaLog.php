<?php

use App\Http\Controllers\AuditoriaLogController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    Route::prefix('auditoria-log')->group(function () {
        Route::get('/', [AuditoriaLogController::class, 'listadoAuditoriaLog']);
    });
});
