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
        //国家政府
        $api->group(['prefix' => 'governments'], function ($api) {
            $api->get('/', 'GovernmentController@index');
            $api->get('/export', 'GovernmentController@export');
            $api->post('/import', 'GovernmentController@import');
            $api->post('/', 'GovernmentController@store');
            $api->post('/{government}', 'GovernmentController@update');
            $api->delete('/{government}', 'GovernmentController@destroy');
            $api->patch('/{government}', 'GovernmentController@toggle');
        });
        //检测检验
        $api->group(['prefix' => 'inspections'], function ($api) {
            $api->get('/', 'InspectionController@index');
            $api->get('/export', 'InspectionController@export');
            $api->post('/import', 'InspectionController@import');
            $api->post('/', 'InspectionController@store');
            $api->post('/{inspection}', 'InspectionController@update');
            $api->delete('/{inspection}', 'InspectionController@destroy');
            $api->patch('/{inspection}', 'InspectionController@toggle');
        });
        //知识产权
        $api->group(['prefix' => 'intellectuals'], function ($api) {
            $api->get('/', 'IntellectualController@index');
            $api->get('/export', 'IntellectualController@export');
            $api->post('/import', 'IntellectualController@import');
            $api->post('/', 'IntellectualController@store');
            $api->post('/{intellectual}', 'IntellectualController@update');
            $api->delete('/{intellectual}', 'IntellectualController@destroy');
            $api->patch('/{intellectual}', 'IntellectualController@toggle');
        });
        //友情链接
        $api->group(['prefix' => 'links'], function ($api) {
            $api->get('/', 'LinkController@index');
            $api->get('/export', 'LinkController@export');
            $api->post('/import', 'LinkController@import');
            $api->post('/', 'LinkController@store');
            $api->post('/{link}', 'LinkController@update');
            $api->delete('/{link}', 'LinkController@destroy');
            $api->patch('/{link}', 'LinkController@toggle');
        });
        //邮箱
        $api->group(['prefix' => 'mails'], function ($api) {
            $api->get('/', 'MailController@index');
            $api->get('/export', 'MailController@export');
            $api->post('/import', 'MailController@import');
            $api->post('/', 'MailController@store');
            $api->post('/{mail}', 'MailController@update');
            $api->delete('/{mail}', 'MailController@destroy');
            $api->patch('/{mail}', 'MailController@toggle');
        });
        //其他
        $api->group(['prefix' => 'others'], function ($api) {
            $api->get('/', 'OtherController@index');
            $api->get('/export', 'OtherController@export');
            $api->post('/import', 'OtherController@import');
            $api->post('/', 'OtherController@store');
            $api->post('/{other}', 'OtherController@update');
            $api->delete('/{other}', 'OtherController@destroy');
            $api->patch('/{other}', 'OtherController@toggle');
        });
        //认证许可
        $api->group(['prefix' => 'permits'], function ($api) {
            $api->get('/', 'PermitController@index');
            $api->get('/export', 'PermitController@export');
            $api->post('/import', 'PermitController@import');
            $api->post('/', 'PermitController@store');
            $api->post('/{permit}', 'PermitController@update');
            $api->delete('/{permit}', 'PermitController@destroy');
            $api->patch('/{permit}', 'PermitController@toggle');
        });
        //服务
        $api->group(['prefix' => 'services'], function ($api) {
            $api->get('/', 'ServiceController@index');
            $api->get('/export', 'ServiceController@export');
            $api->post('/import', 'ServiceController@import');
            $api->post('/', 'ServiceController@store');
            $api->post('/{service}', 'ServiceController@update');
            $api->delete('/{service}', 'ServiceController@destroy');
            $api->patch('/{service}', 'ServiceController@toggle');
        });
        //工具中心
        $api->group(['prefix' => 'tools'], function ($api) {
            $api->get('/', 'ToolController@index');
            $api->get('/export', 'ToolController@export');
            $api->post('/import', 'ToolController@import');
            $api->post('/', 'ToolController@store');
            $api->post('/{tool}', 'ToolController@update');
            $api->delete('/{tool}', 'ToolController@destroy');
            $api->patch('/{tool}', 'ToolController@toggle');
        });
        //政府申报
        $api->group(['prefix' => 'declarations'], function ($api) {
            $api->get('/', 'DeclarationController@index');
            $api->get('/export', 'DeclarationController@export');
            $api->post('/import', 'DeclarationController@import');
            $api->post('/', 'DeclarationController@store');
            $api->post('/{declaration}', 'DeclarationController@update');
            $api->delete('/{declaration}', 'DeclarationController@destroy');
            $api->patch('/{declaration}', 'DeclarationController@toggle');
        });
        //标题
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->get('/', 'CategoryController@index');
            $api->patch('/{category}', 'CategoryController@update');
        });
        // 后台登录
        $api->post('authorizations', 'AuthorizationsController@store')
            ->name('api.authorizations.store');
        // 第三方登录
        $api->post('socials/{social_type}/authorizations', 'AuthorizationsController@socialStore')
            ->name('api.socials.authorizations.store');

        //地区相关
        $api->group(['prefix' => 'areas'], function ($api) {
            $api->get('/{area}', 'AreaController@index');
            $api->get('first/{area}', 'AreaController@first');
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
        $api->group(['prefix' => 'website_categories'], function ($api) {
            $api->get('/', 'WebsiteCategoryController@index');
            $api->get('/lists', 'WebsiteCategoryController@lists');
            $api->post('/', 'WebsiteCategoryController@store');
            $api->put('/{website_category}', 'WebsiteCategoryController@update');
            $api->delete('/{website_category}', 'WebsiteCategoryController@delete');
        });
        // 需要 token 验证的接口
        $api->group(['middleware' => 'api.auth'], function($api) {
            // 刷新token
            $api->put('authorizations/current', 'AuthorizationsController@update')
                ->name('api.authorizations.update');
            // 删除token
            $api->delete('authorizations/current', 'AuthorizationsController@destroy')
                ->name('api.authorizations.destroy');
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
        });
    });
});