<?php

namespace App\Http\Controllers;

use App\Models\Clientes;
use Illuminate\Http\Request;

class clienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Paginação de 1000 clientes por página
        $clientes = Clientes::paginate(1000);

        // Retornar os dados como JSON
        return response()->json([
            'clientes' => $clientes
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
        $request->validate([
            'abreviacao' => 'required|string|max:255',
            'nome' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'sexo' => 'required|string|max:255',
        ]);
        // Criação do cliente no banco de dados
        $cliente = Clientes::create([
            'abreviacao' => $request->input('abreviacao'),
            'nome' => $request->input('nome'),
            'email' => $request->input('email'),
            'sexo' => $request->input('sexo'),
        ]);
        // Retorna o cliente criado em formato JSON
        return response()->json($cliente, 201); // 201 = Created
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
