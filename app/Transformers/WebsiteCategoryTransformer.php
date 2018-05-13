<?php

namespace App\Transformers;

use App\Models\WebsiteCategory;
use League\Fractal\TransformerAbstract;

class WebsiteCategoryTransformer extends TransformerAbstract
{
    public function transform(WebsiteCategory $website_category)
    {
        return [
            'id' => $website_category->id,
            'name' => $website_category->name,
            'parent_id' => $website_category->parent_id,
        ];
    }
}