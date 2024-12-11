<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;

Route::prefix('auth')->group(function () {
    // Registro de un nuevo usuario
    Route::post('register', [UserController::class, 'register']);

    // Login de usuario y obtención de token
    Route::post('login', [UserController::class, 'login']);

    // Logout del usuario (requiere estar autenticado)
    Route::post('logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

    // Obtener datos del usuario autenticado
    Route::get('/user', [UserController::class, 'getUser'])->middleware('auth:sanctum');

    // Crud USUARIOS
    Route::get('/usuarios', [UserController::class, 'listadoUsuarios']);
    Route::get('/usuarios-acciones/{id}', [UserController::class, 'listadoAccionesUsuarioById']); //Trae acciones, form y modulo por id del usuario
    Route::get('/usuarios/{id}/acciones', [UserController::class, 'listadoAcciones']); //Trae solo acciones
    Route::get('/usuarios-grupos/{id}', [UserController::class, 'listadoGruposUsuarioById']); //Trae grupos asociados al usuario por id del usuario
    Route::get('/usuarios-sin-paginacion', [UserController::class, 'listadoUsuariosSinPaginacion']); //Trae Usuarios sin paginacion
    Route::put('/usuarios-cambiar-clave/{id}', [UserController::class, 'cambiarClave'])->middleware('auth:sanctum');
    Route::post('/usuarios', [UserController::class, 'crearUsuario']);
    Route::put('/usuarios/{id}', [UserController::class, 'editarUsuario']);
    Route::delete('/usuarios/{id}', [UserController::class, 'eliminarUsuario']);
    Route::post('/usuarios-resetear-clave/{id}', [UserController::class, 'resetearClave']);
});
