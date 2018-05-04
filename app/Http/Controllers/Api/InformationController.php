<?php

namespace App\Http\Controllers\Api;

use App\Models\Information;
use App\Transformers\InformationTransformer;
use App\Http\Requests\Api\InformationRequest;
use App\Handlers\ImageUploadHandler;

class InformationController extends Controller
{
    public function store(InformationRequest $request, ImageUploadHandler $uploader, Information $Information)
    {
        $Information->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Informations', 2018);
            if ($result) {
                $Information->image_url = $result['path'];
            }
        }

        $maxOrder = Information::max('order');
        if (!$maxOrder) {
            $maxOrder = 1;
        }
        $maxOrder += 1;

        $Information->order = $maxOrder;

        $Information->save();

        return $this->response->item($Information, new InformationTransformer())
            ->setStatusCode(201);
    }

    public function update(InformationRequest $request, ImageUploadHandler $uploader, Information $Information)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Informations', $Information->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Information->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Information->update($data);

        if($Information->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Information, new InformationTransformer());
    }

    public function destroy(Information $Information)
    {
        $oldImg = explode('/',$Information->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Information->delete();
        return $this->response->noContent();
    }

    public function toggle(Information $source, Information $change)
    {
        list($source->order, $change->order) = [
            $change->toArray()['order'],
            $source->toArray()['order'],
        ];
        $change->update();
        $source->update();

        return $this->response->item($source, new InformationTransformer());
    }

    public function status(InformationRequest $request, Information $Information)
    {
        $status = $request->status;
        $Information->status = $status == 'yes' ? true : false;
        $Information->update();

        return $this->response->item($Information, new InformationTransformer());
    }
}
