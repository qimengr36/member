<?php

namespace app\services;

use app\dao\common\AppTabBarDao;
use app\dao\common\ProductCategoryDao;
use kernel\services\CacheService;

class CommonServices extends BaseServices
{

    public function tabBar()
    {
        $dao = app()->make(AppTabBarDao::class);
        return $dao->tabBarList();
    }

    public function cate()
    {
        $dao = app()->make(ProductCategoryDao::class);
        return CacheService::remember('CATEGORY', function () use ($dao) {
            return $dao->getCategory();
        });
    }
}