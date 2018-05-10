<?php

namespace App\Http\Controllers\Api;

use App\Models\Other;
use App\Models\Area;
use App\Transformers\OtherTransformer;
use App\Http\Requests\Api\OtherRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class OtherController extends Controller
{
    public function index(OtherRequest $request)
    {
        $Others = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($Others, new OtherTransformer());
    }

    protected function search($data)
    {
        $Others = Other::where(function($query) use ($data){
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

        return $Others;
    }

    public function store(OtherRequest $request, ImageUploadHandler $uploader, Other $Other)
    {
        $Other->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Others', 2018);
            if ($result) {
                $Other->image_url = $result['path'];
            }
        }

        $Other->save();

        return $this->response->item($Other, new OtherTransformer())
            ->setStatusCode(201);
    }

    public function update(OtherRequest $request, ImageUploadHandler $uploader, Other $Other)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Others', $Other->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Other->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Other->update($data);

        if($Other->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Other, new OtherTransformer());
    }

    public function destroy(Other $Other)
    {
        $oldImg = explode('/',$Other->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Other->delete();
        return $this->response->noContent();
    }

    public function toggle(OtherRequest $request, Other $Other)
    {
        $status = $request->status;

        $maxOrder = Other::max('order');
        $minOrder = Other::min('order');

        if ($status == 'up' && $Other->order != $minOrder) {
            $up = Other::where('order', '<', $Other->order)->orderBy('order', 'desc')->first();
            list($Other->order, $up->order) = [$up->order, $Other->order];
            $up->update();
        } elseif ($status == 'down' && $Other->order != $maxOrder) {
            $down = Other::where('order', '>', $Other->order)->orderBy('order', 'asc')->first();
            list($Other->order, $down->order) = [$down->order, $Other->order];
            $down->update();
        } else {
            return;
        }
        $Other->update();

        return $this->response->item($Other, new OtherTransformer());
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
            $Others = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($Others as $Other) {
                $Other = $Other->simpleInfo();
                $export_data[] = $Other;
            }

            Excel::create('其他',function($excel) use($export_data){
                $excel->sheet('Other',function($sheet) use($export_data){
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
                Other::create($info);
            }
        });
    }
}
