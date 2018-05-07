<?php

namespace App\Http\Controllers\Api;

use App\Models\Area;
use App\Transformers\AreaTransformer;

class AreaController extends Controller
{
    public function first()
    {
        $data = Area::find(1);
        return $this->response->item($data, new AreaTransformer());
    }

    public function second(Area $area)
    {
        $data = Area::find($area->id);
        return $this->response->item($data, new AreaTransformer());
    }

    public function three(Area $area)
    {
        $data = Area::find($area->id);
        return $this->response->item($data, new AreaTransformer());
    }
}
