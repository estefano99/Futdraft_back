<?php

use App\Http\Controllers\horarioController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/cancha.php';
require __DIR__ . '/horario.php';
require __DIR__ . '/reserva.php';
require __DIR__ . '/tipoMantenimiento.php';
require __DIR__ . '/mantenimiento.php';
require __DIR__ . '/tarea.php';
require __DIR__ . '/auditoriaLog.php';
require __DIR__ . '/auditoriaMantenimiento.php';
require __DIR__ . '/auditoriaReserva.php';

//Modulo de seguridad
require __DIR__ . '/user.php';
require __DIR__ . '/modulo.php';
require __DIR__ . '/formulario.php';
require __DIR__ . '/grupo.php';
require __DIR__ . '/accion.php';
