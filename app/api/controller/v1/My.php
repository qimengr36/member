<?php

namespace app\api\controller\v1;

use app\api\controller\AuthController;
use think\facade\App;

class My extends AuthController
{
    /**
     * 个人中心
     * @param  App  $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
//        $this->services = $services;
    }

    /**
     * 菜单
     */
    public function menu()
    {

    }
    /**
     * 订单
     */
    public function order()
    {
    }

}