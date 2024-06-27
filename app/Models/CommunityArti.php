<?php
// app/Models/CommunityArti.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommunityArti extends Model
{


    protected $table = 'community_arti';
    protected $primaryKey = 'id';


    protected $fillable = [
        'community_detail_id',
        'live_arti_link',
        'arti_time',

    ];

    protected $dates = ['deleted_at'];

    public function communityDetail()
    {
        return $this->belongsTo(CommunityDetail::class, 'community_detail_id');
    }

}
