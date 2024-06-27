<?php
// app/Models/CommunityHistory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityHistory extends Model
{
    use SoftDeletes;

    protected $table = 'community_history';
    protected $primaryKey = 'id';


    protected $fillable = [
        'community_detail_id',
        'history',
        'status',
        'deleted_at'
    ];

    protected $dates = ['deleted_at'];

    public function communityDetail()
    {
        return $this->belongsTo(CommunityDetail::class, 'community_detail_id');
    }
}
