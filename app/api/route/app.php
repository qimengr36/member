<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;
use app\http\middleware\AllowOriginMiddleware;
use think\facade\Config;
use think\Response;

Route::group(function () {
    $route = 'Common/';
    Route::get('tab_bar', $route.'tabBar')->option(['real_name' => '底部菜单']);
    Route::get('cate', $route.'cate')->option(['real_name' => '产品分类']);
})->middleware([
    AllowOriginMiddleware::class
])->option(['mark' => 'common', 'mark_name' => '公共']);


/**
 * miss 路由
 */
Route::miss(function () {
    if (app()->request->isOptions()) {
        $header = Config::get('cookie.header');
        $header['Access-Control-Allow-Origin'] = app()->request->header('origin');
        return Response::create('ok')->code(200)->header($header);
    } else {
        return Response::create()->code(404);
    }
});
