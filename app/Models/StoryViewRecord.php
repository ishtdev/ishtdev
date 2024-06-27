<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoryViewRecord extends Model
{
    use HasFactory;
    protected $table = "story_view_record";
    protected $fillables = [
        'viewer_profile_id',
        'story_profile_id',
        'story_post_id',
    ];
}
