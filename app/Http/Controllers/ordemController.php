<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use App\Models\Ordens;
use App\Models\Transacaos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ordemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtém todos os clientes
        $clientes = Clientes::all();

        // Conta o número total de transações
        $contar = Transacaos::count();

        // Obtendo o ID do usuário logado
        $user_id = Auth::user()->id;

        // Pega os parâmetros de data do request (data_inicio e end_date)
        $startDate = $request->query('data_inicio'); // Data de início
        $endDate = $request->query('data_fim'); // Data de fim

        // Inicia a query para buscar as transações com a junção das informações dos clientes e filtra pelo usuário logado
        $query = DB::table('transacaos')
            ->join('clientes', 'transacaos.cliente_id', '=', 'clientes.id')
            ->select('transacaos.*', 'clientes.nome as nome_cliente', 'clientes.email', 'clientes.abreviacao')
            ->where('transacaos.user_id', '=', $user_id);

        // Aplica o filtro de intervalo de datas se ambos os parâmetros forem fornecidos
        if ($startDate && $endDate) {
            $query->whereBetween('transacaos.created_at', [$startDate, $endDate]);
        }

        // Ordena por data de forma descendente e aplica a paginação
        $transacaos = $query->orderBy('transacaos.created_at', 'desc')->paginate(20);

        // Obtém o primeiro caractere do nome do usuário logado
        $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

        // Retorna os dados como JSON
        return response()->json([
            'clientes' => $clientes,
            'transacaos' => $transacaos,
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
    // Validação dos dados da requisição
    $request->validate([
        'nome_produto' => 'required|string|max:255',
        'valor_produto' => 'required|numeric',
        'quantidade_pedidos_pix' => 'required|integer',
        'percentagem_conversao_pix' => 'required|numeric',
        'quantidade_pedidos_cartao' => 'required|integer',
        'genero_cliente' => 'required|integer',
    ]);

    // Obter o ID do usuário logado
    $user_id = Auth::user()->id;

    // Criação de uma nova ordem, associando o usuário logado
    $ordens = Ordens::create([
        'nome_produto' => $request->nome_produto,
        'valor_produto' => $request->valor_produto,
        'total_pedidos' => $request->quantidade_pedidos_pix + $request->quantidade_pedidos_cartao,
        'quantidade_pedidos_pix' => $request->quantidade_pedidos_pix,
        'percentagem_conversao_pix' => $request->percentagem_conversao_pix,
        'quantidade_pedidos_cartao' => $request->quantidade_pedidos_cartao,
        'genero_cliente' => $request->genero_cliente,
        'user_id' => $user_id, // Associando a ordem ao usuário logado
    ]);

    // Quantidade de pedidos pagos com PIX
    $quantidadePedidosPixPagos = round($request->quantidade_pedidos_pix * ($request->percentagem_conversao_pix / 100));

    // Criação automática de transações para pedidos PIX
    for ($i = 0; $i < $request->quantidade_pedidos_pix; $i++) {
        $transacao = new Transacaos();
        $transacao->cliente_id = $this->selecionarClientePorGenero($request->genero_cliente);
        $transacao->ordem_id = $ordens->id;
        $transacao->nome_produto = $request->nome_produto;
        $transacao->valor_produto = $request->valor_produto;
        $transacao->forma_pagamento = 'PIX';
        $transacao->status = $i < $quantidadePedidosPixPagos ? 'Pago' : 'Pendente';
        $transacao->data_pagamento = now();
        $transacao->data_vencimento = now()->addDays(30); // Definindo data de vencimento
        $transacao->user_id = $user_id;

        // Gera um número aleatório de segundos e define timestamps
        $segundosAleatorios = rand(0, 86400);
        $timestampAleatorio = now()->addSeconds($segundosAleatorios);
        $transacao->created_at = $timestampAleatorio;
        $transacao->updated_at = $timestampAleatorio;
        $transacao->timestamps = false;

        $transacao->save();
    }

    // Criação automática de transações para pedidos Cartão
    for ($i = 0; $i < $request->quantidade_pedidos_cartao; $i++) {
        $transacao = new Transacaos();
        $transacao->cliente_id = $this->selecionarClientePorGenero($request->genero_cliente);
        $transacao->ordem_id = $ordens->id;
        $transacao->nome_produto = $request->nome_produto;
        $transacao->valor_produto = $request->valor_produto;
        $transacao->forma_pagamento = 'Cartão';
        $transacao->status = $i < ($request->quantidade_pedidos_cartao * 0.7) ? 'Pago' : 'Pendente';
        $transacao->data_pagamento = now();
        $transacao->data_vencimento = now()->addDays(30); // Definindo data de vencimento
        $transacao->user_id = $user_id;

        // Gera um número aleatório de segundos e define timestamps
        $segundosAleatorios = rand(0, 86400);
        $timestampAleatorio = now()->addSeconds($segundosAleatorios);
        $transacao->created_at = $timestampAleatorio;
        $transacao->updated_at = $timestampAleatorio;
        $transacao->timestamps = false;

        $transacao->save();
    }

    // Retorna resposta JSON com a ordem e suas transações
    return response()->json(['ordem' => $ordens, 'mensagem' => 'Ordem e transações criadas com sucesso!'], 201);
}

// Função para selecionar clientes com base no gênero
private function selecionarClientePorGenero($genero_cliente)
{
    if ($genero_cliente == 1) {
        return Clientes::where('sexo', 1)->inRandomOrder()->first()->id;
    } elseif ($genero_cliente == 0) {
        return Clientes::where('sexo', 0)->inRandomOrder()->first()->id;
    } else {
        return Clientes::inRandomOrder()->first()->id;
    }
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
