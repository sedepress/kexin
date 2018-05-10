<?php

namespace App\Transformers;

use App\Models\Inspection;
use League\Fractal\TransformerAbstract;

class InspectionTransformer extends TransformerAbstract
{
    public function transform(Inspection $Inspection)
    {
        return [
            'id' => $Inspection->id,
            'name' => $Inspection->name,
            'url' => $Inspection->url,
            'image_url' => $Inspection->image_url,
            'area_id' => $Inspection->area_id,
            'order' => $Inspection->order,
            'created_at' => $Inspection->created_at->toDateTimeString(),
            'updated_at' => $Inspection->updated_at->toDateTimeString(),
        ];
    }
}