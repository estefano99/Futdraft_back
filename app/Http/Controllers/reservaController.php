<?php

namespace App\Http\Controllers;

use App\Helpers\ValidationHelpers;
use App\Models\Horario;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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


    //Se usa para administrar reservas.
    public function listadoReservasConUsuarios(Request $request)
    {
        Log::info($request);

        $start = $request->query('start');
        $end = $request->query('end');
        $cancha_id = $request->query('cancha_id');

        if (!$start || !$end || !$cancha_id) {
            return response()->json(["message" => "Parámetros de fecha o cancha_id faltantes"], 400);
        }

        try {
            $reservas = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
                ->join('users', 'reservas.usuario_id', '=', 'users.id')
                ->where('reservas.cancha_id', $cancha_id)
                ->whereBetween('reservas.fecha', [$start, $end])
                ->select(
                    'reservas.*',
                    'cancha.nro_cancha',
                    'users.id as usuario_id',
                    'users.nombre as usuario_nombre',
                    'users.apellido as usuario_apellido',
                    'users.email as usuario_email'
                )
                ->get();

            $reservasConDuracion = $reservas->map(function ($reserva) {
                return calcularHorarioReserva($reserva);
            });

            return response()->json(["reservas" => $reservasConDuracion], 200);
        } catch (\Exception $e) {
            return response()->json([
                "message" => "Error al obtener las reservas",
                "error" => $e->getMessage(),
            ], 500);
        }
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

        // Realiza el join con la tabla 'users' para incluir los datos del usuario
        $reserva = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->join('users', 'reservas.usuario_id', '=', 'users.id')
            ->where('reservas.id', $reserva->id)
            ->select(
                'reservas.*',
                'cancha.nro_cancha',
                'users.nombre as usuario_nombre',
                'users.apellido as usuario_apellido',
                'users.email as usuario_email'
            )
            ->first();

        // Calcula el horario de la reserva
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

        // Realiza el join con las tablas 'cancha' y 'users' para incluir datos adicionales
        $reservaActualizada = Reserva::join('cancha', 'reservas.cancha_id', '=', 'cancha.id')
            ->join('users', 'reservas.usuario_id', '=', 'users.id')
            ->where('reservas.id', $reserva->id)
            ->select(
                'reservas.*',
                'cancha.nro_cancha',
                'users.nombre as usuario_nombre',
                'users.apellido as usuario_apellido',
                'users.email as usuario_email'
            )
            ->first();

        // Calcula el horario de la reserva
        $reservaActualizada = calcularHorarioReserva($reservaActualizada);

        return response()->json([
            "message" => "Reserva editada exitosamente",
            "reserva" => $reservaActualizada
        ], 200);
    }

    public function eliminarReserva($id)
    {
        try {
            $reserva = Reserva::find($id);

            if (!$reserva) {
                return response()->json([
                    'message' => 'Reserva no encontrada',
                    'status' => 404
                ], 404);
            }

            $reserva->delete();
            return response()->json([
                'message' => 'Reserva eliminada exitosamente',
                'status' => 200
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la reserva',
                'error' => $e->getMessage(),
                'status' => 500
            ], 500);
        }
    }


    public function obtenerReportes(Request $request)
    {
        // Parsear la fecha completa y extraer el año
        $year = $request->input('year');

        try {
            // Obtener la cantidad de reservas agrupadas por mes del año actual
            $reservasPorMes = DB::table('reservas')
                ->selectRaw('MONTH(fecha) as mes, COUNT(*) as cantidad')
                ->whereYear('fecha', $year)
                ->groupBy('mes')
                ->pluck('cantidad', 'mes'); // Devuelve un array con clave mes y valor cantidad

            // Inicializar un array con 0 para cada mes
            $datos = array_fill(1, 12, 0); // Llena un array con 12 elementos inicializados en 0

            // Rellenar los meses con los datos reales de la consulta
            foreach ($reservasPorMes as $mes => $cantidad) {
                $datos[$mes] = $cantidad;
            }

            return response()->json([
                'message' => 'Datos obtenidos exitosamente.',
                'datos' => array_values($datos), // Devuelve solo los valores como array numérico
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los reportes.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
