<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ClienteController;


use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas específicas de Pedidos (Deben ir antes del apiResource)
    Route::get('/pedidos/priorizados', [PedidoController::class, 'priorizados']);
    Route::get('/pedidos/hoy', [PedidoController::class, 'hoy']);

    // Rutas resource y adicionales
    Route::apiResource('pedidos', PedidoController::class);
    Route::apiResource('clientes', ClienteController::class);
    
    // Rutas custom de Clientes y Pedidos
    Route::get('/clientes/{id}/pedidos', [ClienteController::class, 'pedidos']);
    Route::post('/pedido-completo', [PedidoController::class, 'crearPedidoCompleto']);
    Route::get('/pedidos/estado/{estado}', [PedidoController::class, 'pedidosPorEstado']);
    Route::put('/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::put('/pedidos/{id}/prioridad', [PedidoController::class, 'actualizarPrioridad']);
    Route::put('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']);
    
    Route::get('/dashboard', [PedidoController::class, 'dashboard']);
});