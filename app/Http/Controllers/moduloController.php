<?php

namespace App\Http\Controllers;

use App\Models\Modulo;
use Illuminate\Http\Request;

class moduloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function listadoModulos()
    {
        $modulos = Modulo::all();

        return response()->json($modulos, 200);
    }

    //Trae las acciones organizadas por módulo y formulario, para crear  un grupo y el listado de grupos
    public function listadoAccionesOrganizadas()
    {
        try {
            // Obtener todos los módulos con formularios y acciones
            $modulos = Modulo::with('formularios.acciones')->get();

            return response()->json([
                'message' => 'Acciones organizadas por módulo y formulario obtenidas correctamente.',
                'modulos' => $modulos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las acciones organizadas.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function crearModulo(Request $request)
    {
        // Validación de datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255|unique:modulos,nombre',
        ], [
            'nombre.unique' => 'El nombre del módulo ya está en uso.',
        ]);


        try {
            // Crear y guardar el módulo
            $modulo = Modulo::create([
                'nombre' => $validatedData['nombre'],
            ]);

            return response()->json([
                'message' => 'Módulo creado exitosamente.',
                'modulo' => $modulo,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo crear el módulo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function editarModulo(Request $request, $id)
    {
        // Validar datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        try {
            // Buscar el módulo por ID
            $modulo = Modulo::find($id);

            if (!$modulo) {
                return response()->json([
                    'error' => 'Módulo no encontrado.',
                ], 404);
            }

            // Actualizar el nombre del módulo
            $modulo->nombre = $validatedData['nombre'];
            $modulo->save();

            return response()->json([
                'message' => 'Módulo actualizado exitosamente.',
                'modulo' => $modulo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el módulo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarModulo($id)
    {
        try {
            // Buscar el módulo por ID
            $modulo = Modulo::find($id);

            if (!$modulo) {
                return response()->json([
                    'error' => 'Módulo no encontrado.',
                ], 404);
            }

            // Eliminar el módulo
            $modulo->delete();

            return response()->json([
                'message' => 'Módulo eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar el módulo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
