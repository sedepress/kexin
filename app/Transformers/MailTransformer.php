<?php

namespace App\Transformers;

use App\Models\Mail;
use League\Fractal\TransformerAbstract;

class MailTransformer extends TransformerAbstract
{
    public function transform(Mail $Mail)
    {
        return [
            'id' => $Mail->id,
            'name' => $Mail->name,
            'url' => $Mail->url,
            'image_url' => $Mail->image_url,
            'order' => $Mail->order,
            'created_at' => $Mail->created_at->toDateTimeString(),
            'updated_at' => $Mail->updated_at->toDateTimeString(),
        ];
    }
}