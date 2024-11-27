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
    Route::get('/user', function (Request $request) {
        return response()->json($request->user());
    })->middleware('auth:sanctum');
});
