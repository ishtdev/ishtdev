<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Badge extends Model
{
    use HasFactory;
    protected $table = 'badge';
    protected $fillable = [
        'lord_id',
        'type',
        'image',
        
    ];
    public function lord()
    {
        return $this->belongsTo(Lord::class, 'lord_id');
    }
}
