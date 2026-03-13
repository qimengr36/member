<?php

namespace app\dao\common;

use app\dao\BaseDao;
use app\model\common\AppTabBar;

class AppTabBarDao extends BaseDao
{
    /**
     * @return string
     */
    public function setModel(): string
    {
        return AppTabBar::class;
    }

    public function tabBarList()
    {
        $where = [
            ['is_show', '=', 1],
        ];
        return $this->getModel()->where($where)
            ->withoutField(['id', 'sort', 'is_show', 'create_time'])
            ->order(['sort' => 'desc'])
            ->select()->toArray();
    }
}