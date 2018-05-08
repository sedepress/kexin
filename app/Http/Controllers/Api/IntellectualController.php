<?php

namespace App\Http\Controllers\Api;

use App\Models\Intellectual;
use App\Transformers\IntellectualTransformer;
use App\Http\Requests\Api\IntellectualRequest;
use App\Handlers\ImageUploadHandler;

class IntellectualController extends Controller
{
    public function index(Intellectual $Intellectual)
    {
        $Intellectuals = $Intellectual->orderBy('order')->paginate(10);
        return $this->response->paginator($Intellectuals, new IntellectualTransformer());
    }

    public function store(IntellectualRequest $request, ImageUploadHandler $uploader, Intellectual $Intellectual)
    {
        $Intellectual->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Intellectuals', 2018);
            if ($result) {
                $Intellectual->image_url = $result['path'];
            }
        }

        $maxOrder = Intellectual::max('order');
        if (!$maxOrder) {
            $maxOrder = 1;
        }
        $maxOrder += 1;

        $Intellectual->order = $maxOrder;

        $Intellectual->save();

        return $this->response->item($Intellectual, new IntellectualTransformer())
            ->setStatusCode(201);
    }

    public function update(IntellectualRequest $request, ImageUploadHandler $uploader, Intellectual $Intellectual)
    {
        $data = $request->all();dd($Intellectual);

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Intellectuals', $Intellectual->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Intellectual->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Intellectual->update($data);

        if($Intellectual->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Intellectual, new IntellectualTransformer());
    }

    public function destroy(Intellectual $Intellectual)
    {
        $oldImg = explode('/',$Intellectual->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Intellectual->delete();
        return $this->response->noContent();
    }

    public function toggle(IntellectualRequest $request, Intellectual $Intellectual)
    {
        $status = $request->status;

        $maxOrder = Intellectual::max('order');
        $minOrder = Intellectual::min('order');

        if ($status == 'up' && $Intellectual->order != $minOrder) {
            $up = Intellectual::where('order', '<', $Intellectual->order)->orderBy('order', 'desc')->first();
            list($Intellectual->order, $up->order) = [$up->order, $Intellectual->order];
            $up->update();
        } elseif ($status == 'down' && $Intellectual->order != $maxOrder) {
            $down = Intellectual::where('order', '>', $Intellectual->order)->orderBy('order', 'asc')->first();
            list($Intellectual->order, $down->order) = [$down->order, $Intellectual->order];
            $down->update();
        } else {
            return;
        }
        $Intellectual->update();

        return $this->response->item($Intellectual, new IntellectualTransformer());
    }
}
