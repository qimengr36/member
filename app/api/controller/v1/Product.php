<?php

namespace app\api\controller\v1;

use app\api\controller\AuthController;
use think\facade\App;

class Product extends AuthController
{
    /**
     * 产品
     * @param  App  $app
     */
    public function __construct(App $app)
    {
        parent::__construct($app);
//        $this->services = $services;
    }

    /**
     * 详情页
     */
    public function info()
    {
    }

}