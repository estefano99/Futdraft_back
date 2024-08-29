<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

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

        //Para registrar un usuario de tipo CLIENTE
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'dni' => $request->dni,
            'nro_celular' => $request->nro_celular,
            'tipo_usuario' => 'cliente',
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('token-name')->plainTextToken,
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
            return response()->json([
                'error' => ['Las credenciales ingresadas son incorrectas.']
            ], 422);
        }

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('token-name')->plainTextToken,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(true);
    }
}
