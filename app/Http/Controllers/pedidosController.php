<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pedidos;
use Illuminate\Support\Facades\Auth;

class pedidosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Verifica se o usuário está autenticado
        $user = Auth::user();
        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Retorna o ID do usuário autenticado
        $user_id = $user->id;
        // Busca os pedidos associados ao usuário
        $pedido = Pedidos::where('user_id', $user_id)->get();
        // Retorna os pedidos em formato JSON
        return response()->json($pedido);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('pedidos/registar_pedido');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validação do campo 'pedido'
        $validated = $request->validate([
            'pedido' => 'required|string',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Criação do novo pedido e associação com o usuário logado
        $pedidos = new Pedidos();
        $pedidos->pedido = $validated['pedido'];

        // Obtendo o ID do usuário logado e associando ao pedido
        $pedidos->user_id = $user->id;

        // Salva o pedido no banco de dados
        $pedidos->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'pedido criado com sucesso!',
            'data' => $pedidos
        ], 201); // Código HTTP 201 para criação bem-sucedida
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
