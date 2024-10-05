<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ordens extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_produto',
        'valor_produto',
        'total_pedidos',
        'quantidade_pedidos_pix',
        'percentagem_conversao_pix',
        'quantidade_pedidos_cartao',
        'genero_cliente',
        'user_id'
    ];


    public function ordens()
    {
        return $this->hasMany(Ordens::class);
    }
}
