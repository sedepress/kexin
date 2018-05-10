<?php

namespace App\Http\Controllers\Api;

use App\Models\Mail;
use App\Transformers\MailTransformer;
use App\Http\Requests\Api\MailRequest;
use App\Handlers\ImageUploadHandler;
use Illuminate\Http\Request;
use Excel,Validator,Response;

class MailController extends Controller
{
    public function index(MailRequest $request)
    {
        $mails = $this->search($request->all())->orderBy('order')->paginate(10);
        return $this->response->paginator($mails, new MailTransformer());
    }

    protected function search($data)
    {
        $mails = Mail::where(function($query) use ($data){
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

        return $mails;
    }

    public function store(MailRequest $request, ImageUploadHandler $uploader, Mail $mail)
    {
        $mail->fill($request->all());

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Mails', 2018);
            if ($result) {
                $mail->image_url = $result['path'];
            }
        }

        $mail->save();

        return $this->response->item($mail, new MailTransformer())
            ->setStatusCode(201);
    }

    public function update(MailRequest $request, ImageUploadHandler $uploader, Mail $mail)
    {
        $data = $request->all();

        if ($request->image_url) {
            $result = $uploader->save($request->image_url, 'Mails', $mail->id);
            if ($result) {
                $data['image_url'] = $result['path'];
            }
        }

        //切割旧图地址获取到图片需要的信息
        $oldImg = explode('/',$mail->image_url);

        //旧图片名称
        $oldImgData = $oldImg[8];

        //获取旧图片的绝对路径
        $oldImgPath = $result['upload_path'].'/'.$oldImgData;

        $mail->update($data);

        if($mail->update($data)&&$oldImgPath!=null){
            unmail($oldImgPath);
        }
        return $this->response->item($mail, new MailTransformer());
    }

    public function destroy(Mail $mail)
    {
        $oldImg = explode('/',$mail->image_url);
        unset($oldImg[0], $oldImg[1], $oldImg[2]);
        $upload_path = public_path() . '/' . implode('/', $oldImg);
        unmail($upload_path);
        $mail->delete();
        return $this->response->noContent();
    }

    public function toggle(MailRequest $request, Mail $mail)
    {
        $status = $request->status;

        $maxOrder = Mail::max('order');
        $minOrder = Mail::min('order');

        if ($status == 'up' && $mail->order != $minOrder) {
            $up = Mail::where('order', '<', $mail->order)->orderBy('order', 'desc')->first();
            list($mail->order, $up->order) = [$up->order, $mail->order];
            $up->update();
        } elseif ($status == 'down' && $mail->order != $maxOrder) {
            $down = Mail::where('order', '>', $mail->order)->orderBy('order', 'asc')->first();
            list($mail->order, $down->order) = [$down->order, $mail->order];
            $down->update();
        } else {
            return;
        }
        $mail->update();

        return $this->response->item($mail, new MailTransformer());
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
            $mails = $this->search($data)->orderBy('order')->get();
            $export_data[] = ['编号ID', '名称', '链接', '图标', '链接状态'];
            foreach ($mails as $mail) {
                $mail = $mail->simpleInfo();
                $export_data[] = $mail;
            }

            Excel::create('友情链接',function($excel) use($export_data){
                $excel->sheet('Mail',function($sheet) use($export_data){
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
                Mail::create($info);
            }
        });
    }
}
