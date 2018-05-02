<?php

namespace App\Transformers;

use App\Models\Literature;
use League\Fractal\TransformerAbstract;

class LiteratureTransformer extends TransformerAbstract
{
    public function transform(Literature $literature)
    {
        return [
            'id' => $literature->id,
            'name' => $literature->name,
            'url' => $literature->url,
            'image_url' => $literature->image_url,
            'area_id' => $literature->area_id,
            'order' => $literature->order,
            'created_at' => $literature->created_at->toDateTimeString(),
            'updated_at' => $literature->updated_at->toDateTimeString(),
        ];
    }
}