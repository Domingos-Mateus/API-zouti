<?php

namespace App\Http\Controllers;

use App\Models\Vendas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class vendaController extends Controller
{
    /**
     * Display a listing of the resource.
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
        // Busca os boletos associados ao usuário
        $vendas = Vendas::where('user_id', $user_id)->get();
        // Retorna os boletos em formato JSON
        return response()->json($vendas);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação do campo 'venda'
        $validated = $request->validate([
            'venda' => 'required|string',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Criação do novo venda e associação com o usuário logado
        $vendas = new Vendas();
        $vendas->venda = $validated['venda'];

        // Obtendo o ID do usuário logado e associando ao venda
        $vendas->user_id = $user->id;

        // Salva o venda no banco de dados
        $vendas->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'venda criado com sucesso!',
            'data' => $vendas
        ], 201); // Código HTTP 201 para criação bem-sucedida
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
