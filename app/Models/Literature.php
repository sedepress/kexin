<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Literature extends Model
{
    protected $table = 'literatures';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order'
    ];

    public function area()
    {
        return $this->belongsTo(Area::class);
    }
}
