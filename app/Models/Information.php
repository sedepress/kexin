<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Information extends Model
{
    protected $table = 'informations';
    protected $fillable = [
        'title', 'content', 'publisher', 'image_url', 'status', 'order'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxOrder = Information::max('order');
            $model->order = $maxOrder + 1 ?? 1;
        });
    }

    public function getStatusAttribute($value)
    {
        $status = [1 => '已生效', 0 => '未生效'];
        return $status[$value];
    }
}
