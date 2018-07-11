<?php

namespace App\Http\Controllers\Api;

use App\Models\Area;
use App\Transformers\AreaTransformer;
use Illuminate\Http\Request;

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

    public function getIp(Request $request)
    {
        $ip = 'http://ip.taobao.com/service/getIpInfo.php?ip=' . $request->ip . '&qq-pf-to=pcqq.c2c';
        $data = json_decode(file_get_contents($ip), true);
        return $this->response->array($data);
    }

    public function getAid(Request $request)
    {
        $name = '%' . $request->name . '%';
        $data = Area::where('name', 'like', $name)->first();
        if ($data->id == '3164') {
            $data['default'] = Area::find(3167);
        } else {
            $data['default'] = Area::find($data->id + 1);
        }

        $data['parent'] = $data->parent;

        return $data;
    }

    public function show(Area $area)
    {
        $data['district'] = $area;
        $data['city'] = $area->parent;
        $data['province'] = $area->parent->parent;
        unset($data['city']);
        unset($data['province']);
        return $data;
    }
}
