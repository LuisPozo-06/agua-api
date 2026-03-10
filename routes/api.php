<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ClienteController;


use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('pedidos', PedidoController::class);
    Route::apiResource('clientes', ClienteController::class);
    Route::post('/pedido-completo', [PedidoController::class, 'crearPedidoCompleto']);
    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/estado/{estado}', [PedidoController::class, 'pedidosPorEstado']);
    Route::put('/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::get('/dashboard', [PedidoController::class, 'dashboard']);
});