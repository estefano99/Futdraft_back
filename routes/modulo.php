<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ModuloController;

Route::prefix('admin')->group(function () {
    Route::prefix('modulos')->group(function () {
        Route::get('/', [ModuloController::class, 'listadoModulos']);
        Route::get('acciones-organizadas', [ModuloController::class, 'listadoAccionesOrganizadas']); //Trae las acciones organizadas por módulo y formulario, para crear  un grupo y el listado de grupos
        Route::post('/', [ModuloController::class, 'crearModulo']);
        Route::get('/{id}', [ModuloController::class, 'mostrarModulo']);
        Route::put('/{id}', [ModuloController::class, 'editarModulo']);
        Route::delete('/{id}', [ModuloController::class, 'eliminarModulo']);
    });
});
