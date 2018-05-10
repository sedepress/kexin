<?php

namespace App\Http\Controllers\Api;

use App\Models\Inspection;
use App\Models\Area;
use App\Transformers\InspectionTransformer;
use App\Http\Requests\Api\InspectionRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class InspectionController extends Controller
{
    public function index(InspectionRequest $request)
    {
        $Inspections = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($Inspections, new InspectionTransformer());
    }

    protected function search($data)
    {
        $Inspections = Inspection::where(function($query) use ($data){
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

        return $Inspections;
    }

    public function store(InspectionRequest $request, ImageUploadHandler $uploader, Inspection $Inspection)
    {
        $Inspection->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Inspections', 2018);
            if ($result) {
                $Inspection->image_url = $result['path'];
            }
        }

        $Inspection->save();

        return $this->response->item($Inspection, new InspectionTransformer())
            ->setStatusCode(201);
    }

    public function update(InspectionRequest $request, ImageUploadHandler $uploader, Inspection $Inspection)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Inspections', $Inspection->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Inspection->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Inspection->update($data);

        if($Inspection->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Inspection, new InspectionTransformer());
    }

    public function destroy(Inspection $Inspection)
    {
        $oldImg = explode('/',$Inspection->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Inspection->delete();
        return $this->response->noContent();
    }

    public function toggle(InspectionRequest $request, Inspection $Inspection)
    {
        $status = $request->status;

        $maxOrder = Inspection::max('order');
        $minOrder = Inspection::min('order');

        if ($status == 'up' && $Inspection->order != $minOrder) {
            $up = Inspection::where('order', '<', $Inspection->order)->orderBy('order', 'desc')->first();
            list($Inspection->order, $up->order) = [$up->order, $Inspection->order];
            $up->update();
        } elseif ($status == 'down' && $Inspection->order != $maxOrder) {
            $down = Inspection::where('order', '>', $Inspection->order)->orderBy('order', 'asc')->first();
            list($Inspection->order, $down->order) = [$down->order, $Inspection->order];
            $down->update();
        } else {
            return;
        }
        $Inspection->update();

        return $this->response->item($Inspection, new InspectionTransformer());
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
            $Inspections = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($Inspections as $Inspection) {
                $Inspection = $Inspection->simpleInfo();
                $export_data[] = $Inspection;
            }

            Excel::create('检验检测',function($excel) use($export_data){
                $excel->sheet('Inspection',function($sheet) use($export_data){
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
                Inspection::create($info);
            }
        });
    }
}
