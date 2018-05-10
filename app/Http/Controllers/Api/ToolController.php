<?php

namespace App\Http\Controllers\Api;

use App\Models\Tool;
use App\Transformers\ToolTransformer;
use App\Http\Requests\Api\ToolRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class ToolController extends Controller
{
    public function index(ToolRequest $request)
    {
        $tools = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($tools, new ToolTransformer());
    }

    protected function search($data)
    {
        $tools = Tool::where(function($query) use ($data){
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

        return $tools;
    }

    public function store(ToolRequest $request, ImageUploadHandler $uploader, Tool $tool)
    {
        $tool->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Tools', 2018);
            if ($result) {
                $tool->image_url = $result['path'];
            }
        }

        $tool->save();

        return $this->response->item($tool, new ToolTransformer())
            ->setStatusCode(201);
    }

    public function update(ToolRequest $request, ImageUploadHandler $uploader, Tool $tool)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Tools', $tool->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$tool->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $tool->update($data);

        if($tool->update($data)&&$oldImgPath!=null){
            untool($oldImgPath);
        }
        return $this->response->item($tool, new ToolTransformer());
    }

    public function destroy(Tool $tool)
    {
        $oldImg = explode('/',$tool->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        untool($upload_path);
        $tool->delete();
        return $this->response->noContent();
    }

    public function toggle(ToolRequest $request, Tool $tool)
    {
        $status = $request->status;

        $maxOrder = Tool::max('order');
        $minOrder = Tool::min('order');

        if ($status == 'up' && $tool->order != $minOrder) {
            $up = Tool::where('order', '<', $tool->order)->orderBy('order', 'desc')->first();
            list($tool->order, $up->order) = [$up->order, $tool->order];
            $up->update();
        } elseif ($status == 'down' && $tool->order != $maxOrder) {
            $down = Tool::where('order', '>', $tool->order)->orderBy('order', 'asc')->first();
            list($tool->order, $down->order) = [$down->order, $tool->order];
            $down->update();
        } else {
            return;
        }
        $tool->update();

        return $this->response->item($tool, new ToolTransformer());
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
            $tools = $this->search($data)->orderBy('order')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态'];
            foreach ($tools as $tool) {
                $tool = $tool->simpleInfo();
                $export_data[] = $tool;
            }

            Excel::create('友情链接',function($excel) use($export_data){
                $excel->sheet('Tool',function($sheet) use($export_data){
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
                Tool::create($info);
            }
        });
    }
}
