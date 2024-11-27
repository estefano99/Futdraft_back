<?php

namespace App\Http\Controllers;

use App\Models\Cancha;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class canchaController extends Controller
{

    public function listadoCanchas()
    {
        $canchas = Cancha::all();

        $data = [
            'canchas' => $canchas,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    public function crearCancha(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'nro_cancha' => 'required|numeric|unique:cancha',
            'precio' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'nro_cancha.required' => 'El número de cancha es obligatorio.',
            'nro_cancha.numeric' => 'El número de cancha debe ser un valor numérico.',
            'nro_cancha.unique' => 'El número de cancha ya está en uso.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un valor numérico.',
            'precio.regex' => 'El precio debe ser un número decimal con hasta dos lugares decimales.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cancha = Cancha::create([
            'nro_cancha' => $request->nro_cancha,
            'precio' => $request->precio
        ]);

        if (!$cancha) {
            $data = [
                'message' => "Error al crear la cancha",
                'status' => 500
            ];
            return response()->json($data, 500);
        }

        $cancha = [
            'cancha' => $cancha,
            'status' => 201
        ];
        return response()->json($cancha, 201);
    }

    public function editarCancha(Request $request, $id)
    {
        // Log::info('Datos de la solicitud:', $request->all());
        $cancha = Cancha::find($id);

        if (!$cancha) {
            $data = [
                'message' => 'Cancha no encontrada',
                'status' => 404
            ];
            return response()->json($data, 404);
        }

        $validator = Validator::make($request->all(), [
            'nro_cancha' => 'required|numeric|unique:cancha,nro_cancha,' . $id,
            'precio' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
        ], [
            'nro_cancha.required' => 'El número de cancha es obligatorio.',
            'nro_cancha.numeric' => 'El número de cancha debe ser un valor numérico.',
            'nro_cancha.unique' => 'El número de cancha ya está en uso.',
            'precio.required' => 'El precio es obligatorio.',
            'precio.numeric' => 'El precio debe ser un valor numérico.',
            'precio.regex' => 'El precio debe ser un número decimal con hasta dos lugares decimales.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cancha->nro_cancha = $request->nro_cancha;
        $cancha->precio = $request->precio;

        $cancha->save();

        $data = [
            'message'=>'Cancha actualizada exitosamente',
            'cancha' => $cancha,
            'status' => 200
        ];
        return response()->json($data, 200);
    }

    public function eliminarCancha($id)
{
    $cancha = Cancha::find($id);

    if (!$cancha) {
        return response()->json(["message" => "Cancha no encontrada"], 404);
    }

    // Verificar si hay horarios o reservas asociadas a esta cancha
    $asociaciones = DB::table('cancha')
        ->leftJoin('horario', 'cancha.id', '=', 'horario.cancha_id')
        ->leftJoin('reservas', 'cancha.id', '=', 'reservas.cancha_id')
        ->where('cancha.id', $id)
        ->where(function ($query) {
            $query->whereNotNull('horario.id') // Si hay horarios asociados
                  ->orWhereNotNull('reservas.id'); // Si hay reservas asociadas
        })
        ->exists();

    // Depuración
    Log::info($asociaciones);

    if ($asociaciones) {
        return response()->json([
            "message" => "No se puede eliminar esta cancha porque tiene horarios o reservas asociadas."
        ], 400);
    }

    // Eliminar la cancha si no tiene horarios ni reservas asociadas
    $cancha->delete();

    return response()->json(["message" => "Cancha eliminada exitosamente."], 200);
}

}
