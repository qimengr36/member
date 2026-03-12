<?php

return [
    //多语言初始化
    \think\middleware\LoadLangPack::class,
    //初始化基础中间件
    \app\http\middleware\BaseMiddleware::class,
    // 多语言支持
    \think\middleware\LoadLangPack::class,
];
