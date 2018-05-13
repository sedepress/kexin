<?php

namespace App\Transformers;

use App\Models\Website;
use League\Fractal\TransformerAbstract;

class WebsiteTransformer extends TransformerAbstract
{
    public function transform(Website $website)
    {
        return [
            'id' => $website->id,
            'name' => $website->name,
            'website_category_id' => $website->website_category_id,
            'url' => $website->url,
            'image_url' => $website->image_url,
            'area_id' => $website->area->level,
            'order' => $website->order,
            'created_at' => $website->created_at->toDateTimeString(),
            'updated_at' => $website->updated_at->toDateTimeString(),
        ];
    }
}