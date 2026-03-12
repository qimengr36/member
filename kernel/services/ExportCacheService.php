<?php

namespace kernel\services;

use kernel\basic\BaseCache;

class ExportCacheService extends BaseCache
{
    protected $uniqId = '';

    public function __construct()
    {
        parent::__construct();
        //以微秒计的当前时间，生成一个唯一的 ID,以tagName为前缀
        $this->uniqId = md5(uniqid($this->tagName, true) . mt_rand());
    }

    /**
     * @notes 获取excel缓存目录
     * @return string
     */
    public function getSrc()
    {
        $dir = app()->getRootPath() . 'runtime/file/export/' . date('Y-m-d') . '/' . $this->uniqId . '/';
        if (!file_exists($dir)) mkdir($dir, 0775, true);
        return $dir;
    }


    /**
     * @notes 设置文件路径缓存地址
     * @param $fileName
     * @return string
     */
    public function setFile($src, $fileName)
    {
        $key = md5($src . $fileName) . time();
        $this->set($key, ['src' => $src, 'name' => $fileName], 300);
        return $key;
    }

    /**
     * @notes 获取文件缓存的路径
     * @param $key
     * @return mixed
     */
    public function getFile($key)
    {
        return $this->get($key);
    }
}