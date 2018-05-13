<?php

namespace App\Transformers;

use App\Models\Area;
use App\Models\Mail;
use League\Fractal\TransformerAbstract;

class AreaTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['children', 'literature', 'mail'];

    public function transform(Area $area)
    {
        return [
            'id' => $area->id,
            'parent_id' => $area->parent_id,
            'name' => $area->name,
            'level' => $area->level,
        ];
    }

    public function includeChildren(Area $area)
    {
        return $this->collection($area->children()->get(), new AreaTransformer());
    }

    public function includeLiterature(Area $area)
    {
        return $this->collection($area->literatures()->get(), new LiteratureTransformer());
    }

    public function includeMail(Area $area)
    {
        return $this->collection($area->mail(), new MailTransformer());
    }

}