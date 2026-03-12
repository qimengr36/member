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
    $route = 'Login/';
    Route::post('login', $route.'login')->option(['real_name' => '用户名密码登录']);
    Route::get('login/info', $route.'info')->option(['real_name' => '登录信息']);
    Route::get('get_lang', $route.'getLang')->option(['real_name' => '多语言']);
})->middleware([
    AllowOriginMiddleware::class
])->option(['mark' => 'login', 'mark_name' => '登录相关']);


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
