<?php

namespace App\Http\Controllers;

use App\Models\Mantenimiento;
use App\Models\Tarea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class TareaController extends Controller
{

    public function listadoTareas(Request $request)
    {
        try {
            $descripcion = $request->input("descripcion");
            $fecha_asignacion = $request->input("fecha");
            $mantenimiento = $request->input("mantenimiento");

            Log::info($mantenimiento);

            // Iniciar la consulta con eager loading de la relación 'responsable'
            $query = Tarea::with(['mantenimiento', 'empleado']);

            if (!empty($descripcion)) {
                $query->where('descripcion', 'LIKE', "%{$descripcion}%");
            }
            if (!empty($mantenimiento)) {
                $query->whereHas('mantenimiento', function ($q) use ($mantenimiento) {
                    $q->where('descripcion', 'LIKE', "%{$mantenimiento}%");
                });
            }
            if (!empty($fecha_asignacion)) {
                $query->where('fecha_asignacion', 'LIKE', "%{$fecha_asignacion}%");
            }

            $tareas = $query->paginate(5);

            // Verificar si existen registros
            if ($tareas->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron Tareas.',
                    'tareas' => [],
                ], 200);
            }

            $data = [
                'tareas' => $tareas->items(),
                'meta' => [
                    'current_page' => $tareas->currentPage(),
                    'last_page' => $tareas->lastPage(),
                    'per_page' => $tareas->perPage(),
                    'total' => $tareas->total(),
                ],
                'status' => 200
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json([
                'message' => 'Ocurrió un error al obtener las tareas.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function crearTarea(Request $request)
    {

        Log::info($request);
        $validator = Validator::make($request->all(), [
            'empleado_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('tipo_usuario', 'empleado');
                }),
            ],
            'descripcion'          => 'required|string|max:255',
            'mantenimiento_id'     => 'required|integer|exists:mantenimientos,id',
            'fecha_asignacion'     => 'required|date_format:Y-m-d H:i:s',
            'fecha_completado'     => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $tarea = new Tarea([
                'descripcion' => $request->descripcion,
                'empleado_id' => $request->empleado_id,
                'mantenimiento_id' => $request->mantenimiento_id,
                'fecha_asignacion' => $request->fecha_asignacion,
                'fecha_completado' => $request->fecha_completado ?? null,
            ]);

            $tarea->save();

            // Cargar relaciones
            $tarea->load(['empleado', 'mantenimiento']);

            Log::info('tarea creada exitosamente', ['tarea' => $tarea]);

            return response()->json([
                'message' => 'tarea creadoa exitosamente.',
                'tarea' => $tarea,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la tarea.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarTarea(Request $request, $id)
    {
        $tarea = Tarea::find($id);
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada.',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'empleado_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('tipo_usuario', 'empleado');
                }),
            ],
            'descripcion'          => 'required|string|max:255',
            'mantenimiento_id'     => 'required|integer|exists:mantenimientos,id',
            'fecha_asignacion'     => 'required|date_format:Y-m-d H:i:s',
            'fecha_completado'     => 'nullable|date_format:Y-m-d H:i:s',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Actualizar los datos generales del tarea
            $tarea->descripcion = $request->descripcion;
            $tarea->empleado_id = $request->empleado_id;
            $tarea->mantenimiento_id = $request->mantenimiento_id;
            $tarea->fecha_asignacion = $request->fecha_asignacion;
            $tarea->fecha_completado = $request->fecha_completado ?? null;

            $tarea->save();

            // Cargar relaciones
            $tarea->load(['empleado', 'mantenimiento']);

            Log::info('tarea editada exitosamente', ['tarea' => $tarea]);

            return response()->json([
                'message' => 'tarea editada exitosamente.',
                'tarea' => $tarea,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al editar la tarea.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarTarea($id)
    {
        try {
            $tarea = Tarea::find($id);

            if (!$tarea) {
                return response()->json([
                    'message' => 'Tarea no encontrada.',
                ], 404);
            }

            $tarea->delete();

            return response()->json([
                'message' => 'Tarea eliminada exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Ocurrió un error al eliminar la Tarea.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
