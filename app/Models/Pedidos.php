<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos extends Model
{
    use HasFactory;

    protected $fillable = ['pedido'];

    public function pedidos()
    {
        return $this->hasMany(Pedidos::class);
    }
}
