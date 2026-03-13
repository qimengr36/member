<?php

namespace app\services;

use app\dao\common\AppTabBarDao;

class CommonServices extends BaseServices
{
    public function __construct()
    {
    }

    public function tabBar()
    {
        $dao = app()->make(AppTabBarDao::class);
        return $dao->tabBarList();
    }

    public function cate()
    {
    }
}