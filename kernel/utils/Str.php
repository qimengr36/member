<?php


namespace kernel\utils;

/**
 * 字符串操作帮助类
 * Class Str
 * @package pmleb\utils
 */
class Str
{
    /**
     * 生成随机字串 默认16位
     *
     * @param integer $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * 判断字串是否为JSON
     *
     * @param string $str
     * @return boolean
     */
    public static function isJson(string $str): bool
    {
        json_decode($str);
        if (json_last_error() == JSON_ERROR_NONE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 判断字串是否为Xml
     *
     * @param string $str
     * @return boolean
     */
    public static function isXml(string $str): bool
    {
        $xml = xml_parser_create();
        if (xml_parse($xml, $str, true)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * JSON To Array
     *
     * @param string $json
     * @return mixed
     */
    public static function json2arr(string $json)
    {
        if (self::check_empty($json)) {
            //记录响应结果
            return json_decode($json, true);
        } else {
            return false;
        }
    }

    /**
     * Array To JSON
     *
     * @param array $arr
     * @return bool|string
     */
    public static function arr2json(array $arr)
    {
        if (is_array($arr)) {
            $params = array();
            //遍历并剔除空值
            foreach ($arr as $k => $v) {
                if (self::check_empty($v)) {
                    $params[$k] = $v;
                }
            }
            //arr to json
            return json_encode($params, JSON_UNESCAPED_UNICODE);
        } else {
            return false;
        }
    }

    /**
     * 非空校验
     *
     * @param mixed $value
     * @return bool
     */
    protected static function check_empty($value): bool
    {
        if (is_array($value)) {
            return true;
        } else if (!empty($value)) {
            return true;
        } else if ($value !== null) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param array|string $needles
     */
    public static function endsWith(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param array|string $needles
     */
    public static function startsWith(string $haystack, $needles): bool
    {
        foreach ((array)$needles as $needle) {
            if ('' !== $needle && substr($haystack, 0, strlen($needle)) === (string)$needle) {
                return true;
            }
        }
        return false;
    }


    /**
     * 获取公私钥 字符
     *
     * @param string $key
     * @param boolean $publicKey
     * @return string
     */
    public static function getPublicOrPrivateCert(string $key, bool $publicKey = false): string
    {
        if ($publicKey) {
            return Str::endsWith($key, ['.cer', '.crt', '.pem']) ? file_get_contents($key) : $key;
        }
        // 读取证书
        $res = @file_get_contents($key);
        if (Str::endsWith($key, ['.crt', '.pem'])) {
            if (!openssl_pkey_get_private($res)) {
                return "-----BEGIN RSA PRIVATE KEY-----\n" .
                    wordwrap($res, 64, "\n", true) .
                    "\n-----END RSA PRIVATE KEY-----";
            }
        } else {
            return $res;
        }
        return '';
    }

    /**
     * A+取出签名内容
     *
     * @param string $httpMethod
     * @param string $path
     * @param string $clientId
     * @param string $timeString
     * @param string $content
     * @return string
     */
    public static function genSignContent(string $httpMethod, string $path, string $clientId, string $timeString, string $content): string
    {
        return $httpMethod . " " . $path . "\n" . $clientId . "." . $timeString . "." . $content;
    }

    /**
     * SHA256 签名
     *
     * @param string $signContent
     * @param string $priKey
     * @return string
     */
    public static function signWithSHA256RSA(string $signContent, string $priKey): string
    {
        openssl_sign($signContent, $signValue, $priKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signValue);
    }


    /**
     * RSA2 签名验证
     *
     * @param string $rspContent
     * @param string $rspSignValue
     * @param string $alipayPublicKey
     * @return false|int
     */
    public static function verifySignatureWithSHA256RSA(string $rspContent, string $rspSignValue, string $alipayPublicKey)
    {
        $pubKey = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($alipayPublicKey, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        if (
            strstr($rspSignValue, "=")
            || strstr($rspSignValue, "+")
            || strstr($rspSignValue, "/")
            || $rspSignValue == base64_encode(base64_decode($rspSignValue))
        ) {
            $originalRspSignValue = base64_decode($rspSignValue);
        } else {
            $originalRspSignValue = base64_decode(urldecode($rspSignValue));
        }
        return openssl_verify($rspContent, $originalRspSignValue, $pubKey, OPENSSL_ALGO_SHA256);
    }

    /**
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param $route
     * @return string
     */
    public static function getAuthName(string $action, string $controller, string $module, $route)
    {
        return strtolower($module . '/' . $controller . '/' . $action . '/' . self::paramStr($route));
    }

    /**
     * @param $params
     * @return string
     */
    public static function paramStr($params)
    {
        if (!is_array($params)) $params = json_decode($params, true) ?: [];
        $p = [];
        foreach ($params as $key => $param) {
            $p[] = $key;
            $p[] = $param;
        }
        return implode('/', $p);
    }

    /**
     * 截取中文指定字节
     * @param string $str
     * @param int $utf8len
     * @param string $encoding
     * @param string $file
     * @return string
     */
    public static function substrUTf8($str, $utf8len = 100, $encoding = 'UTF-8', $file = '....')
    {
        if (mb_strlen($str, $encoding) > $utf8len) {
            $str = mb_substr($str, 0, $utf8len, $encoding) . $file;
        }
        return $str;
    }
}
