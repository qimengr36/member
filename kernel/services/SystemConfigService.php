<?php


namespace kernel\services;

use app\services\system\config\SystemConfigServices;
use kernel\utils\Arr;

/** 获取系统配置服务类
 * Class SystemConfigService
 * @package service
 */
class SystemConfigService
{
    const CACHE_SYSTEM = 'system_config';

    /**
     * 获取单个配置效率更高
     * @param string $key
     * @param $default
     * @param bool $isCaChe 是否获取缓存配置
     * @return bool|mixed|string
     */
    public static function get(string $key, $default = '', bool $isCaChe = false)
    {
        /** @var SystemConfigServices $service */
        $service = app()->make(SystemConfigServices::class);

        $callable = function () use ($service, $key) {
            return $service->getConfigValue($key);
        };

        try {
            return $callable();
//            if ($isCaChe) {
//                return $callable();
//            }
//            return CacheService::remember($key, $callable);
        } catch (\Throwable $e) {
            return $default;
        }
    }

    /**
     * 获取多个配置
     * @param array $keys 示例 [['appid','1'],'appkey']
     * @param bool $isCaChe 是否获取缓存配置
     * @return array
     */
    public static function more(array $keys, bool $isCaChe = false)
    {
        /** @var SystemConfigServices $service */
        $service = app()->make(SystemConfigServices::class);

        $callable = function () use ($service, $keys) {
            return Arr::getDefaultValue($keys, $service->getConfigAll($keys));
        };
        try {
            if ($isCaChe)
                return $callable();

            return CacheService::remember(md5(implode(',', $keys)), $callable);
        } catch (\Throwable $e) {
            return Arr::getDefaultValue($keys);
        }
    }

}
