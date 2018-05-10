<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'services';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxOrder = Service::max('order');
            $model->order = $maxOrder + 1 ?? 1;
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function getStatusAttribute($value)
    {
        $status = [1 => '有效', 0 => '无效'];
        return $status[$value];
    }

    public function simpleInfo()
    {
        $need_data = ['id', 'area_status', 'image_url', 'name', 'url', 'status'];
        $info = $this->toArray();
        $info['area_status'] = $this->area->level;
        $data = array_only($info, $need_data);

        return $data;
    }
}
