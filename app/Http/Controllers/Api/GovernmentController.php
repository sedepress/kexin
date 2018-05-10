<?php

namespace App\Http\Controllers\Api;

use App\Models\Government;
use App\Models\Area;
use App\Transformers\GovernmentTransformer;
use App\Http\Requests\Api\GovernmentRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class GovernmentController extends Controller
{
    public function index(GovernmentRequest $request)
    {
        $governments = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($governments, new GovernmentTransformer());
    }

    protected function search($data)
    {
        $governments = Government::where(function($query) use ($data){
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

        return $governments;
    }

    public function store(GovernmentRequest $request, ImageUploadHandler $uploader, Government $Government)
    {
        $Government->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Governments', 2018);
            if ($result) {
                $Government->image_url = $result['path'];
            }
        }

        $Government->save();

        return $this->response->item($Government, new GovernmentTransformer())
            ->setStatusCode(201);
    }

    public function update(GovernmentRequest $request, ImageUploadHandler $uploader, Government $Government)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Governments', $Government->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$Government->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $Government->update($data);

        if($Government->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($Government, new GovernmentTransformer());
    }

    public function destroy(Government $Government)
    {
        $oldImg = explode('/',$Government->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $Government->delete();
        return $this->response->noContent();
    }

    public function toggle(GovernmentRequest $request, Government $Government)
    {
        $status = $request->status;

        $maxOrder = Government::max('order');
        $minOrder = Government::min('order');

        if ($status == 'up' && $Government->order != $minOrder) {
            $up = Government::where('order', '<', $Government->order)->orderBy('order', 'desc')->first();
            list($Government->order, $up->order) = [$up->order, $Government->order];
            $up->update();
        } elseif ($status == 'down' && $Government->order != $maxOrder) {
            $down = Government::where('order', '>', $Government->order)->orderBy('order', 'asc')->first();
            list($Government->order, $down->order) = [$down->order, $Government->order];
            $down->update();
        } else {
            return;
        }
        $Government->update();

        return $this->response->item($Government, new GovernmentTransformer());
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
            $governments = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($governments as $Government) {
                $Government = $Government->simpleInfo();
                $export_data[] = $Government;
            }

            Excel::create('国家政府',function($excel) use($export_data){
                $excel->sheet('Government',function($sheet) use($export_data){
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
                Government::create($info);
            }
        });
    }
}
