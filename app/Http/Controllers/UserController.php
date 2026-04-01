<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    #[OA\Get(path: "/api/usuarios", summary: "Listar usuarios", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function index()
    {
        $users = User::with(['roles', 'permissions'])->get();
        return response()->json($users);
    }

    #[OA\Post(path: "/api/usuarios", summary: "Crear un nuevo usuario y asignar permisos", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        if (!empty($validated['permissions'])) {
            $user->givePermissionTo($validated['permissions']);
        }

        return response()->json($user->load('permissions'), 201);
    }

    #[OA\Get(path: "/api/usuarios/{usuario}", summary: "Ver detalle de usuario", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function show(User $usuario)
    {
        return response()->json($usuario->load(['roles', 'permissions']));
    }

    #[OA\Put(path: "/api/usuarios/{usuario}", summary: "Actualizar usuario", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function update(Request $request, User $usuario)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name'
        ]);

        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        if (array_key_exists('permissions', $validated)) {
            $usuario->syncPermissions($validated['permissions']);
        }

        return response()->json($usuario->load('permissions'));
    }

    #[OA\Delete(path: "/api/usuarios/{usuario}", summary: "Eliminar usuario", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function destroy(User $usuario)
    {
        $usuario->delete();
        return response()->json(null, 204);
    }

    #[OA\Get(path: "/api/permisos", summary: "Listar todos los permisos", security: [["sanctum" => []]], tags: ["Usuarios"])]
    public function permisos()
    {
        return response()->json(Permission::all());
    }
}
