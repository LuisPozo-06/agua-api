<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clientes = Cliente::all();
        return response()->json($clientes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|unique:clientes,telefono',
            'direccion' => 'required|string|max:255'
        ]);

        $cliente = Cliente::create([
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'direccion' => $request->direccion
        ]);

        return response()->json($cliente, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        return response()->json($cliente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cliente = Cliente::findOrFail($id);

        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'telefono' => 'sometimes|string|max:20|unique:clientes,telefono,' . $cliente->id,
            'direccion' => 'sometimes|string|max:255'
        ]);

        $cliente->update($request->all());
        return response()->json($cliente);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $cliente->delete();

        return response()->json([
            "mensaje" => "Cliente eliminado correctamente"
        ]);
    }

    public function pedidos(string $id)
    {
        $cliente = Cliente::findOrFail($id);
        $pedidos = $cliente->pedidos()->orderBy('fecha_pedido', 'desc')->get();

        return response()->json($pedidos);
    }

    public function buscarPorTelefono(string $telefono)
    {
        $cliente = Cliente::where('telefono', $telefono)->firstOrFail();
        return response()->json($cliente);
    }
}
