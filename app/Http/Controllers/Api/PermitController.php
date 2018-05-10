<?php

namespace App\Http\Controllers\Api;

use App\Models\Permit;
use App\Models\Area;
use App\Transformers\PermitTransformer;
use App\Http\Requests\Api\PermitRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class PermitController extends Controller
{
    public function index(PermitRequest $request)
    {
        $Permits = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($Permits, new PermitTransformer());
    }

    protected function search($data)
    {
        $Permits = Permit::where(function($query) use ($data){
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

        return $Permits;
    }

    public function store(PermitRequest $request, ImageUploadHandler $uploader, Permit $Permit)
    {
        $Permit->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Permits', 2018);
            if ($result) {
                $Permit->image_url = $result['path'];
            }
        }

        $Permit->save();

        return $this->response->item($Permit, new PermitTransformer())
            ->setStatusCode(201);
    }

    public function update(PermitRequest $request, ImageUploadHandler $uploader, Permit $Permit)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Permits', $Permit->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Permit->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Permit->update($data);

        if($Permit->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Permit, new PermitTransformer());
    }

    public function destroy(Permit $Permit)
    {
        $oldImg = explode('/',$Permit->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Permit->delete();
        return $this->response->noContent();
    }

    public function toggle(PermitRequest $request, Permit $Permit)
    {
        $status = $request->status;

        $maxOrder = Permit::max('order');
        $minOrder = Permit::min('order');

        if ($status == 'up' && $Permit->order != $minOrder) {
            $up = Permit::where('order', '<', $Permit->order)->orderBy('order', 'desc')->first();
            list($Permit->order, $up->order) = [$up->order, $Permit->order];
            $up->update();
        } elseif ($status == 'down' && $Permit->order != $maxOrder) {
            $down = Permit::where('order', '>', $Permit->order)->orderBy('order', 'asc')->first();
            list($Permit->order, $down->order) = [$down->order, $Permit->order];
            $down->update();
        } else {
            return;
        }
        $Permit->update();

        return $this->response->item($Permit, new PermitTransformer());
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
            $Permits = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($Permits as $Permit) {
                $Permit = $Permit->simpleInfo();
                $export_data[] = $Permit;
            }

            Excel::create('认证许可',function($excel) use($export_data){
                $excel->sheet('Permit',function($sheet) use($export_data){
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
                Permit::create($info);
            }
        });
    }
}
