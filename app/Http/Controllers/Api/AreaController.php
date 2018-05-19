<?php

namespace App\Http\Controllers\Api;

use App\Models\Area;
use App\Transformers\AreaTransformer;

class AreaController extends Controller
{
    public function index()
    {
        $data = Area::select('id', 'parent_id', 'name')->get();
        return $data;
    }

    public function first()
    {
        $data = Area::find(1);
        return $this->response->item($data, new AreaTransformer());
    }

    public function second(Area $area)
    {
        return $this->response->item($area, new AreaTransformer());
    }

    public function three(Area $area)
    {
        return $this->response->item($area, new AreaTransformer());
    }
}
