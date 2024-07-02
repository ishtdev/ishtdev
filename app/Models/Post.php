<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'post';

    protected $fillable = [
        'post_type',
        'caption',
        //'post_data',
        'city',
        'status',
        'profile_id',
        'deleted_at',
        'interest_name',
        'interest_id',
        'boost_status',
        'start_date',
        'end_date',
        'deleted_by',
    ];

    public function profile()
    {
        return $this->belongsTo(Profile::class, 'profile_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function postLikes()
    {
        return $this->hasMany(PostLike::class)->where("like_flag", 1);
    }

    public function postRelatedData()
    {
        return $this->hasMany(PostData::class);
    }

    public function postHashtag()
    {
        return $this->hasMany(PostHashtag::class);
    }

        public function postReport()
    {
        return $this->hasMany(PostReport::class);
    }
}
