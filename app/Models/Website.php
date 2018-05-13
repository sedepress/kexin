<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model
{
    protected $table = 'websites';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order', 'website_category_id'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxOrder = Website::max('order');
            $model->order = $maxOrder + 1 ?? 1;
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function websiteCategories()
    {
        return $this->belongsTo(WebsiteCategory::class);
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