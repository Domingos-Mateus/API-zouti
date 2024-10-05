<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class usuariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $users = User::paginate(1000);
        $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);
        return view('usuarios.app_listar_usuarios', compact('primeiro_caractere_usuario','users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Obtendo o primeiro caractere do nome do usuário
        $primeiro_caractere_usuario = substr(Auth::user()->name, 0, 1);

        // Obtendo o nome fantasia do usuário
        $nome_fantasia = Auth::user()->nome_fantasia;

        // Obtendo as duas primeiras letras do nome fantasia e convertendo-as para maiúsculas
        $inicial_nome_fantasia = strtoupper(substr($nome_fantasia, 0, 2));


        // Retornando a view com as variáveis necessárias
        return view('usuarios.app_registar_usuario', compact('primeiro_caractere_usuario', 'inicial_nome_fantasia'));
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required|string|max:255',
            'nome_fantasia' => 'required|string|max:255',
            'cnpj' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->nome_fantasia = $request->nome_fantasia;
        $user->cnpj = $request->cnpj;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->save();

        return back()->with('success', 'Usuário salvo com sucesso!');
    }

    // Métodos restantes omitidos por brevidade
}
