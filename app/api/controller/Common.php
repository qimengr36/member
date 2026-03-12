<?php

namespace app\api\controller;

use think\facade\App;

class Common extends AuthController
{
    /**
     * 首页
     * @param  App  $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
//        $this->services = $services;
    }

    /**
     * 底部菜单
     */
    public function tabBar()
    {
    }

    /**
     * 分类
     */
    public function cate()
    {
    }

    /**
     * 个人中心
     */
    public function my()
    {
    }
}