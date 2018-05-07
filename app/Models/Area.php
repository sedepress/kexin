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
}
