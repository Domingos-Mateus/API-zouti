<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produtos;
use Illuminate\Support\Facades\Auth;

class produtoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    // Verifica se o usuário está logado
    if (!Auth::check()) {
        // Retorna uma resposta de erro caso o usuário não esteja logado
        return response()->json([
            'error' => 'Usuário não autenticado'
        ], 401); // Código HTTP 401 Unauthorized
    }

    // Obtendo o ID do usuário logado
    $user_id = Auth::user()->id;

    // Pega os parâmetros de data do request (data_inicio e end_date)
    $startDate = $request->query('data_inicio'); // Data de início
    $endDate = $request->query('data_fim'); // Data de fim

    // Inicia a query para buscar os produtos do usuário logado
    $query = Produtos::where('user_id', $user_id);

    // Aplica o filtro de intervalo de datas se ambos os parâmetros forem fornecidos
    if ($startDate && $endDate) {
        $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // Executa a query e busca os produtos
    $produtos = $query->get();

    // Retornando os dados como JSON
    return response()->json([
        'produtos' => $produtos
    ]);
}


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('produtos/registar_produto');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    // Verificação se o usuário está logado
    if (!Auth::check()) {
        return response()->json([
            'error' => 'Usuário não autenticado'
        ], 401); // Retorna 401 se o usuário não estiver autenticado
    }

    // Validação dos dados
    $request->validate([
        'nome_produto' => 'required|string|max:100',
        'preco' => 'required|string|max:100',
    ]);

    // Criação de um novo produto
    $produtos = new Produtos;
    $produtos->nome_produto = $request->nome_produto;
    $produtos->preco = $request->preco;
    $produtos->user_id = Auth::id(); // Obtém o ID do usuário logado

    // Salvando o produto
    $produtos->save();

    // Retorna uma resposta JSON indicando sucesso
    return response()->json([
        'success' => true,
        'message' => 'Produto salvo com sucesso!',
        'produto' => $produtos
    ], 201); // Código 201 para recurso criado
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
