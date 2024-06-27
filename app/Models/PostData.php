<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostData extends Model
{
    use HasFactory;

    protected $table = 'post_data';

    protected $fillable = ['post_id', 'post_data','deleted_at',];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
