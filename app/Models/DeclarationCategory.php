<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeclarationCategory extends Model
{
    protected $table = 'declaration_categories';
    protected $fillable = [
        'name',
    ];

    public function declatations()
    {
        return $this->hasMany(Declaration::class);
    }
}
