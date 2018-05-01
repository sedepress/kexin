<?php

namespace App\Transformers;

use App\Models\DeclarationCategory;
use League\Fractal\TransformerAbstract;

class DeclarationCategoryTransformer extends TransformerAbstract
{
    public function transform(DeclarationCategory $declaration_category)
    {
        return [
            'id' => $declaration_category->id,
            'name' => $declaration_category->name,
        ];
    }
}