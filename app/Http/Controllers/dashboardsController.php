<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Boletos;
use App\Models\Chargebacks;
use App\Models\Pedidos;
use App\Models\Pixs;
use App\Models\Prechargebacks;
use App\Models\Tickets;
use App\Models\Transacoes;
use App\Models\Vendas;
use App\Models\User;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Support\Facades\Auth;

class dashboardsController extends Controller
{

    // public function index()
    // {
    //     $boletos = Boletos::count();
    //     $chargebacks = Chargebacks::count();
    //     $pedidos = Pedidos::count();
    //     $pixs = Pixs::count();
    //     $tickets = Tickets::count();
    //     $vendas = Vendas::count();
    //     $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

    //     $total_vendas = Transacoes::sum('valor_produto');


    //     $vendas_pagas = Transacoes::where('status', 'Pago')->sum('valor_produto');

    //     $ticket_medio = $total_vendas / $vendas_pagas;

    //     // Somar os valores do PIX
    //     $vendas_pix = Transacoes::where('forma_pagamento', 'PIX')->sum('valor_produto');
    //     $porcentagem_PIX = ($vendas_pix / $total_vendas) * 100;

    //     // Converter para inteiro
    //     $porcentagem_PIX_inteiro = intval($porcentagem_PIX);

    //     // Somar os valores do Cartao
    //     $vendas_cartao = Transacoes::where('forma_pagamento', 'Cartão')->sum('valor_produto');
    //     $porcentagem_cartao = ($vendas_cartao / $total_vendas) * 100;

    //     // Converter para inteiro
    //     $porcentagem_cartao_inteiro = intval($porcentagem_cartao);

    //     // Somar os valores do CBoleto
    //     $vendas_boletos = Transacoes::where('forma_pagamento', 'Boleto')->sum('valor_produto');
    //     $porcentagem_boleto = ($vendas_boletos / $total_vendas) * 100;

    //     // Converter para inteiro
    //     $porcentagem_boleto_inteiro = intval($porcentagem_boleto);


    //     return view('dashboard/dashboard', compact(
    //         'boletos', 'chargebacks', 'pedidos', 'pixs', 'tickets', 'vendas', 'primeiro_caractere_usuario',
    //         'vendas_pix', 'total_vendas', 'porcentagem_PIX_inteiro', 'vendas_cartao',
    //         'porcentagem_cartao_inteiro',
    //         'vendas_boletos','porcentagem_boleto_inteiro',
    //         'vendas_pagas','ticket_medio'
    //     ));
    // }


