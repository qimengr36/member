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

Route::group(function () {
    $route = 'v1.';
    Route::group('song', function () use ($route) {
        $route .= 'song/';
        Route::get('search', $route.'search');
        Route::post('play', $route.'play');
    });
})->middleware([
    AllowOriginMiddleware::class
])->option(['mark' => 'v1', 'mark_name' => '业务']);


