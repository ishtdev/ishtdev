<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lord extends Model
{
    use HasFactory;
    protected $table = 'lord';
    protected $fillable = [
        'lord_name'
    ];
}
