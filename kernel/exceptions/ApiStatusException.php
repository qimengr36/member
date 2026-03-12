<?php


namespace kernel\exceptions;

/**
 * API应用错误信息
 * Class ApiException
 * @package pmleb\exceptions
 */
class ApiStatusException extends \RuntimeException
{
    protected $apiStatus;
    protected $apiData;

    public function __construct($status, $message, $data = [], $replace = [], $code = 0, \Throwable $previous = null)
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

        $this->apiData = $data;
        $this->apiStatus = $status;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getApiStatus()
    {
        return $this->apiStatus;
    }

    /**
     * @return array|mixed
     */
    public function getApiData()
    {
        return $this->apiData;
    }
}
