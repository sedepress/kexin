<?php

namespace App\Http\Controllers\Api;

use App\Models\Website;
use App\Models\Area;
use App\Transformers\WebsiteTransformer;
use App\Http\Requests\Api\WebsiteRequest;
use App\Handlers\ImageUploadHandler;
use Excel;

class WebsiteController extends Controller
{
    public function index(WebsiteRequest $request)
    {
        $websites = $this->search($request->all())->orderBy('order')->paginate(10);

        return $this->response->paginator($websites, new WebsiteTransformer());
    }

    protected function search($data)
    {
        $websites = Website::with('area')->where(function($query) use ($data){
            if(isset($data['area_id'])){
                $area = Area::find($data['area_id']);
                $area_id_list = $area->children()
                    ->get(['id'])
                    ->toArray();
                $area_id_list = array_pluck($area_id_list,'id');
                $area_id_list[] = $data['area_id'];
                $query->whereIn('area_id',$area_id_list);
            }
        })
        ->where(function($query) use ($data){
            $query->where('website_category_id', $data['website_category_id']);
        })
        ->where(function($query) use ($data){
            if(isset($data['name'])){
                $name = '%'.$data['name'].'%';
                $query->where('name', 'like', $name);
            }
        })
        ->where(function($query) use ($data){
            if(isset($data['status'])){
                $query->whereStatus($data['status']);
            }
        })
        ->where(function($query) use ($data){
            if(isset($data['id'])){
                $query->whereId($data['id']);
            }
        });

        return $websites;
    }

    public function store(WebsiteRequest $request, ImageUploadHandler $uploader, Website $website)
    {
        $website->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'websites', 2018);
            if ($result) {
                $website->image_url = $result['path'];
            }
        }

        $website->save();

        return $this->response->item($website, new WebsiteTransformer())
            ->setStatusCode(201);
    }

    public function update(WebsiteRequest $request, ImageUploadHandler $uploader, Website $website)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'websites', $website->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$website->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $website->update($data);

        if($website->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($website, new WebsiteTransformer());
    }

    public function destroy(Website $website)
    {
        $oldImg = explode('/',$website->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $website->delete();
        return $this->response->noContent();
    }

    public function toggle(WebsiteRequest $request, Website $website)
    {
        $status = $request->status;
        $website_category_id = $website->website_category_id;

        $maxOrder = Website::where('website_category_id', $website_category_id)->max('order');
        $minOrder = Website::where('website_category_id', $website_category_id)->min('order');

        if ($status == 'up' && $website->order != $minOrder) {
            $up = Website::where([
                ['order', '<', $website->order],
                ['website_category_id', $website_category_id],
            ])->orderBy('order', 'desc')->first();
            list($website->order, $up->order) = [$up->order, $website->order];
            $up->update();
        } elseif ($status == 'down' && $website->order != $maxOrder) {
            $down = Website::where([
                ['order', '>', $website->order],
                ['website_category_id', $website_category_id],
            ])->orderBy('order', 'asc')->first();
            list($website->order, $down->order) = [$down->order, $website->order];
            $down->update();
        } else {
            return;
        }
        $website->update();

        return $this->response->item($website, new WebsiteTransformer());
    }

    protected function export(WebsiteRequest $request)
    {
        $websites = $this->search($request->all())->orderBy('order')->with('area')->get();
        $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
        foreach ($websites as $website) {
            $website = $website->simpleInfo();
            $export_data[] = $website;
        }

        Excel::create('网站',function($excel) use($export_data){
            $excel->sheet('website',function($sheet) use($export_data){
                $sheet->rows($export_data);
            });
        })->export('xls');
    }

    protected function import()
    {
        $file = $_FILES;
        $excel_file_path = $file['file']['tmp_name'];
        Excel::load($excel_file_path, function($reader) {
            $data = $reader->all()->toArray();
            foreach ($data as $v) {
                $info = ['name' => $v['名称'], 'url' => $v['链接'], 'website_category_id' => $v['分类']];
                Website::create($info);
            }
        });
    }
}
