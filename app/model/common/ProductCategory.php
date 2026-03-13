<?php

namespace app\model\common;

use kernel\basic\BaseModel;
use kernel\traits\ModelTrait;

class ProductCategory extends BaseModel
{
    use ModelTrait;

    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';
    protected $name = 'product_category';

    /**
     * 添加时间获取器
     * @param $value
     * @return false|string
     */
    protected function getAddTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }

    /**
     * 获取子集分类查询条件
     * @return \think\model\relation\HasMany
     */
    public function children()
    {
        return $this->hasMany(self::class, 'pid', 'id')->where('is_show', 1)->order('sort DESC,id DESC');
    }

    /**
     * 分类是否显示搜索器
     * @param $value
     * @param $data
     */
    public function searchIsShowAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('is_show', $value);
        }
    }

    /**
     * 分类是否显示搜索器
     * @param $value
     * @param $data
     */
    public function searchPidAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('pid', $value);
        }
    }

    /**
     * 分类是否显示搜索器
     * @param $value
     * @param $data
     */
    public function searchCateNameAttr($query, $value, $data)
    {
        if ($value !== '') {
            $query->where('cate_name', 'like', '%'.$value.'%');
        }
    }

    /**
     * 分类是否显示搜索器
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
        if ($value) {
            $query->whereIn('id', is_array($value) ? $value : (string)$value);
        }
    }
}