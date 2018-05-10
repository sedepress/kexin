<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Declaration extends Model
{
    protected $table = 'declarations';
    protected $fillable = [
        'name', 'url', 'image_url', 'status', 'area_id', 'order', 'declaration_category_id'
    ];

    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $maxOrder = Declaration::max('order');
            $model->order = $maxOrder + 1 ?? 1;
        });
    }

    public function area()
    {
        return $this->belongsTo(Area::class);
    }

    public function category()
    {
        return $this->belongsTo(DeclarationCategory::class, 'declaration_category_id');
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
        $data = array_only($info, $need_data);
        $data['area_status'] = $this->area->level;
        $data['category'] = $this->category->name;

        return $data;
    }
}