<?php

namespace App\Http\Controllers;

use App\Models\Chargebacks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class chargebackController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Listar todos os chargebacks do usuário logado
    public function index(Request $request)
{
    // Verifica se o usuário está logado e obtém o ID do usuário logado
    $user = Auth::user();
    if (!$user) {
        return response()->json(['error' => 'Usuário não autenticado'], 401);
    }

    $user_id = $user->id;

    // Pega os parâmetros de data do request (data_inicio e data_fim)
    $startDate = $request->query('data_inicio'); // Data de início
    $endDate = $request->query('data_fim'); // Data de fim

    // Inicia a query para buscar chargebacks do usuário logado
    $query = Chargebacks::where('user_id', $user_id);

    // Aplica o filtro de intervalo de datas se ambos os parâmetros de data forem fornecidos
    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Ordena os resultados pela data de criação de forma decrescente
    $chargebacks = $query->orderBy('created_at', 'desc')->get();

    // Retorna os chargebacks filtrados em formato JSON
    return response()->json($chargebacks);
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
    // Criar um novo chargeback
    public function store(Request $request)
    {
        // Validação dos campos
        $validated = $request->validate([
            'quantidade' => 'required|integer',
            'data' => 'required|date',
        ]);

        // Criação do novo chargeback
        $chargeback = new Chargebacks;
        $chargeback->quantidade = $validated['quantidade'];
        $chargeback->data = $validated['data'];

        // Associar o chargeback ao usuário logado
        $chargeback->user_id = Auth::user()->id;

        // Salvar o chargeback no banco de dados
        $chargeback->save();

        return response()->json([
            'success' => true,
            'message' => 'Chargeback criado com sucesso!',
            'data' => $chargeback
        ], 201);
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
    // Atualizar um chargeback existente
    public function update(Request $request, $id)
    {
        // Validação dos campos
        $validated = $request->validate([
            'quantidade' => 'sometimes|required|integer',
            'data' => 'sometimes|required|date',
        ]);

        // Buscar o chargeback pelo ID
        $chargeback = Chargebacks::findOrFail($id);

        // Verifica se o chargeback pertence ao usuário logado
        if ($chargeback->user_id != Auth::user()->id) {
            return response()->json(['error' => 'Ação não autorizada'], 403);
        }

        // Atualizar os campos
        $chargeback->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Chargeback atualizado com sucesso!',
            'data' => $chargeback
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    // Deletar um chargeback
    public function destroy($id)
    {
        // Buscar o chargeback pelo ID
        $chargeback = Chargebacks::findOrFail($id);

        // Verifica se o chargeback pertence ao usuário logado
        if ($chargeback->user_id != Auth::user()->id) {
            return response()->json(['error' => 'Ação não autorizada'], 403);
        }

        // Deletar o chargeback
        $chargeback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chargeback deletado com sucesso!'
        ], 200);
    }
}
