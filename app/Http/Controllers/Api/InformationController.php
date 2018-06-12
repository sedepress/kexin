<?php

namespace App\Http\Controllers\Api;

use App\Models\Information;
use App\Transformers\InformationTransformer;
use App\Http\Requests\Api\InformationRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
class InformationController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->all();
        $informations = $this->search($data)->orderBy('order')->paginate(10);
        return $this->response->paginator($informations, new InformationTransformer());
    }

    protected function search($data)
    {
        $informations = Information::where(function($query) use ($data){
                if(isset($data['title'])){
                    $name = '%'.$data['title'].'%';
                    $query->where('title', 'like', $name);
                }
            })
            ->where(function($query) use ($data){
                if(isset($data['status'])){
                    $query->whereStatus($data['status']);
                }
            });

        return $informations;
    }

    public function show(Information $information)
    {
        return $this->response->item($information, new InformationTransformer());
    }

    public function store(InformationRequest $request, ImageUploadHandler $uploader, Information $information)
    {
        $information->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Informations', 2018);
            if ($result) {
                $information->image_url = $result['path'];
            }
        }

        $information->status = true;
        $information->save();

        return $this->response->item($information, new InformationTransformer())
            ->setStatusCode(201);
    }

    public function update(InformationRequest $request, ImageUploadHandler $uploader, Information $information)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Informations', $information->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$information->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $information->update($data);

        if($information->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($information, new InformationTransformer());
    }

    public function destroy(Information $information)
    {
        $oldImg = explode('/',$information->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $information->delete();
        return $this->response->noContent();
    }

    public function toggle(InformationRequest $request, Information $information)
    {
        $status = $request->status;

        $maxOrder = Information::max('order');
        $minOrder = Information::min('order');

        if ($status == 'up' && $information->order != $minOrder) {
            $up = Information::where('order', '<', $information->order)->orderBy('order', 'desc')->first();
            list($information->order, $up->order) = [$up->order, $information->order];
            $up->update();
        } elseif ($status == 'down' && $information->order != $maxOrder) {
            $down = Information::where('order', '>', $information->order)->orderBy('order', 'asc')->first();
            list($information->order, $down->order) = [$down->order, $information->order];
            $down->update();
        } else {
            return;
        }
        $information->update();

        return $this->response->item($information, new InformationTransformer());
    }

    public function status(InformationRequest $request, Information $information)
    {
        $information->status = $request->status == 'yes' ? true : false;
        $information->update();

        return $this->response->item($information, new InformationTransformer());
    }
}
