<?php

namespace App\Http\Controllers\Api;

use App\Models\Literature;
use App\Transformers\LiteratureTransformer;
use App\Http\Requests\Api\LiteratureRequest;
use App\Handlers\ImageUploadHandler;

class LiteratureController extends Controller
{
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
        $data = $request->all();

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

    public function toggle(Literature $source, Literature $change)
    {
        list($source->order, $change->order) = [
            $change->toArray()['order'],
            $source->toArray()['order'],
        ];
        $change->update();
        $source->update();

        return $this->response->item($source, new LiteratureTransformer());
    }
}
