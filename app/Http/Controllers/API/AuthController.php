<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function getToken(Request $request)
    {
        // Criando uma instância do cliente HTTP Guzzle
        $client = new Client();

        try {
            // Enviar a requisição POST para a URL da API
            $response = $client->post('https://auth.v8sistema.com/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'username'   => 'werlensenna95@outlook.com',
                    'password'   => 'Banco@2025',
                    'audience'   => 'https://bff.v8sistema.com',
                    'scope'      => 'offline_access',
                    'client_id'  => 'DHWogdaYmEI8n5bwwxPDzulMlSK7dwIn',
                ],
            ]);

            // Obter o corpo da resposta (que inclui o token)
            $body = json_decode($response->getBody(), true);

            // Retornar o token
            return response()->json([
                'token' => $body['access_token']
            ]);

        } catch (\Exception $e) {
            // Tratar erros de requisição
            return response()->json([
                'error' => 'Não foi possível gerar o token',
                'message' => $e->getMessage()
            ], 500);
        }
    }


    public function register(Request $request)
    {
        // Validação dos dados de entrada
        $data = $request->validate([
            'name' => 'required|string|max:191',
            'email' => 'required|email|max:191|unique:users,email',
            'password' => 'required|string',
        ]);

        // Criação do usuário no banco de dados
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Gerar o token localmente para o usuário autenticado (Laravel Sanctum, por exemplo)
        $localToken = $user->createToken('authToken')->plainTextToken;

        // Resposta que retorna o usuário e o token local
        $response = [
            'user' => $user,
            'token' => $localToken, // Token gerado pelo Laravel Sanctum
        ];

        // Retorna a resposta com status 201 (Created)
        return response($response, 201);
    }


    public function logout(){
    	// $user = Auth::user();
    	// $user->tokens()->delete();
    	auth()->user()->tokens()->delete();
    	return response(['message'=>'logout feito com sucesso']);
    }

    public function login(Request $request)
{
    // Validação dos dados de entrada
    $data = $request->validate([
        'email' => 'required|email|max:191',
        'password' => 'required|string',
    ]);

    // Verificar se o usuário existe com o email fornecido
    $user = User::where('email', $data['email'])->first();

    // Verificar se as credenciais estão corretas
    if (!$user || !Hash::check($data['password'], $user->password)) {
        return response(['message' => 'Credenciais inválidas'], 401);
    }

    // Gerar o token local para o usuário autenticado (Laravel Sanctum, por exemplo)
    $localToken = $user->createToken('authTokenLogin')->plainTextToken;

    // Resposta que retorna o usuário e o token local
    $response = [
        'user' => $user,
        'token' => $localToken, // Token gerado pelo Laravel Sanctum
    ];

    // Retorna a resposta com status 200 (OK)
    return response($response, 200);
}



}
