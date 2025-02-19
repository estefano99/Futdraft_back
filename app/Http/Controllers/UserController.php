<?php

namespace App\Http\Controllers;

use App\Helpers\ModuloHelper;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Models\Accion;
use App\Models\AuditoriaLog;
use App\Models\Grupo;
use App\Models\Modulo;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
                'dni' => 'required|string|unique:users',
                'nro_celular' => 'required|string',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'apellido.required' => 'El apellido es obligatorio.',
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'El email debe ser una dirección válida.',
                'email.unique' => 'El email ya está registrado.',
                'password.required' => 'La contraseña es obligatoria.',
                'dni.required' => 'El DNI es obligatorio.',
                'dni.unique' => 'El DNI ya está registrado.',
                'nro_celular.required' => 'El número de celular es obligatorio.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Registrar un usuario de tipo CLIENTE
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dni' => $request->dni,
            'nro_celular' => $request->nro_celular,
            'tipo_usuario' => 'cliente',
        ]);

        // Obtener las acciones que el cliente puede realizar
        $accionesCliente = Accion::whereIn('nombre', ['seleccionar cancha', 'editar turno', 'eliminar turno'])->pluck('id');

        // Asignar las acciones al usuario
        $user->acciones()->sync($accionesCliente);

        // Obtener las acciones totales del usuario (directas y de grupos)
        $accionesUsuario = $user->acciones()->pluck('id')->toArray();
        $accionesGrupos = $user->grupos()->with('acciones')->get()
            ->flatMap(fn($grupo) => $grupo->acciones->pluck('id'))
            ->toArray();

        // Unir las acciones totales, osea los dos arreglos de id de acciones
        $accionesTotales = array_unique(array_merge($accionesUsuario, $accionesGrupos));

        // Usar el helper para obtener los módulos
        $modulos = ModuloHelper::obtenerModulos($accionesTotales);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('token-name')->plainTextToken,
            'modulos' => $modulos,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Las credenciales ingresadas son incorrectas.'], 422);
        }

        if ($user->estado !== 1) {
            return response()->json(['error' => 'El usuario está inactivo.'], 403);
        }

        $accionesUsuario = $user->acciones()->pluck('id')->toArray();
        $accionesGrupos = $user->grupos()->with('acciones')->get()
            ->flatMap(fn($grupo) => $grupo->acciones->pluck('id'))
            ->toArray();

        $accionesTotales = array_unique(array_merge($accionesUsuario, $accionesGrupos));

        // Usar el helper para obtener los módulos
        $modulos = ModuloHelper::obtenerModulos($accionesTotales);

        // Registrar el evento de inicio de sesión
        AuditoriaLog::create([
            'user_id' => $user->id,
            'evento' => 'login',
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('token-name')->plainTextToken,
            'modulos' => $modulos,
        ], 200);
    }

    public function getUser(Request $request)
    {
        try {
            // Obtener el usuario autenticado
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado.'], 401);
            }

            // Obtener las acciones del usuario
            $accionesUsuario = $user->acciones()->pluck('id')->toArray();
            $accionesGrupos = $user->grupos()->with('acciones')->get()
                ->flatMap(fn($grupo) => $grupo->acciones->pluck('id'))
                ->toArray();
            $accionesTotales = array_unique(array_merge($accionesUsuario, $accionesGrupos));

            // Obtener los módulos, formularios y acciones asociadas
            $modulos = ModuloHelper::obtenerModulos($accionesTotales);

            return response()->json([
                'user' => $user,
                'modulos' => $modulos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener el usuario.'], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        // Registrar el evento de cierre de sesión
        AuditoriaLog::create([
            'user_id' => $request->user()->id,
            'evento' => 'logout',
        ]);

        return response()->json(true);
    }

    public function listadoUsuarios(Request $request)
    {

        Log::info($request);
        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $estado = $request->input('estado');
        $estado = filter_var($estado, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        try {
            if ($estado == true) {
                $query = User::activos();
            } else {
                $query = User::inactivos();
            }

            if ($nombre) {
                $query->where('nombre', 'like', '%' . $nombre . '%');
            }

            if ($apellido) {
                $query->where('apellido', 'like', '%' . $apellido . '%');
            }

            $usuarios = $query->paginate(5);
            $data = [
                'usuarios' => $usuarios->items(),
                'meta' => [
                    'current_page' => $usuarios->currentPage(),
                    'last_page' => $usuarios->lastPage(),
                    'per_page' => $usuarios->perPage(),
                    'total' => $usuarios->total(),
                ],
                'status' => 200
            ];

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el listado de usuarios.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listadoUsuariosSinPaginacion()
    {

        try {

            $usuarios = User::all();

            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el listado de usuarios.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listadoAccionesUsuarioById($id)
    {
        try {
            //* Traer los módulos con sus formularios y acciones asociadas al usuario específico
            $modulos = Modulo::whereHas('formularios.acciones.usuarios', function ($query) use ($id) {
                $query->where('usuario_accion.usu_id', $id);
            })
                ->with(['formularios.acciones' => function ($query) use ($id) {
                    $query->whereHas('usuarios', function ($q) use ($id) {
                        $q->where('usuario_accion.usu_id', $id);
                    });
                }])
                ->get();

            $grupos = Grupo::whereHas('acciones.usuarios', function ($query) use ($id) {
                $query->where('usuario_accion.usu_id', $id);
            })
                ->with('acciones')
                ->get();

            return response()->json([
                'message' => 'Acciones organizadas y grupos obtenidos correctamente.',
                'modulos' => $modulos,
                'grupos' => $grupos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las acciones asociadas al usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    //Devuelve solo las acciones del usuario, para habilitar los botones en el front
    public function listadoAcciones($id)
    {
        try {
            // Buscar al usuario por ID
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no encontrado.',
                ], 404);
            }

            // Obtener las acciones asignadas directamente al usuario
            $accionesUsuario = $usuario->acciones()->pluck('nombre')->toArray();

            // Obtener las acciones asignadas a través de los grupos del usuario
            $accionesGrupos = $usuario->grupos()->with('acciones')->get()
                ->flatMap(fn($grupo) => $grupo->acciones->pluck('nombre'))
                ->toArray();

            // Unificar las acciones, eliminando duplicados
            $accionesTotales = array_unique(array_merge($accionesUsuario, $accionesGrupos));

            // Retornar las acciones
            return response()->json([
                'acciones' => $accionesTotales,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error al listar acciones del usuario: ' . $e->getMessage());

            return response()->json([
                'error' => 'No se pudieron obtener las acciones del usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function listadoGruposUsuarioById($id)
    {
        try {
            // Obtener los grupos asociados al usuario específico
            $grupos = Grupo::whereHas('usuarios', function ($query) use ($id) {
                $query->where('grupo_usuario.user_id', $id);
            })->get();

            return response()->json([
                'message' => 'Grupos asociados al usuario obtenidos correctamente.',
                'grupos' => $grupos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los grupos asociados al usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function crearUsuario(Request $request)
    {
        Log::info($request);
        // Validar los datos de entrada
        $validator = Validator::make(
            $request->all(),
            [
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'acciones' => 'nullable|array',
                'acciones.*' => 'integer|exists:acciones,id',
                'grupos' => 'nullable|array',
                'grupos.*' => 'integer|exists:grupos,id',
                'tipo_usuario' => 'nullable|in:cliente,admin,empleado',
            ],
            [
                'nombre.required' => 'El nombre es obligatorio.',
                'apellido.required' => 'El apellido es obligatorio.',
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'El email debe ser una dirección válida.',
                'email.unique' => 'El email ya está registrado.',
                'tipo_usuario.in' => 'El tipo de usuario debe ser cliente, admin o empleado.',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $password = Str::random(12);

            $user = new User([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'email' => $request->email,
                'password' => Hash::make($password),
                'nro_celular' => $request->nro_celular ?? null,
                'dni' => $request->dni ?? null,
                'estado' => $request->estado ?? 1,
                'tipo_usuario' => $request->tipo_usuario ?? 'cliente',
            ]);

            // Asigna el password generado al atributo temporal en el model, luego se envia al OBSERVER
            $user->temporalPassword = $password;

            // Guarda el usuario en la base de datos
            $user->save();

            // Sincronizar las acciones (si se proporcionaron)
            if ($request->has('acciones') && is_array($request->acciones)) {
                $user->acciones()->sync($request->acciones);
            }

            // Sincronizar los grupos (si se proporcionaron)
            if ($request->has('grupos') && is_array($request->grupos)) {
                $user->grupos()->sync($request->grupos);
            }

            return response()->json([
                'message' => 'Usuario creado exitosamente.',
                'user' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo crear el usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function editarUsuario(Request $request, $id)
    {
        try {
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no encontrado.',
                ], 404);
            }

            $validatedData = $request->validate([
                'nombre' => 'required|string|max:255',
                'apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $usuario->id,
                'estado' => 'required|boolean',
                'nro_celular' => 'nullable|string',
                'dni' => 'nullable|string',
                'acciones' => 'nullable|array',
                'acciones.*' => 'integer|exists:acciones,id',
                'grupos' => 'nullable|array',
                'grupos.*' => 'integer|exists:grupos,id',
                'tipo_usuario' => 'nullable|in:cliente,admin,empleado',
            ]);

            $usuario->update([
                'nombre' => $validatedData['nombre'],
                'apellido' => $validatedData['apellido'],
                'email' => $validatedData['email'],
                'estado' => $validatedData['estado'],
                'nro_celular' => $validatedData['nro_celular'] ?? null,
                'dni' => $validatedData['dni'] ?? null,
                'tipo_usuario' => $validatedData['tipo_usuario'],
            ]);

            if (isset($validatedData['acciones'])) {
                $usuario->acciones()->sync($validatedData['acciones']);
            }

            if (isset($validatedData['grupos'])) {
                $usuario->grupos()->sync($validatedData['grupos']);
            }

            $accionesUsuario = $usuario->acciones()->pluck('id')->toArray();
            $accionesGrupos = $usuario->grupos()->with('acciones')->get()
                ->flatMap(fn($grupo) => $grupo->acciones->pluck('id'))
                ->toArray();

            $accionesTotales = array_unique(array_merge($accionesUsuario, $accionesGrupos));

            // Usar el helper para obtener los módulos
            $modulos = ModuloHelper::obtenerModulos($accionesTotales);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'usuario' => $usuario,
                'modulos' => $modulos,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function eliminarUsuario($id)
    {
        try {
            $usuario = User::findOrFail($id);

            // Eliminar relaciones en las tablas pivote
            $usuario->acciones()->detach(); // Elimina las relaciones con acciones
            $usuario->grupos()->detach(); // Elimina las relaciones con grupos

            // Eliminar el usuario
            $usuario->delete();

            return response()->json(['message' => 'Usuario eliminado exitosamente.'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se pudo eliminar el usuario.', 'message' => $e->getMessage()], 500);
        }
    }

    public function resetearClave($id)
    {
        try {
            $usuario = User::find($id);
            if (!$usuario) {
                return response()->json(['error' => 'Usuario no encontrado.'], 404);
            }

            // Generar una nueva clave temporal
            $password = Str::random(8);

            // Actualizar la clave y asignarla al atributo temporalPassword
            $usuario->password = Hash::make($password);
            $usuario->temporalPassword = $password;

            // Guardar los cambios en la base de datos, y llama a OBSERVER para enviar mail
            $usuario->save();

            return response()->json(['message' => 'Clave reseteada exitosamente, revise su Email.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo resetear la clave del usuario.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    public function cambiarClave(Request $request, $id)
    {
        try {
            // Buscar el usuario
            $usuario = User::find($id);

            if (!$usuario) {
                return response()->json([
                    'error' => 'Usuario no encontrado.',
                ], 404);
            }

            // Validar las claves
            $validatedData = $request->validate([
                'claveActual' => 'required|string',
                'nuevaClave' => 'required|string|min:6|confirmed', // confirmed requiere nuevaClave_confirmation
            ], [
                'claveActual.required' => 'La clave actual es obligatoria.',
                'nuevaClave.required' => 'La nueva clave es obligatoria.',
                'nuevaClave.min' => 'La nueva clave debe tener al menos 6 caracteres.',
                'nuevaClave.confirmed' => 'La confirmación de la nueva clave no coincide.',
            ]);

            // Verificar si la clave actual coincide con la almacenada en la base de datos
            if (!Hash::check($validatedData['claveActual'], $usuario->password)) {
                return response()->json([
                    'error' => 'La clave actual es incorrecta.',
                ], 403);
            }

            // Actualizar la clave en la base de datos
            $usuario->update([
                'password' => Hash::make($validatedData['nuevaClave']),
            ]);

            return response()->json([
                'message' => 'Clave actualizada exitosamente.',
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'No se pudo actualizar la clave.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
