<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    // Inicio de sesión del usuario
    public function login(Request $request)
    {
        // Validación de datos del formulario
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()
            ], 400);
        }

        // JWTAuth and attempt
        $token = JWTAuth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'type' => $request->input('type'),
        ]);

        if (!empty($token)) {
            $usuario = Auth::user();

            switch ($usuario->type) {
                case 'cliente':
                    $usuario = User::with('customer')->find($usuario->id);
                    break;
                case 'taller':
                    $usuario = User::with('workshop')->find($usuario->id);
                    break;
                case 'tecnico':
                    $usuario = User::with('technician')->find($usuario->id);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Usuario autenticado con éxito.',
                'data' => [
                    'token' => $token,
                    'user' => $usuario,
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'error' => 'Las credenciales ingresadas son incorrectas.'
        ], 401);
    }

    // public function login(Request $request)
    // {
    //     // Validación de datos del formulario
    //     $validator = Validator::make($request->all(), [
    //         'email' => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'error' => $validator->errors()
    //         ], 400);
    //     }

    //     // JWTAuth and attempt
    //     $token = JWTAuth::attempt([
    //         'email' => $request->input('email'),
    //         'password' => $request->input('password'),
    //     ]);

    //     if (!empty($token)) {
    //         $usuario = Auth::user();
    //         $email = $usuario->email;

    //         // Obtengo todos los usuarios asociados al email ingresado
    //         $users = User::where('email', $email)->with('customer', 'workshop', 'technician')->get();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Usuario autenticado con éxito.',
    //             'token' => $token,
    //             'data' => $users,
    //         ], 200);
    //     }

    //     return response()->json([
    //         'status' => false,
    //         'error' => 'Las credenciales ingresadas son incorrectas.'
    //     ], 401);
    // }


    // Cierre de sesión del usuario
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'status' => true,
            'message' => 'La sesión se ha cerrado con éxito.'
        ]);
    }

    public function profile()
    {
        $usuario = auth()->user();

        switch ($usuario->type) {
            case 'cliente':
                $usuario = User::with('customer')->find($usuario->id);
                break;
            case 'taller':
                $usuario = User::with('workshop')->find($usuario->id);
                break;
            case 'tecnico':
                $usuario = User::with('technician')->find($usuario->id);
                break;
        }

        return response()->json([
            'status' => true,
            'message' => 'Datos del perfil del usuario',
            'usuario' => $usuario
        ]);
    }

    // Para generar el valor del token actualizado
    public function refreshToken()
    {
        $newToken = auth()->refresh();

        return response()->json([
            "status" => true,
            "message" => "Nuevo token de acceso generado",
            "token" => $newToken
        ]);
    }
}
