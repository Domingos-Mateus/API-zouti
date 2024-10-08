<?php

namespace App\Http\Controllers;

use App\Models\Tickets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ticketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Verifica se o usuário está autenticado
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Retorna o ID do usuário autenticado
        $user_id = $user->id;

        // Pega os parâmetros de data do request (start_date e end_date)
        $startDate = $request->query('data_inicio'); // Data de início
        $endDate = $request->query('data_fim'); // Data de fim

        // Inicia a query para buscar os tickets do usuário logado
        $query = Tickets::where('user_id', $user_id);

        // Aplica o filtro de intervalo de datas se ambos os parâmetros de data forem fornecidos
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Executa a query e busca os tickets
        $tickets = $query->get();

        // Retorna os tickets filtrados em formato JSON
        return response()->json($tickets);
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
        // Validação do campo 'ticket'
        $validated = $request->validate([
            'ticket' => 'required|string',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Criação do novo ticket e associação com o usuário logado
        $tickets = new Tickets();
        $tickets->ticket = $validated['ticket'];

        // Obtendo o ID do usuário logado e associando ao ticket
        $tickets->user_id = $user->id;

        // Salva o ticket no banco de dados
        $tickets->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'ticket criado com sucesso!',
            'data' => $tickets
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
