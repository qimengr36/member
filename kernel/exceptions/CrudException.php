<?php
/**
 *  +----------------------------------------------------------------------
 *  | PAYMUL [电商系统]
 *  +----------------------------------------------------------------------
 *  | Copyright (c) 2016~2024 https://www.paymul.co.jp All rights reserved.
 *  +----------------------------------------------------------------------
 *  | Licensed PAYMUL并不是自由软件，未经许可不能去掉PAYMUL相关版权
 *  +----------------------------------------------------------------------
 *  | Author: PMLEB Team
 *  +----------------------------------------------------------------------
 */

namespace kernel\exceptions;


class CrudException extends \RuntimeException
{
    public function __construct($message = "", $replace = [], $code = 0, \Throwable $previous = null)
    {
        if (is_array($message)) {
            $errInfo = $message;
            $message = $errInfo[1] ?? '未知错误';
            if ($code === 0) {
                $code = $errInfo[0] ?? 400;
            }
        }

        if (is_numeric($message)) {
            $code = $message;
            $message = getLang($message, $replace);
        }

        parent::__construct($message, $code, $previous);
    }
}
