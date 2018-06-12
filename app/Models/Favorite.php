<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table = 'favorites';
    protected $fillable = [
        'name', 'user_id', 'website', 'icon'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
