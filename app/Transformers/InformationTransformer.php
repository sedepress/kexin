<?php

namespace App\Transformers;

use App\Models\Information;
use League\Fractal\TransformerAbstract;

class InformationTransformer extends TransformerAbstract
{
    public function transform(Information $information)
    {
        return [
            'id' => $information->id,
            'title' => $information->title,
            'content' => $information->content,
            'publisher' => $information->publisher,
            'image_url' => $information->image_url,
            'status' => $information->status,
            'order' => $information->order,
            'created_at' => $information->created_at->format('Y-m-d'),
            'updated_at' => $information->updated_at->format('Y-m-d H:i'),
        ];
    }
}