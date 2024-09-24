<?php

namespace App\Http\Controllers;

use App\Helpers\ValidationHelpers;
use App\Models\Horario;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class reservaController extends Controller
{

    public function listadoReservas()
    {
        $reservas = Reserva::all();

        return response()->json(["reservas" => $reservas], 200);
    }

    public function obtenerReservas($usuario_id)
    {
        // Obtener las reservas del usuario
        $reservas = Reserva::where('usuario_id', $usuario_id)->get();

        if ($reservas->isEmpty()) {
            return response()->json(["message" => "No se encontró la reserva"], 404);
        }

        $reservasConDuracion = $reservas->map(function ($reserva) {

            // Extraer solo la parte de año-mes-día de la fecha de la reserva
            $fechaSinHora = date('Y-m-d', strtotime($reserva->fecha));

            // Buscar el horario correspondiente a la cancha y la fecha sin hora
            $horario = Horario::where('cancha_id', $reserva->cancha_id)
                ->where('fecha', $fechaSinHora)
                ->first();

            // Verificar si el horario existe y convertir la duración del turno de "01:00:00" a minutos
            if ($horario && $horario->duracion_turno) {
                list($horas, $minutos, $segundos) = explode(':', $horario->duracion_turno);
                $duracionEnMinutos = ($horas * 60) + $minutos + ($segundos / 60); // Convertir la duración a minutos
            }

            // Calcular la hora de finalización (end) en base a la duración en minutos
            $reserva->start = $reserva->fecha;
            $reserva->end = date('Y-m-d H:i:s', strtotime($reserva->fecha) + ($duracionEnMinutos * 60)); // Sumar duración en segundos
            Log::info($reserva);
            return $reserva;
        });

        return response()->json(["reservas" => $reservasConDuracion], 200);
    }

    public function crearReserva(Request $request)
    {

        Log::info($request);

        $validator = Validator::make($request->all(), ValidationHelpers::reservaRules(), ValidationHelpers::reservaMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Checkea que no exista ya una fecha para esa cancha en la tabla horario, osea deben ser unique las dos juntas.
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

        return response()->json([
            "message" => "Turno {$reserva->fecha} reservado correctamente.",
            "reserva" => $reserva
        ], 201);
    }

    public function editarReserva(Request $request, $id)
    {
        $reserva = Reserva::find($id);
        Log::info($reserva);

        if (!$reserva) {
            $data = [
                'message' => 'Reserva no encontrada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), ValidationHelpers::reservaRules(), ValidationHelpers::reservaMessages());
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $reserva->precio = $request->precio;
        $reserva->fecha = $request->fecha;
        $reserva->cancha_id = $request->cancha_id;

        $reserva->save();

        return response()->json([
            "message" => "Reserva editada correctamete",
            "reserva" => $reserva
        ], 200);
    }

    public function eliminarReserva($id)
    {
        $reserva = Reserva::find($id);

        if (!$reserva) {
            return response()->json(["message" => "No se encontro la reserva"], 404);
        }

        $reserva->delete();

        return response()->json(["message" => "Reserva eliminada correctamente"], 200);
    }
}
