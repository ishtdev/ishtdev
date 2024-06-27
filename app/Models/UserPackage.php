<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPackage extends Model
{
    use HasFactory;
    
    protected $table = 'user_package';

    protected $fillable = [
        'profile_id',
        'package_type',
        'duration',
        'amount',
        'gst_in_percent',
        'total',
        'package_description',
        'post_id',
        'status',
        'rejected_reason',
    ];
    public function packageDetail()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
    public function postDetail()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
    public function profileDetail()
    {
        return $this->belongsTo(UserDetails::class, 'profile_id', 'profile_id');
    }
}
