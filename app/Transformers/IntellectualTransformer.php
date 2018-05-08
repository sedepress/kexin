<?php

namespace App\Transformers;

use App\Models\Intellectual;
use League\Fractal\TransformerAbstract;

class IntellectualTransformer extends TransformerAbstract
{
    public function transform(Intellectual $intellectual)
    {
        return [
            'id' => $intellectual->id,
            'name' => $intellectual->name,
            'url' => $intellectual->url,
            'image_url' => $intellectual->image_url,
            'area_id' => $intellectual->area_id,
            'order' => $intellectual->order,
            'created_at' => $intellectual->created_at->toDateTimeString(),
            'updated_at' => $intellectual->updated_at->toDateTimeString(),
        ];
    }
}