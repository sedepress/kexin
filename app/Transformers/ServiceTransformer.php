<?php

namespace App\Transformers;

use App\Models\Service;
use League\Fractal\TransformerAbstract;

class ServiceTransformer extends TransformerAbstract
{
    public function transform(Service $Service)
    {
        return [
            'id' => $Service->id,
            'name' => $Service->name,
            'url' => $Service->url,
            'image_url' => $Service->image_url,
            'area_id' => $Service->area_id,
            'order' => $Service->order,
            'created_at' => $Service->created_at->toDateTimeString(),
            'updated_at' => $Service->updated_at->toDateTimeString(),
        ];
    }
}