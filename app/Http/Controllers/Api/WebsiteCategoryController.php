<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\WebsiteCategoryRequest;
use App\Models\WebsiteCategory;
use App\Transformers\WebsiteCategoryTransformer;
use App\Libs\Helper;
use App\Models\Area;
use App\Models\Website;
use App\Models\Information;
use Illuminate\Http\Request;

class WebsiteCategoryController extends Controller
{
    public function index()
    {
        return $this->response->collection(WebsiteCategory::all(), new WebsiteCategoryTransformer());
    }

    public function lists(Request $request)
    {
        $area_id = $request->area_id;
        //正式环境需要修改id
        $data = WebsiteCategory::with(['websites' => function ($query) use ($area_id) {
            $query->whereIn('area_id', [$area_id, 0]);
        }])->whereNotIn('id', [1,9])->get();
        $data = Helper::getTree($data);
        $area_type = Area::find($area_id)->value('level');
        $area_lists = [];
        $area_lists['country'] = Website::whereAreaId(1)->get();

        switch ($area_type)
        {
            case '省':
                $area_lists['province'] = Website::whereAreaId($area_id)->get();
            case '市':
                $city = Area::find($area_id);
                $area_lists['province'] = Website::whereAreaId($city->parent_id)->get();
                $area_lists['city'] = Website::whereAreaId($city->id)->get();
            case '区':
                $district = Area::find($area_id);
                $city = Area::find($district->parent_id);
                $area_lists['province'] = Website::whereAreaId($city->parent_id)->get();
                $area_lists['city'] = Website::whereAreaId($district->parent_id)->get();
                $area_lists['district'] = Website::whereAreaId($district->id)->get();
        }

        $informations = Information::select('id', 'title', 'image_url')->orderBy('created_at', 'DESC')->limit(10)->get();
        $info = WebsiteCategory::find(1);
        $info['websites'] = $area_lists;
        array_unshift($data, $info);
        $inform = WebsiteCategory::find(9);
        $inform['websites'] = $informations;
        array_push($data, $inform);

        return $data;
    }

    public function store(WebsiteCategoryRequest $request, WebsiteCategory $website_category)
    {
        $website_category->fill($request->all());
        $website_category->save();

        return $this->response->item($website_category, new WebsiteCategoryTransformer())
            ->setStatusCode(201);
    }

    public function update(WebsiteCategoryRequest $request, WebsiteCategory $website_category)
    {
        $website_category->update($request->all());

        return $this->response->item($website_category, new WebsiteCategoryTransformer());
    }

    public function delete(WebsiteCategory $website_category)
    {
        $website_category->delete();

        return $this->response->noContent();
    }
}
