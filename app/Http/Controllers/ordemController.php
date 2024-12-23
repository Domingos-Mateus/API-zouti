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
    // Obtendo o ID do usuário logado
    $user_id = Auth::user()->id;

    // Pega os parâmetros de data do request (data_inicio e data_fim)
    $startDate = $request->query('data_inicio'); // Data de início
    $endDate = $request->query('data_fim'); // Data de fim

    // Inicia a query para buscar apenas as ordens filtradas pelo usuário logado
    $query = DB::table('ordens')
        ->select('ordens.*') // Seleciona apenas os dados da tabela ordens
        ->where('ordens.user_id', '=', $user_id);

    // Aplica o filtro de intervalo de datas se ambos os parâmetros forem fornecidos
    if ($startDate && $endDate) {
        $query->whereBetween('ordens.created_at', [$startDate, $endDate]);
    }

    // Ordena por data de forma descendente e aplica paginação
    $ordens = $query->orderBy('ordens.created_at', 'desc')->paginate(30); // Aplicando a paginação de 20 registros por página

    // Adiciona o campo parcelas da tabela transacaos para cada ordem
    $ordens->getCollection()->transform(function ($ordem) {
        // Busca o valor de parcelas na tabela transacaos correspondente à ordem atual
        $transacao = DB::table('transacaos')
            ->where('ordem_id', $ordem->id)
            //->select('parcelas') // Obtém apenas o campo parcelas
            ->first();



        return $ordem;
    });

    // Retorna os dados como JSON com as informações das ordens e o número de parcelas
    return response()->json([
        'ordens' => $ordens,
        'mensagem' => 'Listagem de ordens com parcelas das transações incluídas!',
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
            'percentagem_pedidos_pagos_cartao' => 'required|numeric',
            'genero_cliente' => 'required|integer',
        ]);

        // Obter o ID do usuário logado
        $user_id = Auth::user()->id;

        // Conversão do valor do produto para float
        $valorProduto = floatval($request->valor_produto);

        // Criação de uma nova ordem
        $ordens = Ordens::create([
            'nome_produto' => $request->nome_produto,
            'valor_produto' => $valorProduto,
            'total_pedidos' => $request->quantidade_pedidos_pix + $request->quantidade_pedidos_cartao,
            'quantidade_pedidos_pix' => $request->quantidade_pedidos_pix,
            'percentagem_conversao_pix' => $request->percentagem_conversao_pix,
            'quantidade_pedidos_cartao' => $request->quantidade_pedidos_cartao,
            'genero_cliente' => $request->genero_cliente,
            'user_id' => $user_id,
        ]);

        // Quantidade de pedidos pagos com PIX
        $quantidadePedidosPixPagos = round($request->quantidade_pedidos_pix * ($request->percentagem_conversao_pix / 100));

        // Criação automática de transações para pedidos PIX
        for ($i = 0; $i < $request->quantidade_pedidos_pix; $i++) {
            $transacao = new Transacaos();
            $transacao->cliente_id = $this->selecionarClientePorGenero($request->genero_cliente);
            $transacao->ordem_id = $ordens->id;
            $transacao->nome_produto = $request->nome_produto;
            $transacao->valor_produto = $valorProduto;
            $transacao->forma_pagamento = 'PIX';
            $transacao->status = $i < $quantidadePedidosPixPagos ? 'Pago' : 'Pendente';
            $transacao->data_pagamento = now();
            $transacao->data_vencimento = now()->addDays(30);
            $transacao->user_id = $user_id;

            // Gera timestamps aleatórios
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
            $transacao->forma_pagamento = 'Cartão';
            $transacao->valor_produto = $valorProduto;
            $transacao->data_pagamento = now();
            $transacao->data_vencimento = now()->addDays(30);

            // Gera timestamps aleatórios
            $segundosAleatorios = rand(0, 86400);
            $timestampAleatorio = now()->addSeconds($segundosAleatorios);
            $transacao->created_at = $timestampAleatorio;
            $transacao->updated_at = $timestampAleatorio;
            $transacao->timestamps = false;

            // Verificação do status de pagamento do pedido Cartão
            $percentagem_pedidos_pagos_cartao = $request->percentagem_pedidos_pagos_cartao;
            if ($i < ($request->quantidade_pedidos_cartao * ($percentagem_pedidos_pagos_cartao / 100))) {
                $transacao->status = 'Pago';
            } else {
                $transacao->status = 'Recusado';
            }

            // Define aleatoriamente o número de parcelas
            $numeroParcelas = [1, 2, 3, 6, 10, 12];
            $parcelas = $numeroParcelas[array_rand($numeroParcelas)];

            // Calcula o valor do produto com base nas parcelas
            if ($parcelas == 2) {
                $valorParcela = ($valorProduto * 1.29) / $parcelas;
            } elseif ($parcelas == 3) {
                $valorParcela = ($valorProduto * 1.32) / $parcelas;
            } elseif ($parcelas == 6) {
                $valorParcela = ($valorProduto * 1.26) / $parcelas;
            } elseif ($parcelas == 10) {
                $valorParcela = ($valorProduto * 1.30) / $parcelas;
            } elseif ($parcelas == 12) {
                $valorParcela = ($valorProduto * 1.45) / $parcelas;
            } else {
                $valorParcela = $valorProduto / $parcelas;
            }

            $transacao->valor_produto = $valorParcela * $parcelas;
            $transacao->parcelas = $parcelas;
            $transacao->valor_parcela = $valorParcela;

            // Define aleatoriamente o tipo de cartão
            $cartaoAleatorio = mt_rand(1, 100);
            $transacao->tipo_cartao = $cartaoAleatorio <= 40 ? 'Mastercard' : ($cartaoAleatorio <= 80 ? 'Visa' : 'Ello');

            $transacao->user_id = $user_id;
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
        // Verifica se a ordem existe
    $ordem = Ordens::find($id);

    if (!$ordem) {
        return response()->json(['mensagem' => 'Ordem não encontrada'], 404);
    }

    // Verifica se o usuário logado é o dono da ordem
    if ($ordem->user_id !== Auth::id()) {
        return response()->json(['mensagem' => 'Acesso não autorizado'], 403);
    }

    // Deleta as transações associadas à ordem
    Transacaos::where('ordem_id', $ordem->id)->delete();

    // Deleta a ordem
    $ordem->delete();

    return response()->json(['mensagem' => 'Ordem e transações associadas deletadas com sucesso!'], 200);
    }
}
