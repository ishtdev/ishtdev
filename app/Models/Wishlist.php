<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "wishlist";
    protected $fillables = [
        'title',
        'profile_id',
        'date',
        'planning_with',
        'total_member',
        'num_of_male',
        'num_of_female',
        'num_of_child',
        'deleted_at',
    ];
    public function user_detail(){
        return $this->belongsTo(UserDetails::class, 'profile_id', 'profile_id');
    }
}
