<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: "/api/register",
        summary: "Registrar un nuevo usuario",
        tags: ["Autenticación"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["name", "email", "password"],
            properties: [
                new OA\Property(property: "name", type: "string", example: "Juan Perez"),
                new OA\Property(property: "email", type: "string", format: "email", example: "juan@test.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "secret123")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Usuario registrado exitosamente")]
    #[OA\Response(response: 422, description: "Error de validación")]
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ], 201);
    }

    #[OA\Post(
        path: "/api/login",
        summary: "Iniciar sesión",
        tags: ["Autenticación"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["email", "password"],
            properties: [
                new OA\Property(property: "email", type: "string", format: "email", example: "juan@test.com"),
                new OA\Property(property: "password", type: "string", format: "password", example: "secret123")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Login exitoso con Token Bearer")]
    #[OA\Response(response: 422, description: "Credenciales incorrectas")]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        $user->load(['roles', 'permissions']);
        $user->all_permissions = $user->getAllPermissions()->pluck('name');

        return response()->json([
            'user' => $user,
            'token' => $user->createToken('API Token')->plainTextToken
        ]);
    }

    #[OA\Post(
        path: "/api/logout",
        summary: "Cerrar sesión (Invalidar token)",
        security: [["sanctum" => []]],
        tags: ["Autenticación"]
    )]
    #[OA\Response(response: 200, description: "Sesión cerrada correctamente")]
    #[OA\Response(response: 401, description: "No autenticado")]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente'
        ]);
    }
}
