<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'post_comment';
    protected $fillable = [
        'comment',
        'profile_id',
        'post_id',
        'parent_comment_id',
        'deleted_at',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class ,'post_id' );
    }

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id') ;
    }

    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_comment_id');
    }

}



