<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebsiteCategory extends Model
{
    protected $table = 'website_categories';
    protected $fillable = [
        'name', 'parent_id'
    ];
    protected $hidden = ['created_at', 'updated_at'];

    public function parent()
    {
        return $this->belongsTo(Area::class,'parent_id');
    }

    public function children()
    {
        return $this->belongsTo(Area::class, 'id', 'parent_id');
    }

    public function websites()
    {
        return $this->hasMany(Website::class);
    }
}
