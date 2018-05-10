<?php

namespace App\Http\Controllers\Api;

use App\Models\Declaration;
use App\Models\Area;
use App\Transformers\DeclarationTransformer;
use App\Http\Requests\Api\DeclarationRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class DeclarationController extends Controller
{
    public function index(DeclarationRequest $request)
    {
        $declarations = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($declarations, new DeclarationTransformer());
    }

    protected function search($data)
    {
        $declarations = Declaration::where(function($query) use ($data){
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
                if(isset($data['declaration_category_id'])){
                    $query->where('declaration_category_id', $data['declaration_category_id']);
                }
            })
            ->where(function($query) use ($data){
                if(isset($data['id'])){
                    $query->whereId($data['id']);
                }
            });

        return $declarations;
    }

    public function store(DeclarationRequest $request, ImageUploadHandler $uploader, Declaration $declaration)
    {
        $declaration->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'declarations', 2018);
            if ($result) {
                $declaration->image_url = $result['path'];
            }
        }

        $declaration->save();

        return $this->response->item($declaration, new DeclarationTransformer())
            ->setStatusCode(201);
    }

    public function update(DeclarationRequest $request, ImageUploadHandler $uploader, Declaration $declaration)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'declarations', $declaration->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$declaration->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $declaration->update($data);

        if($declaration->update($data)&&$oldImgPath!=null){
            unlink($oldImgPath);
        }
        return $this->response->item($declaration, new DeclarationTransformer());
    }

    public function destroy(Declaration $declaration)
    {
        $oldImg = explode('/',$declaration->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unlink($upload_path);
        $declaration->delete();
        return $this->response->noContent();
    }

    public function toggle(DeclarationRequest $request, Declaration $declaration)
    {
        $status = $request->status;

        $maxOrder = Declaration::max('order');
        $minOrder = Declaration::min('order');

        if ($status == 'up' && $declaration->order != $minOrder) {
            $up = Declaration::where('order', '<', $declaration->order)->orderBy('order', 'desc')->first();
            list($declaration->order, $up->order) = [$up->order, $declaration->order];
            $up->update();
        } elseif ($status == 'down' && $declaration->order != $maxOrder) {
            $down = Declaration::where('order', '>', $declaration->order)->orderBy('order', 'asc')->first();
            list($declaration->order, $down->order) = [$down->order, $declaration->order];
            $down->update();
        } else {
            return;
        }
        $declaration->update();

        return $this->response->item($declaration, new DeclarationTransformer());
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
            $declarations = $this->search($data)->orderBy('order')->with(['area', 'category'])->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态', '区域属性', '类别'];
            foreach ($declarations as $declaration) {
                $declaration = $declaration->simpleInfo();
                $export_data[] = $declaration;
            }

            Excel::create('政府申报',function($excel) use($export_data){
                $excel->sheet('declaration',function($sheet) use($export_data){
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
                Declaration::create($info);
            }
        });
    }
}
