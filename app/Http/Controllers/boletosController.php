<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Boletos;
use Illuminate\Support\Facades\Auth;

class boletosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
        $boletos = Boletos::where('user_id', $user_id)->get();
        // Retorna os boletos em formato JSON
        return response()->json($boletos);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validação do campo 'boleto'
        $validated = $request->validate([
            'boleto' => 'required|string',
        ]);

        // Verifica se o usuário está autenticado
        $user = Auth::user();

        // Se o usuário não estiver autenticado, retorna erro
        if (!$user) {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }

        // Criação do novo boleto e associação com o usuário logado
        $boletos = new Boletos;
        $boletos->boleto = $validated['boleto'];

        // Obtendo o ID do usuário logado e associando ao boleto
        $boletos->user_id = $user->id;

        // Salva o boleto no banco de dados
        $boletos->save();

        // Retorna uma resposta em JSON
        return response()->json([
            'success' => true,
            'message' => 'Boleto criado com sucesso!',
            'data' => $boletos
        ], 201); // Código HTTP 201 para criação bem-sucedida
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
