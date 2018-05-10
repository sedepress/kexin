<?php

namespace App\Http\Controllers\Api;

use App\Models\Intellectual;
use App\Models\Area;
use App\Transformers\IntellectualTransformer;
use App\Http\Requests\Api\IntellectualRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class IntellectualController extends Controller
{
    public function index(IntellectualRequest $request)
    {
        $Intellectuals = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($Intellectuals, new IntellectualTransformer());
    }

    protected function search($data)
    {
        $Intellectuals = Intellectual::where(function($query) use ($data){
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

        return $Intellectuals;
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

        $Intellectual->save();

        return $this->response->item($Intellectual, new IntellectualTransformer())
            ->setStatusCode(201);
    }

    public function update(IntellectualRequest $request, ImageUploadHandler $uploader, Intellectual $Intellectual)
    {
        $data = $request->all();

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

    protected function export(Request $request)
    {
        $data = array_filter($request->only(['area_id', 'name', 'status']));

        $validator = Validator::make($data, [
            'area_id' => 'exists:areas,id',
            'name' => 'string',
            'status' => 'in:1,0'
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return Response::json($errors);
        }else{
            $Intellectuals = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($Intellectuals as $Intellectual) {
                $Intellectual = $Intellectual->simpleInfo();
                $export_data[] = $Intellectual;
            }

            Excel::create('知识产权',function($excel) use($export_data){
                $excel->sheet('Intellectual',function($sheet) use($export_data){
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
                Intellectual::create($info);
            }
        });
    }
}