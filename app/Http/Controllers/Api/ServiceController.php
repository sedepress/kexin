<?php

namespace App\Http\Controllers\Api;

use App\Models\Service;
use App\Models\Area;
use App\Transformers\ServiceTransformer;
use App\Http\Requests\Api\ServiceRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class ServiceController extends Controller
{
    public function index(ServiceRequest $request)
    {
        $Services = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($Services, new ServiceTransformer());
    }

    protected function search($data)
    {
        $Services = Service::where(function($query) use ($data){
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

        return $Services;
    }

    public function store(ServiceRequest $request, ImageUploadHandler $uploader, Service $Service)
    {
        $Service->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Services', 2018);
            if ($result) {
                $Service->image_url = $result['path'];
            }
        }

        $Service->save();

        return $this->response->item($Service, new ServiceTransformer())
            ->setStatusCode(201);
    }

    public function update(ServiceRequest $request, ImageUploadHandler $uploader, Service $Service)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Services', $Service->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Service->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Service->update($data);

        if($Service->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Service, new ServiceTransformer());
    }

    public function destroy(Service $Service)
    {
        $oldImg = explode('/',$Service->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Service->delete();
        return $this->response->noContent();
    }

    public function toggle(ServiceRequest $request, Service $Service)
    {
        $status = $request->status;

        $maxOrder = Service::max('order');
        $minOrder = Service::min('order');

        if ($status == 'up' && $Service->order != $minOrder) {
            $up = Service::where('order', '<', $Service->order)->orderBy('order', 'desc')->first();
            list($Service->order, $up->order) = [$up->order, $Service->order];
            $up->update();
        } elseif ($status == 'down' && $Service->order != $maxOrder) {
            $down = Service::where('order', '>', $Service->order)->orderBy('order', 'asc')->first();
            list($Service->order, $down->order) = [$down->order, $Service->order];
            $down->update();
        } else {
            return;
        }
        $Service->update();

        return $this->response->item($Service, new ServiceTransformer());
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
            $Services = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($Services as $Service) {
                $Service = $Service->simpleInfo();
                $export_data[] = $Service;
            }

            Excel::create('服务',function($excel) use($export_data){
                $excel->sheet('Service',function($sheet) use($export_data){
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
                Service::create($info);
            }
        });
    }
}
