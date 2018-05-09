<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areas';

    public function parent()
    {
        return $this->belongsTo(Area::class,'parent_id');
    }

    public function children()
    {
        return $this->belongsTo(Area::class, 'id', 'parent_id');
    }

    public function literatures()
    {
        return $this->hasMany(Literature::class);
    }

    public function getLevelAttribute($value)
    {
        switch ($value)
        {
            case 'country':
                $value = '国家';
                break;
            case 'province':
                $value = '省';
                break;
            case 'city':
                $value = '市';
                break;
            default:
                $value = '区';
        }

        return $value;
    }
}
