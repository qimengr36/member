<?php


namespace kernel\services\workerMan;

use app\services\system\admin\B2bAdminAuthServices;
use Workerman\Connection\TcpConnection;
use app\services\system\admin\AdminAuthServices;
use kernel\exceptions\AuthException;

class WorkerManHandle
{
    protected $service;

    public function __construct(WorkermanService &$service)
    {
        $this->service = &$service;
    }

    public function login(TcpConnection &$connection, array $res, Response $response)
    {
        if (!isset($res['data']) && !isset($res['noncestr'])) {
            return $response->close([
                'msg' => '授权失败!'
            ]);
        }
        $token = $res['data'];
        $noncestr = $res['noncestr'];
        try {
            $service = app()->make(B2bAdminAuthServices::class);
            $authInfo = $service->parseToken($token);
        } catch (AuthException $e) {
            return $response->close([
                'msg' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
        }
        $connection->adminInfo = $authInfo;
        $connection->noncestr = $noncestr;
        $connection->adminId = $authInfo['id'];
        $this->service->setUser($connection);
        return $response->success();
    }
}
