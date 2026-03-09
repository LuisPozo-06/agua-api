<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Support\Facades\DB;
use App\Models\Pedido;
use Illuminate\Http\Request;
use App\Http\Requests\StorePedidoRequest;
class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pedidos = Pedido::with('cliente')->get();

        return response()->json($pedidos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePedidoRequest $request)
    {
        $pedido = Pedido::create([
            'cliente_id' => $request->cliente_id,
            'cantidad_agua' => $request->cantidad_agua,
            'direccion_entrega' => $request->direccion_entrega,
            'prioridad' => $request->prioridad,
            'estado' => 'Pendiente',
            'fecha_pedido' => now()
        ]);

        return response()->json($pedido, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pedido = Pedido::with('cliente')->findOrFail($id);

        return response()->json($pedido);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->update([
            'estado' => $request->estado
        ]);

        return response()->json($pedido);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->delete();

        return response()->json([
            "mensaje" => "Pedido eliminado correctamente"
        ]);
    }
    public function crearPedidoCompleto(Request $request)
    {
        return DB::transaction(function () use ($request) {

            // Crear cliente
            $cliente = Cliente::firstOrCreate(
                ['telefono' => $request->telefono],
                [
                    'nombre' => $request->nombre,
                    'direccion' => $request->direccion
                ]
            );

            // Crear pedido
            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'cantidad_agua' => $request->cantidad_agua,
                'direccion_entrega' => $request->direccion_entrega,
                'prioridad' => $request->prioridad,
                'estado' => 'Pendiente',
                'fecha_pedido' => now()
            ]);

            return response()->json([
                "cliente" => $cliente,
                "pedido" => $pedido
            ], 201);
        });
    }
    public function pedidosPorEstado($estado)
    {
        $pedidos = Pedido::with('cliente')
            ->where('estado', $estado)
            ->get();

        return response()->json($pedidos);
    }
    public function actualizarEstado(Request $request, $id)
    {
        $pedido = Pedido::findOrFail($id);

        $pedido->estado = $request->estado;
        $pedido->save();

        return response()->json([
            "mensaje" => "Estado actualizado",
            "pedido" => $pedido
        ]);
    }
    public function dashboard()
    {
        $pendientes = Pedido::where('estado', 'Pendiente')->count();
        $proceso = Pedido::where('estado', 'En proceso')->count();
        $entregados = Pedido::where('estado', 'Entregado')->count();
        $total = Pedido::count();

        return response()->json([
            "pedidos_pendientes" => $pendientes,
            "pedidos_proceso" => $proceso,
            "pedidos_entregados" => $entregados,
            "total_pedidos" => $total
        ]);
    }
}
