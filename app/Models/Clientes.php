<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;
    protected $fillable = [
        'abreviacao',
        'nome',
        'email',
        'sexo'
    ];


    public function transacoes()
    {
        return $this->hasMany(Transacaos::class, 'cliente_id');
    }
}
