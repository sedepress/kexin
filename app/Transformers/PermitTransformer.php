<?php

namespace App\Transformers;

use App\Models\Permit;
use League\Fractal\TransformerAbstract;

class PermitTransformer extends TransformerAbstract
{
    public function transform(Permit $Permit)
    {
        return [
            'id' => $Permit->id,
            'name' => $Permit->name,
            'url' => $Permit->url,
            'image_url' => $Permit->image_url,
            'area_id' => $Permit->area_id,
            'order' => $Permit->order,
            'created_at' => $Permit->created_at->toDateTimeString(),
            'updated_at' => $Permit->updated_at->toDateTimeString(),
        ];
    }
}