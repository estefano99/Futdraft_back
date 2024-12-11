<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Accion;

class AccionController extends Controller
{
    // Listado de todas las acciones
    public function listadoAcciones()
    {
        try {
            $acciones = Accion::with('formulario')->get();
            return response()->json($acciones, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las acciones', 'message' => $e->getMessage()], 500);
        }
    }

    // Crear una acción
    public function crearAccion(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'for_id' => 'required|exists:formularios,id', // Validar que for_id exista en la tabla formularios
        ]);

        try {
            $accion = Accion::create($validatedData);

            return response()->json([
                'message' => 'Acción creada exitosamente.',
                'accion' => $accion,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo crear la acción.', 'message' => $e->getMessage()], 500);
        }
    }

    // Mostrar una acción específica
    public function mostrarAccion($id)
    {
        try {
            $accion = Accion::with('formulario')->find($id);

            if (!$accion) {
                return response()->json(['message' => 'Acción no encontrada.'], 404);
            }

            return response()->json($accion, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener la acción.', 'message' => $e->getMessage()], 500);
        }
    }

    // Editar una acción
    public function editarAccion(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'for_id' => 'required|exists:formularios,id',
        ]);

        try {
            $accion = Accion::find($id);

            if (!$accion) {
                return response()->json(['message' => 'Acción no encontrada.'], 404);
            }

            $accion->update($validatedData);

            return response()->json([
                'message' => 'Acción actualizada exitosamente.',
                'accion' => $accion,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo actualizar la acción.', 'message' => $e->getMessage()], 500);
        }
    }

    // Eliminar una acción
    public function eliminarAccion($id)
    {
        try {
            $accion = Accion::find($id);

            if (!$accion) {
                return response()->json(['message' => 'Acción no encontrada.'], 404);
            }

            $accion->delete();

            return response()->json(['message' => 'Acción eliminada exitosamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar la acción.', 'message' => $e->getMessage()], 500);
        }
    }
}


