<?php

namespace App\Transformers;

use App\Models\Other;
use League\Fractal\TransformerAbstract;

class OtherTransformer extends TransformerAbstract
{
    public function transform(Other $Other)
    {
        return [
            'id' => $Other->id,
            'name' => $Other->name,
            'url' => $Other->url,
            'image_url' => $Other->image_url,
            'area_id' => $Other->area_id,
            'order' => $Other->order,
            'created_at' => $Other->created_at->toDateTimeString(),
            'updated_at' => $Other->updated_at->toDateTimeString(),
        ];
    }
}