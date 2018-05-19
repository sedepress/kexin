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
use Response;

class WebsiteCategoryController extends Controller
{
    public function index()
    {
        $data = WebsiteCategory::all();
        $data = Helper::getTree($data);
        return Response::json($data);
    }

    public function left(Request $request)
    {
        $area_id = $request->area_id;
        //正式环境需要修改id
        $data = WebsiteCategory::with(['websites' => function ($query) use ($area_id) {
            $query->whereIn('area_id', [$area_id, 0]);
        }])->whereIn('id', [14,15])->get()->toArray();

        $informations = Information::limit(10)->get();
        $informs = WebsiteCategory::select('id', 'name')->find(2);
        $informs['websites'] = $informations;
        array_unshift($data, $informs);

        $area_type = Area::whereId($area_id)->value('level');
        $area_lists = [];
        $area_lists['country']['country_name'] = Area::whereId(1)->value('name');
        $area_lists['country']['websites'] = Website::where([['area_id', 1], ['website_category_id', 16]])->get();

        switch ($area_type)
        {
            case '省':
                $area_lists['province']['province_name'] = Area::whereId($area_id)->value('name');
                $area_lists['province']['websites'] = Website::whereAreaId($area_id)->get();
                $area_lists['province']['websites'] = Website::where([['area_id', $area_id], ['website_category_id', 16]])->get();
                break;
            case '市':
                $city = Area::find($area_id);
                $area_lists['province']['province_name'] = Area::whereId($city->parent_id)->value('name');
                $area_lists['province']['websites'] = Website::where([['area_id', $city->parent_id], ['website_category_id', 16]])->get();
                $area_lists['city']['city_name'] = Area::whereId($city->id)->value('name');
                $area_lists['city']['websites'] = Website::where([['area_id', $city->id], ['website_category_id', 16]])->get();
                break;
            case '区':
                $district = Area::find($area_id);
                $city = Area::find($district->parent_id);
                $area_lists['province']['province_name'] = Area::whereId($city->parent_id)->value('name');
                $area_lists['province']['websites'] = Website::where([['area_id', $city->parent_id], ['website_category_id', 16]])->get();
                $area_lists['city']['city_name'] = Area::whereId($district->parent_id)->value('name');
                $area_lists['city']['websites'] = Website::where([['area_id', $district->parent_id], ['website_category_id', 16]])->get();
                $area_lists['district']['district_name'] = Area::whereId($district->id)->value('name');
                $area_lists['district']['websites'] = Website::where([['area_id', $district->id], ['website_category_id', 16]])->get();
                break;
        }

        $info = WebsiteCategory::find(16);
        $info['websites'] = $area_lists;
        array_push($data, $info);

        return $data;
    }

    public function right()
    {
        $data = WebsiteCategory::with('websites')->find(17);

        return $data;
    }

    public function lists(Request $request)
    {
        $area_id = $request->area_id;
        //正式环境需要修改id
        $data = WebsiteCategory::with(['websites' => function ($query) use ($area_id) {
            $query->whereIn('area_id', [$area_id, 0]);
        }])->whereNotIn('id', [1,2,14,15,16,17])->get();
        $data = Helper::getTree($data);
        $area_type = Area::whereId($area_id)->value('level');
        $area_lists = [];
        $area_lists['country']['country_name'] = Area::whereId(1)->value('name');
        $area_lists['country']['websites'] = Website::where([['area_id', 1], ['website_category_id', 1]])->get();

        switch ($area_type)
        {
            case '省':
                $area_lists['province']['province_name'] = Area::whereId($area_id)->value('name');
                $area_lists['province']['websites'] = Website::whereAreaId($area_id)->get();
                $area_lists['province']['websites'] = Website::where([['area_id', $area_id], ['website_category_id', 1]])->get();
                break;
            case '市':
                $city = Area::find($area_id);
                $area_lists['province']['province_name'] = Area::whereId($city->parent_id)->value('name');
                $area_lists['province']['websites'] = Website::where([['area_id', $city->parent_id], ['website_category_id', 1]])->get();
                $area_lists['city']['city_name'] = Area::whereId($city->id)->value('name');
                $area_lists['city']['websites'] = Website::where([['area_id', $city->id], ['website_category_id', 1]])->get();
                break;
            case '区':
                $district = Area::find($area_id);
                $city = Area::find($district->parent_id);
                $area_lists['province']['province_name'] = Area::whereId($city->parent_id)->value('name');
                $area_lists['province']['websites'] = Website::where([['area_id', $city->parent_id], ['website_category_id', 1]])->get();
                $area_lists['city']['city_name'] = Area::whereId($district->parent_id)->value('name');
                $area_lists['city']['websites'] = Website::where([['area_id', $district->parent_id], ['website_category_id', 1]])->get();
                $area_lists['district']['district_name'] = Area::whereId($district->id)->value('name');
                $area_lists['district']['websites'] = Website::where([['area_id', $district->id], ['website_category_id', 1]])->get();
                break;
        }

        $info = WebsiteCategory::find(1);
        $info['websites'] = $area_lists;
        array_unshift($data, $info);

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
