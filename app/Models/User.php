<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth,DB;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'weixin_openid', 'weixin_unionid', 'avatar', 'email', 'password'
    ];

    public static function boot()
    {
        parent::boot();
        self::created(function ($model) {
            DB::table('favorites')->insert([
                ['user_id' => $model->id, 'website' => 'www.baidu.com', 'name' => '百度', 'icon' => 'http://favicon.byi.pw/?url=www.baidu.com'],
                ['user_id' => $model->id, 'website' => 'www.sina.com.cn', 'name' => '新浪', 'icon' => 'http://favicon.byi.pw/?url=www.sina.com.cn'],
                ['user_id' => $model->id, 'website' => 'www.sohu.com', 'name' => '搜狐', 'icon' => 'http://favicon.byi.pw/?url=www.sohu.com'],
                ['user_id' => $model->id, 'website' => 'www.jd.com', 'name' => '京东', 'icon' => 'http://favicon.byi.pw/?url=www.jd.com'],
                ['user_id' => $model->id, 'website' => 'www.qq.com', 'name' => '腾讯', 'icon' => 'http://favicon.byi.pw/?url=www.qq.com'],
                ['user_id' => $model->id, 'website' => 'www.suning.com', 'name' => '苏宁易购', 'icon' => 'http://favicon.byi.pw/?url=www.suning.com'],
                ['user_id' => $model->id, 'website' => 'www.tmall.com', 'name' => '天猫', 'icon' => 'http://favicon.byi.pw/?url=www.tmall.com'],
                ['user_id' => $model->id, 'website' => 'www.ifeng.com', 'name' => '凤凰网', 'icon' => 'http://favicon.byi.pw/?url=www.ifeng.com'],
                ['user_id' => $model->id, 'website' => 'www.taobao.com', 'name' => '淘宝网', 'icon' => 'http://favicon.byi.pw/?url=www.taobao.com'],
                ['user_id' => $model->id, 'website' => 'www.baidu.com', 'name' => '百度', 'icon' => 'http://favicon.byi.pw/?url=www.baidu.com'],
                ['user_id' => $model->id, 'website' => 'www.qunar.com', 'name' => '去哪儿', 'icon' => 'http://favicon.byi.pw/?url=www.qunar.com'],
                ['user_id' => $model->id, 'website' => 'www.163.com', 'name' => '网易', 'icon' => 'http://favicon.byi.pw/?url=www.163.com'],
            ]);
        });
    }

    // Rest omitted for brevity

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function websites()
    {
        return $this->hasMany(Favorite::class);
    }
}
