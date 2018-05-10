<?php

namespace App\Transformers;

use App\Models\Declaration;
use League\Fractal\TransformerAbstract;

class DeclarationTransformer extends TransformerAbstract
{
    public function transform(Declaration $declaration)
    {
        return [
            'id' => $declaration->id,
            'name' => $declaration->name,
            'url' => $declaration->url,
            'image_url' => $declaration->image_url,
            'area_id' => $declaration->area_id,
            'order' => $declaration->order,
            'declaration_category_id' => $declaration->declaration_category_id,
            'created_at' => $declaration->created_at->toDateTimeString(),
            'updated_at' => $declaration->updated_at->toDateTimeString(),
        ];
    }
}