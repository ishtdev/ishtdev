<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interest extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "interest";
    protected $fillable = [
        'name_of_interest',
        'image_of_interest',
        'deleted_at',
    ];
}
