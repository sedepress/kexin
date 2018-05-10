<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Link extends Model
{
    protected $table = 'links';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'order'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxOrder = Link::max('order');
            $model->order = $maxOrder + 1 ?? 1;
        });
    }

    public function getStatusAttribute($value)
    {
        $status = [1 => '有效', 0 => '无效'];
        return $status[$value];
    }

    public function simpleInfo()
    {
        $need_data = ['id','image_url', 'name', 'url', 'status'];
        $info = $this->toArray();
        $data = array_only($info, $need_data);

        return $data;
    }
}
