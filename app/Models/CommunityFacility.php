<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityFacility extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'community_facility';
    protected $primaryKey = 'id';

    protected $fillable = [
        'community_profile_id',
        'facility',
        'key',
        'value',
        'city',
        'deleted_at'
    ];
}
