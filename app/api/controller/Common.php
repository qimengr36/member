<?php

namespace app\api\controller;

use app\services\CommonServices;
use think\facade\App;

class Common extends AuthController
{
    /**
     * 首页
     * @param  App  $app
     */
    public function __construct(App $app,CommonServices $services)
    {
        parent::__construct($app);
        $this->services = $services;
    }

    /**
     * 底部菜单
     */
    public function tabBar()
    {
        $result = $this->services->tabBar();
        return app('json')->success($result);
    }

    /**
     * 产品分类
     */
    public function cate()
    {
        $result = $this->services->cate();
        return app('json')->success($result);
    }

}