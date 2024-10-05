<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chargebacks extends Model
{
    use HasFactory;

    protected $fillable = [
        'quantidade',
        'data'
    ];

    public function chargebacks()
    {
        return $this->hasMany(Chargebacks::class);
    }
}
