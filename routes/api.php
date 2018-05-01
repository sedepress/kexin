<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

$api = app('Dingo\Api\Routing\Router');

$api->version('v1', [
    'namespace' => 'App\Http\Controllers\Api',
    'middleware' => ['serializer:array', 'bindings']
], function($api) {

    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ], function($api) {
        // 后台登录
        $api->post('authorizations', 'AuthorizationsController@store')
            ->name('api.authorizations.store');
        // 第三方登录
        $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
            ->name('api.socials.authorizations.store');
        // 刷新token
        $api->put('authorizations/current', 'AuthorizationsController@update')
            ->name('api.authorizations.update');
        // 删除token
        $api->delete('authorizations/current', 'AuthorizationsController@destroy')
            ->name('api.authorizations.destroy');
        // 分类信息
        $api->get('categories', 'DeclarationCategoryController@index')
            ->name('api.categories.index');
        $api->post('categories', 'DeclarationCategoryController@store')
            ->name('api.categories.store');
        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function($api) {
            $api->post('literatures', 'LiteratureController@store')
                ->name('api.literatures.store');
            $api->patch('literatures/{literature}', 'LiteratureController@update')
                ->name('api.literatures.update');
            $api->delete('literatures/{literature}', 'LiteratureController@destroy')
                ->name('api.literatures.destroy');
        });
    });
});