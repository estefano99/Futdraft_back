<?php

namespace App\Http\Controllers;

use App\Helpers\ValidationHelpers;
use App\Models\Horario;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class reservaController extends Controller
{

    public function listadoReservas(Request $request)
    {
        Log::info($request);

        $start = $request->query('start');
        $end = $request->query('end');
        $cancha_id = $request->query('cancha_id');

        if (!$start || !$end || !$cancha_id) {
            return response()->json(["message" => "Parámetros de fecha o cancha_id faltantes"], 400);
        }

        $reservas = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->where('reservas.cancha_id', $cancha_id)
            ->whereBetween('reservas.fecha', [$start, $end])
            ->select('reservas.*', 'cancha.nro_cancha')
            ->get();

        $reservasConDuracion = $reservas->map(function ($reserva) {
            return calcularHorarioReserva($reserva);
        });

        return response()->json(["reservas" => $reservasConDuracion], 200);
    }

    public function obtenerReservasByIdUsuario($usuario_id, Request $request)
    {
        $nroCancha = $request->input('filtrarNroCancha');
        $fecha = $request->input('filtrarFecha'); // Fecha del filtro
        $isFinalizados = filter_var($request->input('isFinalizados'), FILTER_VALIDATE_BOOLEAN);

        // Calculamos la fecha actual directamente en el backend
        $fechaActual = Carbon::now()->format('Y-m-d H:i:s');

        $query = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->where('reservas.usuario_id', $usuario_id)
            ->where('reservas.fecha', $isFinalizados ? '<' : '>=', $fechaActual) // Trae reservas viejas(finalizadas) o Disponibles
            ->select('reservas.*', 'cancha.nro_cancha');

        // Filtrar por número de cancha
        if (!empty($nroCancha)) {
            $query->where('cancha.nro_cancha', 'LIKE', "%{$nroCancha}%");
        }

        // Filtrar por fecha de inicio
        if (!empty($fecha)) {
            $query->where('reservas.fecha', 'LIKE', "%{$fecha}%");
        }

        $reservas = $query->paginate(5);

        if ($reservas->isEmpty()) {
            return response()->json(["message" => "No se encontraron reservas"], 404);
        }

        // Procesar las reservas como antes
        $reservasConDuracion = $reservas->getCollection()->map(function ($reserva) {
            $fechaSinHora = date('Y-m-d', strtotime($reserva->fecha));
            $horario = Horario::where('cancha_id', $reserva->cancha_id)
                ->where('fecha', $fechaSinHora)
                ->first();

            $duracionEnMinutos = 0;
            if ($horario && $horario->duracion_turno) {
                list($horas, $minutos, $segundos) = explode(':', $horario->duracion_turno);
                $duracionEnMinutos = ($horas * 60) + $minutos + ($segundos / 60);
            }

            $reserva->start = $reserva->fecha;
            $reserva->end = date('Y-m-d H:i:s', strtotime($reserva->fecha) + ($duracionEnMinutos * 60));

            return $reserva;
        });

        $reservas->setCollection($reservasConDuracion);

        $data = [
            'reservas' => $reservas->items(),
            'meta' => [
                'current_page' => $reservas->currentPage(),
                'last_page' => $reservas->lastPage(),
                'per_page' => $reservas->perPage(),
                'total' => $reservas->total(),
            ],
        ];

        return response()->json($data, 200);
    }


    public function crearReserva(Request $request)
    {
        Log::info($request);

        $validator = Validator::make($request->all(), ValidationHelpers::reservaRules(), ValidationHelpers::reservaMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $existingReserva = Reserva::where('cancha_id', $request->cancha_id)
            ->where('fecha', $request->fecha)
            ->exists();

        if ($existingReserva) {
            return response()->json(['errors' => 'Ya existe una reserva para esta cancha en la fecha especificada, por favor seleccione otra.'], 422);
        }

        $reserva = new Reserva();
        $reserva->precio = $request->precio;
        $reserva->fecha = $request->fecha;
        $reserva->usuario_id = $request->usuario_id;
        $reserva->cancha_id = $request->cancha_id;

        $reserva->save();

        $reserva = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->where('reservas.id', $reserva->id)
            ->select('reservas.*', 'cancha.nro_cancha')
            ->first();

        $reserva = calcularHorarioReserva($reserva);

        return response()->json([
            "message" => "Turno {$reserva->fecha} reservado exitosamente.",
            "reserva" => $reserva
        ], 201);
    }

    public function editarReserva(Request $request, $id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json([
                'message' => 'Reserva no encontrada',
                'status' => 404
            ], 404);
        }

        $validator = Validator::make($request->all(), ValidationHelpers::reservaRules(), ValidationHelpers::reservaMessages());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reserva->precio = $request->precio;
        $reserva->fecha = $request->start;
        $reserva->cancha_id = $request->cancha_id;
        $reserva->save();

        // Obtener la reserva actualizada con el número de cancha
        $reservaActualizada = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->where('reservas.id', $reserva->id)
            ->select('reservas.*', 'cancha.nro_cancha')
            ->first();

        //Helper que calcula el start y el end en base al horario de la cancha.
        $reservaActualizada = calcularHorarioReserva($reservaActualizada);

        return response()->json([
            "message" => "Reserva editada exitosamente",
            "reserva" => $reservaActualizada
        ], 200);
    }
}
