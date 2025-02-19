<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class grupoController extends Controller
{
    public function listadoGrupos(Request $request)
    {
        $codigo = $request->input("codigo");
        $nombre = $request->input("nombre");
        $isActivos = filter_var($request->input('estadoGrupos'), FILTER_VALIDATE_BOOLEAN);

        try {
            if ($isActivos == true) {
                $query = Grupo::activos();
            } else {
                $query = Grupo::inactivos();
            }

            if (!empty($codigo)) {
                $query->where('grupos.codigo', 'LIKE', "%{$codigo}%");
            }

            if (!empty($nombre)) {
                $query->where('grupos.nombre', 'LIKE', "%{$nombre}%");
            }

            $grupos = $query->paginate(5);
            // Log::info($grupos);

            $data = [
                'grupos' => $grupos->items(),
                'meta' => [
                    'current_page' => $grupos->currentPage(),
                    'last_page' => $grupos->lastPage(),
                    'per_page' => $grupos->perPage(),
                    'total' => $grupos->total(),
                ],
                'status' => 200
            ];
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el listado de grupos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listadoGruposSinPaginacion(Request $request)
    {
        try {
            $grupos = Grupo::all();
            return response()->json($grupos, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el listado de grupos.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //Trae las acciones organizadas por módulo y formulario, para crear  un grupo y el listado de grupos
    public function listadoAccionesGrupoById($id)
    {
        try {

            $modulos = Modulo::whereHas('formularios.acciones.grupos', function ($query) use ($id) {
                $query->where('grupos.id', $id);
            })
                ->with(['formularios.acciones' => function ($query) use ($id) {
                    $query->whereHas('grupos', function ($q) use ($id) {
                        $q->where('grupos.id', $id);
                    });
                }])
                ->get();

            return response()->json([
                'message' => 'Acciones organizadas por módulo y formulario asociadas al grupo obtenidas correctamente.',
                'modulos' => $modulos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las acciones asociadas al grupo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function crearGrupo(Request $request)
    {
        Log::info($request);

        $validatedData = $request->validate([
            'codigo' => 'required|string|max:50|unique:grupos,codigo',
            'nombre' => 'required|string|max:255|unique:grupos,nombre',
            'descripcion' => 'nullable|string|max:500',
            'estado' => 'required|boolean',
            'acciones' => 'nullable|array',
            'acciones.*' => 'integer|exists:acciones,id', // Cada acción debe existir en la tabla de acciones
        ], [
            'codigo.unique' => 'El código del grupo ya existe. Por favor, elija otro.',
            'nombre.unique' => 'El nombre del grupo ya existe. Por favor, elija otro.',

        ]);

        try {
            $grupo = Grupo::create($validatedData);
            $grupo->acciones()->sync($validatedData['acciones']);

            return response()->json([
                'message' => 'Grupo creado exitosamente.',
                'grupo' => $grupo,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo crear el grupo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarGrupo(Request $request, $id)
    {
        try {
            // Buscar el grupo
            $grupo = Grupo::find($id);

            if (!$grupo) {
                return response()->json([
                    'error' => 'Grupo no encontrado.',
                ], 404);
            }

            // Validar los datos de entrada
            $validatedData = $request->validate([
                'codigo' => 'required|string|max:50|unique:grupos,codigo,' . $grupo->id,
                'nombre' => 'required|string|max:255|unique:grupos,nombre,' . $grupo->id,
                'descripcion' => 'nullable|string|max:500',
                'estado' => 'required|boolean',
                'acciones' => 'nullable|array',
                'acciones.*' => 'integer|exists:acciones,id', // Cada acción debe existir en la tabla de acciones
            ], [
                'codigo.unique' => 'El código del grupo ya existe. Por favor, elija otro.',
                'nombre.unique' => 'El nombre del grupo ya existe. Por favor, elija otro.',
            ]);

            Log::info($validatedData);

            // Actualizar el grupo
            $grupo->update($validatedData);

            // Sincronizar las acciones (si se proporcionaron)
            if (isset($validatedData['acciones'])) {
                $grupo->acciones()->sync($validatedData['acciones']);
            }

            return response()->json([
                'message' => 'Grupo actualizado exitosamente.',
                'grupo' => $grupo,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el grupo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarGrupo($id)
    {
        try {
            $grupo = Grupo::find($id);

            if (!$grupo) {
                return response()->json([
                    'error' => 'Grupo no encontrado.',
                ], 404);
            }

            // Verificar si el grupo tiene elementos relacionados
            if ($grupo->usuarios()->exists() || $grupo->acciones()->exists()) {
                return response()->json([
                    'error' => 'No es posible eliminar el grupo porque tiene elementos relacionados.',
                ], 400);
            }

            // Eliminar el grupo
            $grupo->delete();

            return response()->json([
                'message' => 'Grupo eliminado exitosamente.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar el grupo.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //Esta funcion guarda en la tabla intermedia entre grupo y acciones
    public function asignarAcciones(Request $request, $grupoId)
    {
        // Validar que las acciones sean un array de IDs
        $validatedData = $request->validate([
            'acciones' => 'required|array',
            'acciones.*' => 'integer|exists:acciones,id',
        ]);

        $grupo = Grupo::find($grupoId);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        try {
            // Sincronizar las acciones para el grupo
            $grupo->acciones()->sync($validatedData['acciones']);

            return response()->json(['message' => 'Acciones asignadas al grupo exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudieron asignar las acciones', 'message' => $e->getMessage()], 500);
        }
    }

    public function asignarUsuarios(Request $request, $grupoId)
    {
        // Validar que los usuarios sean un array de IDs
        $validatedData = $request->validate([
            'usuarios' => 'required|array',
            'usuarios.*' => 'integer|exists:users,id',
        ]);

        $grupo = Grupo::find($grupoId);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        try {
            // Sincronizar los usuarios para el grupo
            $grupo->usuarios()->sync($validatedData['usuarios']);

            return response()->json(['message' => 'Usuarios asignados al grupo exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudieron asignar los usuarios', 'message' => $e->getMessage()], 500);
        }
    }

    public function listarUsuarios($grupoId)
    {
        $grupo = Grupo::with('usuarios')->find($grupoId);

        if (!$grupo) {
            return response()->json(['message' => 'Grupo no encontrado'], 404);
        }

        return response()->json([
            'message' => 'Usuarios obtenidos exitosamente',
            'usuarios' => $grupo->usuarios,
        ], 200);
    }
}
