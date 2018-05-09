<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inspection extends Model
{
    protected $table = 'inspections';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order'
    ];

    public function getStatusAttribute($value)
    {
        $status = [1 => '有效', 0 => '无效'];
        return $status[$value];
    }
}
