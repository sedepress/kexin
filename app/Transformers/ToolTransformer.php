<?php

namespace App\Transformers;

use App\Models\Tool;
use League\Fractal\TransformerAbstract;

class ToolTransformer extends TransformerAbstract
{
    public function transform(Tool $tool)
    {
        return [
            'id' => $tool->id,
            'name' => $tool->name,
            'url' => $tool->url,
            'image_url' => $tool->image_url,
            'order' => $tool->order,
            'created_at' => $tool->created_at->toDateTimeString(),
            'updated_at' => $tool->updated_at->toDateTimeString(),
        ];
    }
}