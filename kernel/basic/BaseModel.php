<?php


namespace kernel\basic;

use kernel\traits\ModelTrait;
use think\db\Query;
use think\Model;

/**
 * Class BaseModel
 * @package pmleb\basic
 * @mixin ModelTrait
 * @mixin Query
 */
class BaseModel extends Model
{
    public function getSiteUrl()
    {
        return rtrim_url(sys_config('site_url')) . '/';
    }
}
