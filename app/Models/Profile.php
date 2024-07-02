<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;
    protected $table = 'profile';
    protected $fillable = ['user_id', 'user_type_id'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
      public function address()
    {
        return $this->hasOne(Address::class,'profile_id');
    }


    public function userType()
    {
        return $this->hasOne(UserType::class, 'id', 'user_type_id');
    }

    public function UserDetail()
    {
        return $this->hasOne(UserDetails::class, 'profile_id', 'id');
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'profile_id');
    }

    public function following()
    {
        return $this->belongsToMany(Profile::class, 'follows', 'following_profile_id', 'followed_profile_id');
    }

    public function followers()
    {
        return $this->belongsToMany(Profile::class, 'follows', 'followed_profile_id', 'following_profile_id');
    }
    public function communityDetail() {
        return $this->hasOne(CommunityDetail::class, 'profile_id');
    }
}
