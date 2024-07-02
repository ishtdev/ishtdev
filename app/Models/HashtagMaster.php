<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HashtagMaster extends Model
{
    use HasFactory;

    protected $table = 'hashtag_master';

    protected $fillable = ['name'];

}
