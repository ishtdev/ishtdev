<?php

namespace App\Models;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Cashier\Billable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    //use HasFactory, Notifiable, Billable, SoftDeletes;
    use HasFactory, Notifiable, Billable;
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'device_key',
        'username',
        'mobile_number',
        'fcm_token',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

     public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    // public function following()
    // {
    //     return $this->profile->following(); 
    // }

    // public function followers()
    // {
    //     return $this->profile->followers(); 
    // }
    // public function profile()
    // {
    //     return $this->hasOne(Profile::class);
    // }

    // public function following()
    // {
    //     return $this->belongsToMany(User::class, 'follows', 'following_profile_id', 'followed_profile_id');
    // }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'follows', 'following_profile_id', 'followed_profile_id');
    }

}
