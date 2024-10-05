<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pixs extends Model
{
    use HasFactory;
    protected $fillable = ['pix'];

    public function pixs()
    {
        return $this->hasMany(Pixs::class);
    }
}
