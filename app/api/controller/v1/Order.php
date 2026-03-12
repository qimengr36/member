<?php

namespace app\api\controller\v1;

use app\api\controller\AuthController;
use think\facade\App;

class Order extends AuthController
{
    /**
     * 订单
     * @param  App  $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
//        $this->services = $services;
    }

    /**
     * 创建订单
     */
    public function create()
    {
    }

}