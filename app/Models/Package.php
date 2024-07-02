<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $table = 'package';

    protected $fillable = [
        'package_type',
        'duration',
        'amount',
        'profile_id',
        'gst_in_percent',
        'status',
        'total',
        'package_description',
    ];
}
