<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostReport extends Model
{
    use HasFactory;
    protected $table = 'post_report';
    protected $fillable = [
        'profile_id',
        'report_flag',
        'post_id',
    ];
        public function post()
    {
        return $this->belongsTo(Post::class ,'post_id' );
    }
        public function profile()
    {
        return $this->belongsTo(Profile::class ,'profile_id' );
    }
}