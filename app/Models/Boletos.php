<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boletos extends Model
{
    use HasFactory;

    protected $fillable = ['boleto'];

    public function boletos()
    {
        return $this->hasMany(Boletos::class);
    }
}
