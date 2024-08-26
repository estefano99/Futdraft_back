<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ValidationHelpers;
use Carbon\Carbon;

class horarioController extends Controller
{

    public function listadoHorarios()
    {
        $currentDate = Carbon::now()->toDateString();

        $horarios = Horario::join('cancha', 'horario.cancha_id', '=', 'cancha.id')
            ->select(
                'horario.id as id',
                'horario.cancha_id',
                'horario.fecha',
                'horario.horario_apertura',
                'horario.horario_cierre',
                'horario.duracion_turno',
                'cancha.nro_cancha',
                'cancha.precio'
            )
            ->where('horario.fecha', '>=', $currentDate)
            ->orderBy('horario.fecha', 'asc')
            ->paginate(5);

        // Log::info($horarios);
        $data = [
            'horarios' => $horarios->items(),
            'meta' => [
                'current_page' => $horarios->currentPage(),
                'last_page' => $horarios->lastPage(),
                'per_page' => $horarios->perPage(),
                'total' => $horarios->total(),
            ],
            'status' => 200
        ];

        return response()->json($data, 200);
    }

    public function listadoHorario($cancha_id, $fecha)
    {

        $validator = Validator::make(
            [
                'cancha_id' => $cancha_id,
                'fecha' => $fecha,
            ],
            [
                'cancha_id' => 'required|integer',
                'fecha' => 'required|date_format:Y-m-d',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['error' => 'Validación fallida', 'messages' => $validator->errors()], 400);
        }

        $horario = Horario::where('cancha_id', $cancha_id)
        ->whereDate('fecha', $fecha)
        ->first();

        // Log::info($horario);

        if (!$horario) {
            $data = [
                'message' => 'No hay horarios creados en la fecha seleccionada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $data = [
            'message' => 'Horario encontrado',
            'horario' => $horario,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    public function crearHorario(Request $request)
    {

        //Se llama la funcion en app/helpers para validar los campos que llegan y los mensajes de errores personalizados de retorno.
        $validator = Validator::make($request->all(), ValidationHelpers::horarioRules(), ValidationHelpers::horarioMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Checkea que no exista ya una fecha para esa cancha en la tabla horario, osea deben ser unique las dos juntas.
        $existingHorario = Horario::where('cancha_id', $request->cancha_id)
            ->where('fecha', $request->fecha)
            ->first();

        if ($existingHorario) {
            return response()->json(['errors' => 'Ya existe un horario para esta cancha en la fecha especificada.'], 422);
        }

        $horario = Horario::create($request->all());

        // Obtener el horario creado con el join de la tabla cancha
        $horarioConCancha = Horario::join('cancha', 'horario.cancha_id', '=', 'cancha.id')
            ->select('horario.*', 'cancha.*')
            ->where('horario.id', '=', $horario->id)
            ->first();

        return response()->json([
            'message' => 'Horario creado exitosamente',
            'horario' => $horarioConCancha
        ], 201);
    }

    public function editarHorario(Request $request, $id)
    {
        $horario = Horario::find($id);
        Log::info($horario);

        if (!$horario) {
            $data = [
                'message' => 'Horario no encontrado',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        //Se llama la funcion en app/helpers para validar los campos que llegan y los mensajes de errores personalizados de retorno.
        $validator = Validator::make($request->all(), ValidationHelpers::horarioRules(), ValidationHelpers::horarioMessages());

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Verificar si existe un horario con la misma cancha_id y fecha pero que no sea el horario que estamos editando
        $existingHorario = Horario::where('cancha_id', $request->cancha_id)
            ->where('fecha', $request->fecha)
            ->where('id', '!=', $id) // Excluir el registro actual de la verificación
            ->first();

        if ($existingHorario) {
            return response()->json(['errors' => ['Ya existe un horario para esta cancha en la fecha especificada.']], 422);
        }

        try {
            // Actualizar el horario con los nuevos datos
            $horario->cancha_id = $request->cancha_id;
            $horario->fecha = $request->fecha;
            $horario->horario_apertura = $request->horario_apertura;
            $horario->horario_cierre = $request->horario_cierre;
            $horario->duracion_turno = $request->duracion_turno;
            Log::info($horario);
            $horario->save();

            // Obtener el horario creado con el join de la tabla cancha
            $horarioConCancha = Horario::join('cancha', 'horario.cancha_id', '=', 'cancha.id')
                ->select('horario.*', 'cancha.*')
                ->where('horario.id', '=', $horario->id)
                ->first();

            $data = [
                'message' => 'Horario actualizado exitosamente',
                'horario' => $horarioConCancha,
                'status' => 200
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json(['errors' => 'Ocurrió un error al actualizar el horario: ' . $e->getMessage()], 500);
        }
    }
}
