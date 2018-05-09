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
        //科学文献
        $api->group(['prefix' => 'literatures'], function ($api) {
            $api->get('/', 'LiteratureController@index');
            $api->get('/export', 'LiteratureController@export');
            $api->post('/import', 'LiteratureController@import');
            $api->post('/', 'LiteratureController@store');
            $api->post('/{literature}', 'LiteratureController@update');
            $api->delete('/{literature}', 'LiteratureController@destroy');
            $api->patch('/{literature}', 'LiteratureController@toggle');
        });
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
        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function($api) {
            //地区相关
            $api->group(['prefix' => 'areas'], function ($api) {
                $api->get('/{area}', 'AreaController@index');
                $api->get('/{area}/export', 'AreaController@export');
                $api->get('first', 'AreaController@first');
                $api->get('second/{area}', 'AreaController@second');
                $api->get('three/{area}', 'AreaController@three');
            });
            // 分类信息
            $api->group(['prefix' => 'declaration_categories'], function ($api) {
                $api->get('/', 'DeclarationCategoryController@index');
                $api->post('/', 'DeclarationCategoryController@store');
                $api->put('/{declaration_category}', 'DeclarationCategoryController@update');
                $api->delete('/{declaration_category}', 'DeclarationCategoryController@delete');
            });

            //知识产权
            $api->group(['prefix' => 'intellectuals'], function ($api) {
                $api->get('/', 'IntellectualController@index');
                $api->post('/', 'IntellectualController@store');
                $api->post('/{intellectual}', 'IntellectualController@update');
                $api->delete('/{intellectual}', 'IntellectualController@destroy');
                $api->patch('/{intellectual}', 'IntellectualController@toggle');
            });
            //检验检测

            //资讯中心
            $api->group(['prefix' => 'informations'], function ($api) {
                $api->get('/', 'InformationController@index');
                $api->post('/', 'InformationController@store');
                $api->post('/{information}', 'InformationController@update');
                $api->delete('/{information}', 'InformationController@destroy');
                $api->patch('/{information}', 'InformationController@toggle');
                $api->put('/{information}', 'InformationController@status');
            });
        });
    });
});