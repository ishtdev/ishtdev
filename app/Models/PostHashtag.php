<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostHashtag extends Model
{
    use HasFactory, SoftDeletes;
    public $timestamps = false;

    protected $table = 'post_hashtag';

    protected $fillable = ['post_id','hashtag_id','deleted_at',];

    public function post()
    {
        return $this->belongsTo(Post::class ,'post_id' );
    }

    public function hashtag()
    {
        return $this->belongsTo(HashtagMaster::class);
    }


}
