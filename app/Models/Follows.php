<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follows extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'follows';

    protected $fillable = [
        'following_profile_id',
        'followed_profile_id',
    ];

}