    public function index(Request $request)
{
    // Obtendo o ID do usuário logado
    $user_id = Auth::user()->id;

    // Verificar se foram passadas datas como parâmetros
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Definir as datas padrão se não forem fornecidas
    if (empty($startDate) || empty($endDate)) {
        $startDate = date('Y-m-d', strtotime('-7 days')); // Data de 7 dias atrás
        $endDate = date('Y-m-d'); // Data atual
    }

    // Adicionar o final do dia à data final
    $endDate = date('Y-m-d 23:59:59', strtotime($endDate));

    // Obter todas as datas dentro do intervalo
    $period = new DatePeriod(
        new DateTime($startDate),
        new DateInterval('P1D'),
        new DateTime($endDate)
    );

    // Inicializar array com todos os dias do intervalo
    $dates = [];
    foreach ($period as $date) {
        $dates[$date->format('Y-m-d')] = 0;
    }

    // Contar o total de vendas por dia dentro do intervalo de datas
    $salesByDay = Transacoes::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
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
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar os chargebacks pelo user_id do usuário logado
    $chargebacks = Chargebacks::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar os prechargebacks pelo user_id do usuário logado
    $prechargebacks = Prechargebacks::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar os pedidos pelo user_id do usuário logado
    $pedidos = Pedidos::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar os PIX pelo user_id do usuário logado
    $pixs = Pixs::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar os tickets pelo user_id do usuário logado
    $tickets = Tickets::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Filtrar as vendas pelo user_id do usuário logado
    $vendas = Vendas::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

    // Filtrar as transações pelo user_id do usuário logado
    $total_vendas = Transacoes::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('valor_produto');

    // Filtrar as transações pagas pelo user_id do usuário logado
    $vendas_pagas = Transacoes::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->where('status', 'Pago')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->count();

    // Calcular o ticket médio
    $ticket_medio = $vendas_pagas != 0 ? $total_vendas / $vendas_pagas : 0;

    // Filtrar as transações PIX pelo user_id do usuário logado
    $vendas_pix = Transacoes::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->where('forma_pagamento', 'PIX')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('valor_produto');

    // Calcular a porcentagem de vendas PIX
    $porcentagem_PIX = $total_vendas != 0 ? ($vendas_pix / $total_vendas) * 100 : 0;
    $porcentagem_PIX_inteiro = number_format($porcentagem_PIX, 1, ',', '');

    // Filtrar as transações de cartão pelo user_id do usuário logado
    $vendas_cartao = Transacoes::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->where('forma_pagamento', 'Cartão')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->sum('valor_produto');

    // Calcular a porcentagem de vendas de cartão
    $porcentagem_cartao = $total_vendas != 0 ? ($vendas_cartao / $total_vendas) * 100 : 0;
    $porcentagem_cartao_inteiro = number_format($porcentagem_cartao, 1, ',', '');

    // Filtrar as transações de boleto pelo user_id do usuário logado
    $vendas_boletos = Transacoes::where('user_id', $user_id)
        ->where('created_at', '<=', now())
        ->where('forma_pagamento', 'Boleto')
        ->whereBetween('created_at', [$startDate, $endDate])
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

    return view('dashboard/dashboard', compact(
        'boletos', 'chargebacks', 'pedidos', 'pixs', 'tickets', 'vendas', 'primeiro_caractere_usuario',
        'vendas_pix', 'total_vendas', 'porcentagem_PIX_inteiro', 'vendas_cartao',
        'porcentagem_cartao_inteiro',
        'vendas_boletos', 'porcentagem_boleto_inteiro',
        'vendas_pagas', 'ticket_medio', 'salesByDay', 'transacoesPorParcela', 'totalValorChargebacks',
        'totalQuantidadeChargebacks', 'totalValorPreChargebacks', 'totalQuantidadePreChargebacks', 'preChargebacksPercentage', 'chargebacksPercentage', 'valoresMeses', 'labels', 'data'
    ))->with([
        'startDate' => $startDate,
        'endDate' => $endDate,
        'transacoesPorParcela' => json_encode($transacoesPorParcela)
    ]);
}





public function filter_date(Request $request)
{
    // Verificar se foram passadas datas como parâmetros
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    // Obtendo o ID do usuário logado
    $user_id = Auth::user()->id;

    // Definir as datas padrão se não forem fornecidas
    if (empty($startDate) || empty($endDate)) {
        $startDate = date('Y-m-d', strtotime('-7 dias')); // Data de 7 dias atrás
        $endDate = date('Y-m-d'); // Data atual
    }

    // Adicionar o final do dia à data final
    $endDate = date('Y-m-d 23:59:59', strtotime($endDate));

    // Verificar a diferença de dias entre as datas
    $start = new DateTime($startDate);
    $end = new DateTime($endDate);
    $diff = $start->diff($end)->days;

    if ($diff <= 31) {
        // Obter todas as datas dentro do intervalo
        $period = new DatePeriod(
            new DateTime($startDate),
            new DateInterval('P1D'),
            new DateTime($endDate)
        );

        // Inicializar array com todos os dias do intervalo
        $dates = [];
        foreach ($period as $date) {
            $dates[$date->format('Y-m-d')] = 0;
        }

        // Contar o total de vendas por dia dentro do intervalo de datas
        $salesByDay = Transacoes::where('user_id', $user_id)
            ->whereBetween('created_at', [$startDate, $endDate])
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
    } else {
        // Obter todas os meses dentro do intervalo
        $startMonth = new DateTime($startDate);
        $endMonth = new DateTime($endDate);
        $months = [];
        while ($startMonth <= $endMonth) {
            $months[$startMonth->format('Y-m')] = 0;
            $startMonth->modify('+1 month');
        }

        // Contar o total de vendas por mês dentro do intervalo de datas
        $salesByMonth = Transacoes::where('user_id', $user_id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('created_at', '<=', now())
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, SUM(valor_produto) as total_sales')
            ->groupBy('month')
            ->pluck('total_sales', 'month')
            ->toArray();

        // Combinar os dados de vendas com o array de meses
        foreach ($salesByMonth as $month => $total_sales) {
            $months[$month] = $total_sales;
        }

        // Convertendo array para valores que podem ser utilizados no gráfico
        $labels = array_keys($months);
        $data = array_values($months);
    }

    // Calcular somatórios dos chargebacks
    $totalValorChargebacks = Chargebacks::where('user_id', $user_id)
        ->whereBetween('data', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('quantidade');

    $totalQuantidadeChargebacks = Chargebacks::where('user_id', $user_id)
        ->whereBetween('data', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('quantidade');

    // Filtrar os boletos pelo user_id do usuário logado
    $boletos = Boletos::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar os chargebacks pelo user_id do usuário logado
    $chargebacks = Chargebacks::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar os prechargebacks pelo user_id do usuário logado
    $prechargebacks = Prechargebacks::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar os pedidos pelo user_id do usuário logado
    $pedidos = Pedidos::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar os pixs pelo user_id do usuário logado
    $pixs = Pixs::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar os tickets pelo user_id do usuário logado
    $tickets = Tickets::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    // Filtrar as vendas pelo user_id do usuário logado
    $vendas = Vendas::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

    // Calcular o total de vendas pelo user_id do usuário logado
    $total_vendas = Transacoes::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('valor_produto');

    // Contar o número de vendas pagas pelo user_id do usuário logado
    $vendas_pagas = Transacoes::where('user_id', $user_id)
        ->where('status', 'Pago')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count();

    if ($vendas_pagas != 0) {
        $ticket_medio = $total_vendas / $vendas_pagas;
    } else {
        $ticket_medio = 0; // Ou qualquer valor padrão que você deseje atribuir
    }

    // Somar os valores do PIX
    $vendas_pix = Transacoes::where('user_id', $user_id)
        ->where('forma_pagamento', 'PIX')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('valor_produto');

    if ($total_vendas != 0) {
        $porcentagem_PIX = ($vendas_pix / $total_vendas) * 100;
    } else {
        $porcentagem_PIX = 0; // Ou qualquer valor padrão que você deseje atribuir
    }

    $porcentagem_PIX_inteiro = number_format($porcentagem_PIX, 1, ',', '');

    // Somar os valores do Cartao
    $vendas_cartao = Transacoes::where('user_id', $user_id)
        ->where('forma_pagamento', 'Cartão')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('valor_produto');

    $quantidade_vendas_cartao_aprovados = Transacoes::where('user_id', $user_id)
        ->where('forma_pagamento', 'Cartão')
        ->where('status', 'pago')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->count('valor_produto');

    $totalValorPreChargebacks = PreChargebacks::where('user_id', $user_id)
        ->whereBetween('data', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('quantidade');

    $totalQuantidadePreChargebacks = PreChargebacks::where('user_id', $user_id)
        ->whereBetween('data', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('quantidade');

    $preChargebacksPercentage = 0;

    if ($quantidade_vendas_cartao_aprovados != 0) {
        $chargebacksPercentage = ($totalQuantidadeChargebacks / $quantidade_vendas_cartao_aprovados) * 100;
        $preChargebacksPercentage = ($totalQuantidadePreChargebacks * 100) / $quantidade_vendas_cartao_aprovados;
    } else {
        $chargebacksPercentage = 0; // Ou qualquer valor padrão que você deseje atribuir
    }

    $chargebacksPercentage = number_format($chargebacksPercentage, 1, '.', '');
    $preChargebacksPercentage = number_format($preChargebacksPercentage, 1, '.', '');

    if ($total_vendas != 0) {
        $porcentagem_cartao = ($vendas_cartao / $total_vendas) * 100;
    } else {
        $porcentagem_cartao = 0; // Ou qualquer valor padrão que você deseje atribuir
    }

    $porcentagem_cartao_inteiro = number_format($porcentagem_cartao, 1, ',', '');

    $vendas_boletos = Transacoes::where('user_id', $user_id)
        ->where('forma_pagamento', 'Boleto')
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->sum('valor_produto');

    if ($total_vendas != 0) {
        $porcentagem_boleto = ($vendas_boletos / $total_vendas) * 100;
    } else {
        $porcentagem_boleto = 0; // Ou qualquer valor padrão que você deseje atribuir
    }

    $porcentagem_boleto_inteiro = intval($porcentagem_boleto);

    // Inicializar o array $transacoesPorParcela com valores zerados para cada parcela
    $transacoesPorParcela = [];
    for ($i = 1; $i <= 12; $i++) {
        $transacoesPorParcela[$i] = 0;
    }

    // Preencher o array $transacoesPorParcela com o valor total das transações por parcela
    $salesByDay = Transacoes::where('user_id', $user_id)
        ->whereBetween('created_at', [$startDate, $endDate])
        ->where('created_at', '<=', now())
        ->selectRaw('parcelas, SUM(valor_produto) as total_sales')
        ->groupBy('parcelas')
        ->get();

    foreach ($salesByDay as $sale) {
        $parcela = intval($sale->parcelas);
        $totalSales = intval($sale->total_sales);
        if ($parcela >= 1 && $parcela <= 12) {
            $transacoesPorParcela[$parcela] = $totalSales;
        }
    }

    $transacoesPorParcela = collect($transacoesPorParcela);

    return view('dashboard/dashboard', compact(
        'boletos', 'chargebacks', 'prechargebacks', 'pedidos', 'pixs', 'tickets', 'vendas', 'primeiro_caractere_usuario',
        'vendas_pix', 'total_vendas', 'porcentagem_PIX_inteiro', 'vendas_cartao',
        'porcentagem_cartao_inteiro', 'vendas_boletos', 'porcentagem_boleto_inteiro',
        'vendas_pagas', 'ticket_medio', 'salesByDay', 'transacoesPorParcela', 'totalValorChargebacks', 'totalQuantidadeChargebacks',
        'totalValorPreChargebacks', 'totalQuantidadePreChargebacks', 'chargebacksPercentage', 'preChargebacksPercentage', 'labels', 'data'
    ))->with([
        'startDate' => $startDate,
        'endDate' => $endDate,
        'transacoesPorParcela' => json_encode($transacoesPorParcela)
    ]);
}


    public function create()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }


    public function destroy($id)
    {
        //
    }
}
