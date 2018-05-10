<?php

namespace App\Transformers;

use App\Models\Government;
use League\Fractal\TransformerAbstract;

class GovernmentTransformer extends TransformerAbstract
{
    public function transform(Government $Government)
    {
        return [
            'id' => $Government->id,
            'name' => $Government->name,
            'url' => $Government->url,
            'image_url' => $Government->image_url,
            'area_id' => $Government->area_id,
            'order' => $Government->order,
            'created_at' => $Government->created_at->toDateTimeString(),
            'updated_at' => $Government->updated_at->toDateTimeString(),
        ];
    }
}