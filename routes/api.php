<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\boletosController;
use App\Http\Controllers\chargebackController;
use App\Http\Controllers\clienteController;
use App\Http\Controllers\dashboardController;
use App\Http\Controllers\ordemController;
use App\Http\Controllers\pedidosController;
use App\Http\Controllers\pixController;
use App\Http\Controllers\prechargebacksController;
use App\Http\Controllers\produtoController;
use App\Http\Controllers\ticketController;
use App\Http\Controllers\transacaoController;
use App\Http\Controllers\vendaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    // Sessão de usuários
    Route::post('logout', [AuthController::class, 'logout']);

    // Rotas para boletos
    Route::get('/boletos', [boletosController::class, 'index']);
    Route::post('/boletos', [boletosController::class, 'store']);
    Route::delete('/eliminar_boleto/{id}', [boletosController::class, 'destroy']);

    // Rotas para pedidos
    Route::get('/pedidos', [pedidosController::class, 'index']);
    Route::post('/pedidos', [pedidosController::class, 'store']);
    Route::delete('/eliminar_pedido/{id}', [pedidosController::class, 'destroy']);

    // Rotas para pixs
    Route::get('/pixs', [pixController::class, 'index']);
    Route::post('/pixs', [pixController::class, 'store']);
    Route::delete('/eliminar_pix/{id}', [pixController::class, 'destroy']);

    // Rotas para tickets
    Route::get('/listar_tickets', [ticketController::class, 'index']);
    Route::post('/registrar_tickets', [ticketController::class, 'store']);
    Route::delete('/eliminar_ticket/{id}', [ticketController::class, 'destroy']);

    // Rotas para vendas
    Route::get('/listar_vendas', [vendaController::class, 'index']);
    Route::post('/registrar_vendas', [vendaController::class, 'store']);
    Route::delete('/eliminar_venda/{id}', [vendaController::class, 'destroy']);

    // Rotas para ordens
    Route::get('/listar_ordens', [ordemController::class, 'index']);
    Route::post('/registrar_ordens', [ordemController::class, 'store']);
    Route::delete('/eliminar_ordens/{id}', [ordemController::class, 'destroy']);

    // Rotas para transações
    Route::get('/listar_transacaos', [transacaoController::class, 'index']);
    Route::post('/registrar_transacaos', [transacaoController::class, 'store']);

    // Rotas para produtos
    Route::get('/listar_produtos', [produtoController::class, 'index']);
    Route::post('/registrar_produtos', [produtoController::class, 'store']);
    Route::delete('/eliminar_produto/{id}', [produtoController::class, 'destroy']);

    // Rotas para chargebacks
    Route::get('/chargebacks', [chargebackController::class, 'index']);
    Route::post('/chargebacks', [chargebackController::class, 'store']);
    Route::put('/chargebacks/{id}', [chargebackController::class, 'update']);
    Route::delete('/chargebacks/{id}', [chargebackController::class, 'destroy']);

    // Rotas para prechargebacks
    Route::get('/prechargebacks', [prechargebacksController::class, 'index']);
    Route::post('/prechargebacks', [PreChargebacksController::class, 'store']);
    Route::get('/prechargebacks/{id}/edit', [PreChargebacksController::class, 'edit']);
    Route::put('/prechargebacks/{id}', [PreChargebacksController::class, 'update']);
    Route::delete('/prechargebacks/{id}', [PreChargebacksController::class, 'destroy']);

    //Dashboard
    Route::get('/dashboard', [dashboardController::class, 'index']);
});

// Rotas para clientes
Route::get('/listar_clientes', [clienteController::class, 'index']);
Route::post('/registrar_clientes', [clienteController::class, 'store']);
Route::delete('/eliminar_cliente/{id}', [clienteController::class, 'destroy']);
