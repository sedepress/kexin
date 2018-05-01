<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Literature extends Model
{
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id'
    ];
}
