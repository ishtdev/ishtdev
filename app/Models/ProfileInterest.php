<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileInterest extends Model
{
    use HasFactory;
    protected $table = "profile_interest";
    protected $fillable = [
        "name_of_interest",
        "Profile_id",
        "interest_id",
        "interest_flag",
    ];
}
