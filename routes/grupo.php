<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GrupoController;

Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::prefix('grupos')->group(function () {
        // Rutas generales
        Route::get('/', [GrupoController::class, 'listadoGrupos']); // Listar grupos
        Route::get('/sin-paginacion', [GrupoController::class, 'listadoGruposSinPaginacion']); // Listar grupos
        Route::post('/', [GrupoController::class, 'crearGrupo']); // Crear un grupo

        // Rutas relacionadas a un grupo específico
        Route::get('/{id}', [GrupoController::class, 'verGrupo']); // Ver un grupo
        Route::put('/{id}', [GrupoController::class, 'editarGrupo']); // Editar un grupo
        Route::delete('/{id}', [GrupoController::class, 'eliminarGrupo']); // Eliminar un grupo

        // Rutas para gestionar acciones
        Route::post('/{id}/acciones', [GrupoController::class, 'asignarAcciones']); // Asignar acciones a un grupo
        Route::get('/{id}/acciones', [GrupoController::class, 'listadoAccionesGrupoById']); // Listar acciones de un grupo

        // Rutas para gestionar usuarios
        Route::post('/{id}/usuarios', [GrupoController::class, 'asignarUsuarios']); // Asignar usuarios a un grupo
        Route::get('/{id}/usuarios', [GrupoController::class, 'listarUsuarios']); // Listar usuarios de un grupo
    });
});
