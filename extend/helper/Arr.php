<?php

namespace helper;

class Arr
{
    /**
     * 移除空数组值
     *
     * @param array $para
     * @return array
     */
    public static function paraFilter($para)
    {
        $paraFilter = [];
        foreach ($para as $key => $val) {
            if ($val === '' || $val === null) {
                continue;
            }
            if (!is_array($para[$key])) {
                $para[$key] = is_bool($para[$key]) ? $para[$key] : trim($para[$key]);
            }

            $paraFilter[$key] = $para[$key];
        }
        return $paraFilter;
    }

    /**
     * 数组排序
     *
     * @param array $param
     * @return array
     */
    public static function arraySort($param)
    {
        ksort($param);
        reset($param);
        return $param;
    }

    /**
     * 删除一维数组 指定key的值
     *
     * @param array $inputs
     * @param string $keys
     * @return array
     */
    public static function removeKeys($inputs,$keys)
    {
        // 如果不是数组，需要进行转换
        if (!is_array($keys)) {
            $keys = explode(',', $keys);
        }

        if (empty($keys) || !is_array($keys)) {
            return $inputs;
        }

        $flag = true;
        foreach ($keys as $key) {
            if (array_key_exists($key, $inputs)) {
                if (is_int($key)) {
                    $flag = false;
                }
                unset($inputs[$key]);
            }
        }

        if (!$flag) {
            $inputs = array_values($inputs);
        }
        return $inputs;
    }

    /**
     * 数组字典排序
     *
     * @param array $param
     */
    public static function arrSort($param)
    {
        ksort($param);
        reset($param);
        return $param;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     *
     * @param array $params
     * @return string
     */
    public static function createLinkString($params)
    {
        if (!is_array($params)) {
            throw new \Exception('必须传入数组参数');
        }
        reset($params);
        $buff = '';
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                continue;
            }

            if ($key != "sign" && $val != "" && !is_array($val)) {
                $buff .= $key . "=" . urldecode($val) . "&";
            }
        }
        $buff = trim($buff, "&");
        return $buff;
    }
}