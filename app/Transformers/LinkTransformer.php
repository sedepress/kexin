<?php

namespace App\Transformers;

use App\Models\Link;
use League\Fractal\TransformerAbstract;

class LinkTransformer extends TransformerAbstract
{
    public function transform(Link $Link)
    {
        return [
            'id' => $Link->id,
            'name' => $Link->name,
            'url' => $Link->url,
            'image_url' => $Link->image_url,
            'order' => $Link->order,
            'created_at' => $Link->created_at->toDateTimeString(),
            'updated_at' => $Link->updated_at->toDateTimeString(),
        ];
    }
}