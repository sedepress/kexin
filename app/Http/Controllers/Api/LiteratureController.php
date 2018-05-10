<?php

namespace App\Http\Controllers\Api;

use App\Models\Literature;
use App\Models\Area;
use App\Transformers\LiteratureTransformer;
use App\Http\Requests\Api\LiteratureRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class LiteratureController extends Controller
{
    public function index(LiteratureRequest $request)
    {
        $literatures = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($literatures, new LiteratureTransformer());
    }

    protected function search($data)
    {
        $literatures = Literature::where(function($query) use ($data){
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

        return $literatures;
    }

    public function store(LiteratureRequest $request, ImageUploadHandler $uploader, Literature $literature)
    {
        $literature->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'literatures', 2018);
            if ($result) {
                $literature->image_url = $result['path'];
            }
        }

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

    public function toggle(LiteratureRequest $request, Literature $literature)
    {
        $status = $request->status;

        $maxOrder = Literature::max('order');
        $minOrder = Literature::min('order');

        if ($status == 'up' && $literature->order != $minOrder) {
            $up = Literature::where('order', '<', $literature->order)->orderBy('order', 'desc')->first();
            list($literature->order, $up->order) = [$up->order, $literature->order];
            $up->update();
        } elseif ($status == 'down' && $literature->order != $maxOrder) {
            $down = Literature::where('order', '>', $literature->order)->orderBy('order', 'asc')->first();
            list($literature->order, $down->order) = [$down->order, $literature->order];
            $down->update();
        } else {
            return;
        }
        $literature->update();

        return $this->response->item($literature, new LiteratureTransformer());
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
            $literatures = $this->search($data)->orderBy('order')->with('area')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性'];
            foreach ($literatures as $literature) {
                $literature = $literature->simpleInfo();
                $export_data[] = $literature;
            }

            Excel::create('科学文献',function($excel) use($export_data){
                $excel->sheet('literature',function($sheet) use($export_data){
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
                Literature::create($info);
            }
        });
    }
}
