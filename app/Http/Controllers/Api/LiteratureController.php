<?php

namespace App\Http\Controllers\Api;

use App\Models\Literature;
use App\Transformers\LiteratureTransformer;
use App\Http\Requests\Api\LiteratureRequest;
use App\Handlers\ImageUploadHandler;

class LiteratureController extends Controller
{
    public function index(Literature $literature)
    {
        $literatures = $literature->orderBy('order')->paginate(10);
        return $this->response->paginator($literatures, new LiteratureTransformer());
    }

    public function store(LiteratureRequest $request, ImageUploadHandler $uploader, Literature $literature)
    {
        $literature->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'literatures', 2018);
            if ($result) {
                $literature->image_url = $result['path'];
            }
        }

        $maxOrder = Literature::max('order');
        if (!$maxOrder) {
            $maxOrder = 1;
        }
        $maxOrder += 1;

        $literature->order = $maxOrder;

        $literature->save();

        return $this->response->item($literature, new LiteratureTransformer())
            ->setStatusCode(201);
    }

    public function update(LiteratureRequest $request, ImageUploadHandler $uploader, Literature $literature)
    {
        $data = $request->all();dd($literature);

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'literatures', $literature->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$literature->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $literature->update($data);

        if($literature->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($literature, new LiteratureTransformer());
    }

    public function destroy(Literature $literature)
    {
        $oldImg = explode('/',$literature->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $literature->delete();
        return $this->response->noContent();
    }

    public function toggle(LiteratureRequest $request, Literature $literature)
    {
        $status = $request->status;

        $maxOrder = Literature::max('order');
        $minOrder = Literature::min('order');

        if ($status == 'up' && $literature->order != $minOrder) {
            $up = Literature::where('order', '<', $literature->order)->orderBy('order', 'desc')->first();
            list($literature->order, $up->order) = [$up->order, $literature->order];
            $up->update();
        } elseif ($status == 'down' && $literature->order != $maxOrder) {
            $down = Literature::where('order', '>', $literature->order)->orderBy('order', 'asc')->first();
            list($literature->order, $down->order) = [$down->order, $literature->order];
            $down->update();
        } else {
            return;
        }
        $literature->update();

        return $this->response->item($literature, new LiteratureTransformer());
    }
}
