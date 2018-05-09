<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    protected $table = 'informations';
    protected $fillable = [
        'title', 'content', 'publisher', 'image_url', 'status', 'order'
    ];

    public function getStatusAttribute($value)
    {
        $status = [1 => '有效', 0 => '无效'];
        return $status[$value];
    }
}
