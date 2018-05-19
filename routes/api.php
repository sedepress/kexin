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
        //地区相关
        $api->group(['prefix' => 'areas'], function ($api) {
            $api->get('/', 'AreaController@index');
            $api->get('first/{area}', 'AreaController@first');
            $api->get('second/{area}', 'AreaController@second');
            $api->get('three/{area}', 'AreaController@three');
        });
        $api->group(['prefix' => 'website_categories'], function ($api) {
            $api->get('/', 'WebsiteCategoryController@index');
            $api->get('/lists', 'WebsiteCategoryController@lists');
            $api->get('/left', 'WebsiteCategoryController@left');
            $api->get('/right', 'WebsiteCategoryController@right');
            $api->post('/', 'WebsiteCategoryController@store');
            $api->put('/{website_category}', 'WebsiteCategoryController@update');
            $api->delete('/{website_category}', 'WebsiteCategoryController@delete');
        });
        //资讯中心
        $api->group(['prefix' => 'informations'], function ($api) {
            $api->get('/', 'InformationController@index');
            $api->post('/', 'InformationController@store');
            $api->post('/{information}', 'InformationController@update');
            $api->delete('/{information}', 'InformationController@destroy');
            $api->patch('/{information}', 'InformationController@toggle');
            $api->put('/{information}', 'InformationController@status');
        });
        //网站
        $api->group(['prefix' => 'websites'], function ($api) {
            $api->get('/', 'WebsiteController@index');
            $api->get('/export', 'WebsiteController@export');
            $api->post('/import', 'WebsiteController@import');
            $api->post('/', 'WebsiteController@store');
            $api->post('/{website}', 'WebsiteController@update');
            $api->delete('/{website}', 'WebsiteController@destroy');
            $api->patch('/{website}', 'WebsiteController@toggle');
        });
        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function($api) {
            // 刷新token
            $api->put('authorizations/current', 'AuthorizationsController@update')
                ->name('api.authorizations.update');
            // 删除token
            $api->delete('authorizations/current', 'AuthorizationsController@destroy')
                ->name('api.authorizations.destroy');
        });
    });
});