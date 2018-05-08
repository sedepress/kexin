<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Intellectual extends Model
{
    protected $table = 'intellectuals';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order'
    ];
}
