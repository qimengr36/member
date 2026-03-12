<?php
namespace app\adminapi\middleware;

use app\Request;
use app\services\system\log\SystemFileServices;
use kernel\interfaces\MiddlewareInterface;
use kernel\services\CacheService;

/**
 * 后台登陆验证中间件
 * Class AdminAuthTokenMiddleware
 * @package app\adminapi\middleware
 */
class AdminEditorTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $token = CacheService::get(trim($request->get('fileToken')));

        /** @var SystemFileServices $service */
        $service = app()->make(SystemFileServices::class);
        $service->parseToken($token);

        return $next($request);
    }
}
