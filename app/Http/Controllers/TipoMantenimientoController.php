<?php

namespace App\Http\Controllers;

use App\Models\TipoMantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TipoMantenimientoController extends Controller
{
    public function listadoTiposMantenimiento()
    {
        try {
            // Obtener todos los tipos de mantenimiento
            $tiposMantenimiento = TipoMantenimiento::all();

            // Verificar si existen registros
            if ($tiposMantenimiento->isEmpty()) {
                return response()->json([
                    'message' => 'No se encontraron tipos de mantenimiento.',
                    'tiposMantenimiento' => [],
                ], 200);
            }

            // Respuesta exitosa con los registros
            return response()->json([
                'message' => 'Tipos de mantenimiento obtenidos correctamente.',
                'tiposMantenimiento' => $tiposMantenimiento,
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier excepción
            return response()->json([
                'message' => 'Ocurrió un error al obtener los tipos de mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function crearTipoMantenimiento(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'descripcion' => 'required|string|max:255|unique:tipos_mantenimiento,descripcion',
        ], [
            'descripcion.required' => 'La descripción es obligatoria.',
            'descripcion.string' => 'La descripción debe ser un texto.',
            'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
            'descripcion.unique' => 'El tipo de mantenimiento ya existe. Por favor, elija otro.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación.',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Crear el registro
            $tipoMantenimiento = TipoMantenimiento::create([
                'descripcion' => $request->descripcion,
            ]);

            // Respuesta de éxito
            return response()->json([
                'message' => 'Tipo de mantenimiento creado exitosamente.',
                'tipoMantenimiento' => $tipoMantenimiento,
            ], 201);
        } catch (\Exception $e) {
            // Manejo de errores inesperados
            return response()->json([
                'message' => 'Error al crear el tipo de mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarTipoMantenimiento(Request $request, $id)
    {
        try {
            // Validar los datos del request
            $validator = Validator::make($request->all(), [
                'descripcion' => 'required|string|max:255|unique:tipos_mantenimiento,descripcion,' . $id,
            ], [
                'descripcion.required' => 'La descripción es obligatoria.',
                'descripcion.string' => 'La descripción debe ser un texto.',
                'descripcion.max' => 'La descripción no puede superar los 255 caracteres.',
                'descripcion.unique' => 'El tipo de mantenimiento ya existe. Por favor, elija otro.',
            ]);

            // Si falla la validación, devolver errores
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Error de validación.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Buscar el tipo de mantenimiento por ID
            $tipoMantenimiento = TipoMantenimiento::find($id);

            if (!$tipoMantenimiento) {
                return response()->json([
                    'message' => 'Tipo de mantenimiento no encontrado.',
                ], 404);
            }

            // Actualizar el registro
            $tipoMantenimiento->descripcion = $request->descripcion;
            $tipoMantenimiento->save();

            return response()->json([
                'message' => 'Tipo de mantenimiento actualizado exitosamente.',
                'tipoMantenimiento' => $tipoMantenimiento,
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier error inesperado
            return response()->json([
                'message' => 'Ocurrió un error al actualizar el tipo de mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarTipoMantenimiento($id)
    {
        try {
            // Buscar el tipo de mantenimiento por ID
            $tipoMantenimiento = TipoMantenimiento::find($id);

            if (!$tipoMantenimiento) {
                return response()->json([
                    'message' => 'Tipo de mantenimiento no encontrado.',
                ], 404);
            }

            // Eliminar el registro
            $tipoMantenimiento->delete();

            return response()->json([
                'message' => 'Tipo de mantenimiento eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            // Manejar cualquier error inesperado
            return response()->json([
                'message' => 'Ocurrió un error al eliminar el tipo de mantenimiento.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
