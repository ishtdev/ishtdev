<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kuldevta extends Model
{
    use HasFactory;
    protected $table = 'kuldevta';
    protected $fillable = [
        'name'
    ];
}
