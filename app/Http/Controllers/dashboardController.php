<?php

namespace App\Http\Controllers;

use App\Models\Boletos;
use App\Models\Chargebacks;
use App\Models\Pedidos;
use App\Models\Pixs;
use App\Models\Prechargebacks;
use App\Models\Tickets;
use App\Models\Transacaos;
use App\Models\Vendas;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class dashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Obtendo o ID do usuário logado
        $user_id = Auth::user()->id;

        // Verificar se foram passadas datas como parâmetros
        $data_inicio = $request->input('data_inicio');
        $data_fim = $request->input('data_fim');

        // Definir as datas padrão se não forem fornecidas
        if (empty($data_inicio) || empty($data_fim)) {
            $data_inicio = date('Y-m-d', strtotime('-7 days')); // Data de 7 dias atrás
            $data_fim = date('Y-m-d'); // Data atual
        }

        // Adicionar o final do dia à data final
        $data_fim = date('Y-m-d 23:59:59', strtotime($data_fim));

        // Obter todas as datas dentro do intervalo
        $period = new DatePeriod(
            new DateTime($data_inicio),
            new DateInterval('P1D'),
            new DateTime($data_fim)
        );

        // Inicializar array com todos os dias do intervalo
        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
        }

        // Contar o total de vendas por dia dentro do intervalo de datas
        $salesByDay = Transacaos::where('user_id', $user_id)
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->where('created_at', '<=', now())
            ->selectRaw('DATE(created_at) as date, SUM(valor_produto) as total_sales')
            ->groupBy('date')
            ->pluck('total_sales', 'date')
            ->toArray();

        // Combinar os dados de vendas com o array de datas
        foreach ($salesByDay as $date => $total_sales) {
            $dates[$date] = $total_sales;
        }

        // Convertendo array para valores que podem ser utilizados no gráfico
        $labels = array_keys($dates);
        $data = array_values($dates);

        // Filtrar os boletos pelo user_id do usuário logado
        $boletos = Boletos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar os chargebacks pelo user_id do usuário logado
        $chargebacks = Chargebacks::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar os prechargebacks pelo user_id do usuário logado
        $prechargebacks = Prechargebacks::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar os pedidos pelo user_id do usuário logado
        $pedidos = Pedidos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar os PIX pelo user_id do usuário logado
        $pixs = Pixs::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar os tickets pelo user_id do usuário logado
        $tickets = Tickets::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Filtrar as vendas pelo user_id do usuário logado
        $vendas = Vendas::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

        // Filtrar as transações pelo user_id do usuário logado
        $total_vendas = Transacaos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->sum('valor_produto');

        // Filtrar as transações pagas pelo user_id do usuário logado
        $vendas_pagas = Transacaos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->where('status', 'Pago')
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->count();

        // Calcular o ticket médio
        $ticket_medio = $vendas_pagas != 0 ? $total_vendas / $vendas_pagas : 0;

        // Filtrar as transações PIX pelo user_id do usuário logado
        $vendas_pix = Transacaos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->where('forma_pagamento', 'PIX')
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->sum('valor_produto');

        // Calcular a porcentagem de vendas PIX
        $porcentagem_PIX = $total_vendas != 0 ? ($vendas_pix / $total_vendas) * 100 : 0;
        $porcentagem_PIX_inteiro = number_format($porcentagem_PIX, 1, ',', '');

        // Filtrar as transações de cartão pelo user_id do usuário logado
        $vendas_cartao = Transacaos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->where('forma_pagamento', 'Cartão')
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->sum('valor_produto');

        // Calcular a porcentagem de vendas de cartão
        $porcentagem_cartao = $total_vendas != 0 ? ($vendas_cartao / $total_vendas) * 100 : 0;
        $porcentagem_cartao_inteiro = number_format($porcentagem_cartao, 1, ',', '');

        // Filtrar as transações de boleto pelo user_id do usuário logado
        $vendas_boletos = Transacaos::where('user_id', $user_id)
            ->where('created_at', '<=', now())
            ->where('forma_pagamento', 'Boleto')
            ->whereBetween('created_at', [$data_inicio, $data_fim])
            ->sum('valor_produto');

        // Calcular a porcentagem de vendas de boleto
        $porcentagem_boleto = $total_vendas != 0 ? ($vendas_boletos / $total_vendas) * 100 : 0;
        $porcentagem_boleto_inteiro = number_format($porcentagem_boleto, 1, ',', '');

        // Inicializar $transacoesPorParcela com valores zerados para cada mês
        $transacoesPorParcela = [];
        for ($i = 1; $i <= 12; $i++) {
            $transacoesPorParcela[$i] = 0;
        }

        // Inicializar as variáveis dos chargebacks
        $totalValorChargebacks = 0;
        $totalQuantidadeChargebacks = 0;

        $totalValorPreChargebacks = 0;
        $totalQuantidadePreChargebacks = 0;
        $preChargebacksPercentage = 0;
        $chargebacksPercentage = 0;

        $valoresMeses = [];

        // Retornar os dados em formato JSON
        return response()->json([
            'boletos' => $boletos,
            'chargebacks' => $chargebacks,
            'pedidos' => $pedidos,
            'pixs' => $pixs,
            'tickets' => $tickets,
            'vendas' => $vendas,
            'primeiro_caractere_usuario' => $primeiro_caractere_usuario,
            'vendas_pix' => $vendas_pix,
            'total_vendas' => $total_vendas,
            'porcentagem_PIX_inteiro' => $porcentagem_PIX_inteiro,
            'vendas_cartao' => $vendas_cartao,
            'porcentagem_cartao_inteiro' => $porcentagem_cartao_inteiro,
            'vendas_boletos' => $vendas_boletos,
            'porcentagem_boleto_inteiro' => $porcentagem_boleto_inteiro,
            'vendas_pagas' => $vendas_pagas,
            'ticket_medio' => $ticket_medio,
            'salesByDay' => $salesByDay,
            'transacoesPorParcela' => $transacoesPorParcela,
            'totalValorChargebacks' => $totalValorChargebacks,
            'totalQuantidadeChargebacks' => $totalQuantidadeChargebacks,
            'totalValorPreChargebacks' => $totalValorPreChargebacks,
            'totalQuantidadePreChargebacks' => $totalQuantidadePreChargebacks,
            'preChargebacksPercentage' => $preChargebacksPercentage,
            'chargebacksPercentage' => $chargebacksPercentage,
            'valoresMeses' => $valoresMeses,
            'labels' => $labels,
            'data' => $data,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim,
            'transacoesPorParcela' => json_encode($transacoesPorParcela),
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
        //
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
