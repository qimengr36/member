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
use think\Response;

Route::group(function () {
    //下载文件
    Route::get('download', '\app\common\controller\PublicController@download')->option(['real_name' => '下载文件']);
})->middleware(AllowOriginMiddleware::class)->option(['mark' => 'login', 'mark_name' => '开放接口相关']);

Route::miss(function () {
    return Response::create()->code(404);
//    $appRequest = request()->pathinfo();
//    if ($appRequest === null) {
//        $appName = '';
//    } else {
//        $appRequest = str_replace('//', '/', $appRequest);
//        $appName = explode('/', $appRequest)[0] ?? '';
//    }
////    return view(app()->getRootPath() . 'public' . DS . $appName . DS . 'index.html');
//    switch (strtolower($appName)) {
//        case 'admin':
//            return view(app()->getRootPath() . 'public' . DS . 'admin' . DS . 'index.html');
//        default:
//            return view(app()->getRootPath() . 'public' . DS . 'b2b' . DS . 'index.html');
//    }
});