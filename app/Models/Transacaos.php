<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transacaos extends Model
{
    use HasFactory;

    // Permite a inserção em massa desses campos
    protected $fillable = [
        'cliente_id',
        'nome_produto',
        'valor_produto',
        'ordem_id',
        'forma_pagamento',
        'status',
        'tipo_cartao',
        'data_pagamento',
        'data_vencimento',
        'parcelas',
        'valor_parcela',
        'user_id'
    ];

    // Relacionamento com o cliente
    public function cliente()
    {
        return $this->belongsTo(Clientes::class);
    }

    // Relacionamento com a ordem
    public function ordem()
    {
        return $this->belongsTo(Ordens::class);
    }

    // Relacionamento com o usuário
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
