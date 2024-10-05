<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Transacaos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class transacaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Obtém todos os clientes
    $clientes = Clientes::all();

    // Conta o número total de transações
    $contar = Transacaos::count();  // Corrigido para usar a model 'Transacaos'

    // Obtendo o ID do usuário logado
    $user_id = Auth::user()->id;

    // Inicia a consulta de transações com a junção das informações dos clientes
    $query = DB::table('transacaos')  // Corrigido para usar a tabela 'transacaos'
        ->join('clientes', 'transacaos.cliente_id', '=', 'clientes.id')
        ->select('transacaos.*', 'clientes.nome as nome_cliente', 'clientes.email', 'clientes.abreviacao')
        ->where('transacaos.user_id', '=', $user_id)
        ->where('transacaos.created_at', '<=', now());

    // Aplica filtro pelo nome do cliente, se fornecido
    if ($request->has('cliente') && !empty($request->cliente)) {
        $query->where('clientes.nome', 'like', '%' . $request->cliente . '%');
    }

    // Aplica filtro pelo nome do produto, se fornecido
    if ($request->has('produto') && !empty($request->produto)) {
        $query->where('transacaos.nome_produto', 'like', '%' . $request->produto . '%');
    }

    // Ordena por data de forma descendente e aplica paginação com 20 itens por página
    $transacoes = $query->orderBy('transacaos.created_at', 'desc')->paginate(20);

    // Obtém o primeiro caractere do nome do usuário logado
    $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

    // Retorna os dados como JSON
    return response()->json([
        'clientes' => $clientes,
        'transacoes' => $transacoes,
        'contar' => $contar,
        'primeiro_caractere_usuario' => $primeiro_caractere_usuario
    ]);
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
        $request->validate([
            'cliente_id' => 'required|string|max:255',
            'nome_produto' => 'required|string|max:255',
            'valor_produto' => 'required|string|max:255',
            'ordem_id' => 'required|string|max:255',
            'forma_pagamento' => 'required|string|max:255',
            'status' => 'required|string|max:255',
            'data_pagamento' => 'required|string|max:255',
        ]);
        // Criação do cliente no banco de dados
        $transacao = Transacaos::create([
            'cliente_id' => $request->input('cliente_id'),
            'nome_produto' => $request->input('nome_produto'),
            'valor_produto' => $request->input('valor_produto'),
            'ordem_id' => $request->input('ordem_id'),
            'forma_pagamento' => $request->input('forma_pagamento'),
            'status' => $request->input('status'),
            'data_pagamento' => $request->input('data_pagamento'),
        ]);
        // Retorna o cliente criado em formato JSON
        return response()->json($transacao, 201); // 201 = Created
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
