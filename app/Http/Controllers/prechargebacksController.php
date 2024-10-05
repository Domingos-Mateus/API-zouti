<?php

namespace App\Http\Controllers;

use App\Models\Prechargebacks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class prechargebacksController extends Controller
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
        $prechargeback = Prechargebacks::where('user_id', $user_id)->get();
        // Retorna os boletos em formato JSON
        return response()->json($prechargeback);
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
        // Validação do campo 'pix'
        $validated = $request->validate([
            'quantidade' => 'required|integer',
            'data' => 'required|date',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Criação do novo pix e associação com o usuário logado
        $prechargeback = new Prechargebacks();
        $prechargeback->quantidade = $validated['quantidade'];
        $prechargeback->data = $validated['data'];

        // Obtendo o ID do usuário logado e associando ao pix
        $prechargeback->user_id = $user->id;

        // Salva o pix no banco de dados
        $prechargeback->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'Prechargebacks criado com sucesso!',
            'data' => $prechargeback
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
    public function update(Request $request, $id)
    {
        // Validação dos dados
        $validated = $request->validate([
            'quantidade' => 'required|integer',
            'data' => 'required|date',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Busca o registro pelo ID e verifica se pertence ao usuário autenticado
        $prechargeback = Prechargebacks::where('id', $id)->where('user_id', $user->id)->first();

        // Se o registro não for encontrado ou não pertencer ao usuário, retorna erro
        if (!$prechargeback) {
            return response()->json(['error' => 'Registro não encontrado ou sem permissão'], 404);
        }

        // Atualiza os dados do pix
        $prechargeback->quantidade = $validated['quantidade'];
        $prechargeback->data = $validated['data'];

        // Salva as alterações no banco de dados
        $prechargeback->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'prechargebacks atualizado com sucesso!',
            'data' => $prechargeback
        ], 200); // Código HTTP 200 para sucesso
    }


    /**
     * Remove the specified resource from storage.
     */
    // Excluir um prechargeback
    public function destroy($id)
    {
        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Busca o registro pelo ID e verifica se pertence ao usuário autenticado
        $prechargeback = Prechargebacks::where('id', $id)->where('user_id', $user->id)->first();

        // Se o registro não for encontrado ou não pertencer ao usuário, retorna erro
        if (!$prechargeback) {
            return response()->json(['error' => 'Registro não encontrado ou sem permissão'], 404);
        }

        // Deleta o registro
        $prechargeback->delete();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'prechargeback deletado com sucesso!'
        ], 200); // Código HTTP 200 para sucesso
    }
}
