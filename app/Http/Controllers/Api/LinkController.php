<?php

namespace App\Http\Controllers\Api;

use App\Models\Link;
use App\Transformers\LinkTransformer;
use App\Http\Requests\Api\LinkRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class LinkController extends Controller
{
    public function index(LinkRequest $request)
    {
        $links = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($links, new LinkTransformer());
    }

    protected function search($data)
    {
        $links = Link::where(function($query) use ($data){
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

        return $links;
    }

    public function store(LinkRequest $request, ImageUploadHandler $uploader, Link $link)
    {
        $link->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Links', 2018);
            if ($result) {
                $link->image_url = $result['path'];
            }
        }

        $link->save();

        return $this->response->item($link, new LinkTransformer())
            ->setStatusCode(201);
    }

    public function update(LinkRequest $request, ImageUploadHandler $uploader, Link $link)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Links', $link->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$link->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $link->update($data);

        if($link->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($link, new LinkTransformer());
    }

    public function destroy(Link $link)
    {
        $oldImg = explode('/',$link->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $link->delete();
        return $this->response->noContent();
    }

    public function toggle(LinkRequest $request, Link $link)
    {
        $status = $request->status;

        $maxOrder = Link::max('order');
        $minOrder = Link::min('order');

        if ($status == 'up' && $link->order != $minOrder) {
            $up = Link::where('order', '<', $link->order)->orderBy('order', 'desc')->first();
            list($link->order, $up->order) = [$up->order, $link->order];
            $up->update();
        } elseif ($status == 'down' && $link->order != $maxOrder) {
            $down = Link::where('order', '>', $link->order)->orderBy('order', 'asc')->first();
            list($link->order, $down->order) = [$down->order, $link->order];
            $down->update();
        } else {
            return;
        }
        $link->update();

        return $this->response->item($link, new LinkTransformer());
    }

    protected function export(Request $request)
    {
        $data = array_filter($request->only(['name', 'status']));

        $validator = Validator::make($data, [
            'name' => 'string',
            'status' => 'in:1,0'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return Response::json($errors);
        }else{
            $links = $this->search($data)->orderBy('order')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态'];
            foreach ($links as $link) {
                $link = $link->simpleInfo();
                $export_data[] = $link;
            }

            Excel::create('友情链接',function($excel) use($export_data){
                $excel->sheet('Link',function($sheet) use($export_data){
                    $sheet->rows($export_data);
                });
            })->export('xls');
        }
    }

    protected function import()
    {
        $file = $_FILES;
        $excel_file_path = $file['file']['tmp_name'];
        Excel::load($excel_file_path, function($reader) {
            $data = $reader->all()->toArray();
            foreach ($data as $v) {
                $info = ['name' => $v['名称'], 'url' => $v['链接']];
                Link::create($info);
            }
        });
    }
}
