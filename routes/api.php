<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CloudinaryController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rutas específicas de Pedidos (Deben ir antes del apiResource)
    Route::get('/pedidos/pendientes', [PedidoController::class, 'pendientes']);
    Route::get('/pedidos/priorizados', [PedidoController::class, 'priorizados']);
    Route::get('/pedidos/prioridad/{nivel}', [PedidoController::class, 'pedidosPorPrioridad']);
    Route::get('/pedidos/hoy', [PedidoController::class, 'hoy']);
    Route::get('/pedidos/pago-pendiente', [PedidoController::class, 'pagoPendiente']);

    // Rutas custom de Clientes específicas
    Route::get('/clientes/telefono/{telefono}', [ClienteController::class, 'buscarPorTelefono']);

    // Rutas resource
    Route::apiResource('pedidos', PedidoController::class);
    Route::apiResource('clientes', ClienteController::class);
    
    // Rutas custom adicionales de Clientes y Pedidos
    Route::get('/clientes/{id}/pedidos', [ClienteController::class, 'pedidos']);
    Route::post('/pedido-completo', [PedidoController::class, 'crearPedidoCompleto']);
    Route::get('/pedidos/estado/{estado}', [PedidoController::class, 'pedidosPorEstado']);
    Route::put('/pedidos/{id}/estado', [PedidoController::class, 'actualizarEstado']);
    Route::put('/pedidos/{id}/prioridad', [PedidoController::class, 'actualizarPrioridad']);
    Route::put('/pedidos/{id}/nota', [PedidoController::class, 'actualizarNota']);
    Route::put('/pedidos/{id}/direccion', [PedidoController::class, 'actualizarDireccion']);
    Route::put('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar']);
    Route::put('/pedidos/{id}/estado-pago', [PedidoController::class, 'actualizarEstadoPago']);
    Route::post('/pedidos/{id}/comprobante', [PedidoController::class, 'guardarComprobante']);

    // Cloudinary firma
    Route::post('/cloudinary/firma', [CloudinaryController::class, 'generarFirma']);
    
    // Dashboard separado
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
});