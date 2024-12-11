<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Formulario;

class FormularioController extends Controller
{
    // Listar todos los formularios
    public function listadoFormularios()
    {
        $formularios = Formulario::with('modulo')->get(); // Incluye el módulo asociado
        return response()->json($formularios, 200);
    }

    // Crear un nuevo formulario
    public function crearFormulario(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'mod_id' => 'required|exists:modulos,id', // Asegura que el módulo exista
        ]);

        try {
            $formulario = Formulario::create($validatedData);

            return response()->json([
                'message' => 'Formulario creado exitosamente.',
                'formulario' => $formulario,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al crear el formulario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Mostrar un formulario específico
    public function editarFormulario($id)
    {
        $formulario = Formulario::with('modulo')->find($id);

        if (!$formulario) {
            return response()->json(['error' => 'Formulario no encontrado.'], 404);
        }

        return response()->json($formulario, 200);
    }

    // Actualizar un formulario
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'mod_id' => 'required|exists:modulos,id', // Asegura que el módulo asociado exista
        ]);

        $formulario = Formulario::find($id);

        if (!$formulario) {
            return response()->json(['error' => 'Formulario no encontrado.'], 404);
        }

        try {
            $formulario->update($validatedData);

            return response()->json([
                'message' => 'Formulario actualizado exitosamente.',
                'formulario' => $formulario,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al actualizar el formulario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // Eliminar un formulario
    public function eliminarFormulario($id)
    {
        $formulario = Formulario::find($id);

        if (!$formulario) {
            return response()->json(['error' => 'Formulario no encontrado.'], 404);
        }

        try {
            $formulario->delete();

            return response()->json([
                'message' => 'Formulario eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al eliminar el formulario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
