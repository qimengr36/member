<?php

namespace app\api\middleware;

use app\Request;
use kernel\interfaces\MiddlewareInterface;
use think\facade\Config;

class AuthTokenMiddleware implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        $token = trim(ltrim($request->header(Config::get('cookie.token_name', 'Authorization')), 'Bearer'));
        if (!$token) {
            $token = trim(ltrim($request->get('token')));
        }
//        /** @var AdminAuthServices $service */
//        $service = app()->make(AdminAuthServices::class);
//        $adminInfo = $service->parseToken($token);
//
//        $request->macro('isAdminLogin', function () use (&$adminInfo) {
//            return !is_null($adminInfo);
//        });
//        $request->macro('adminId', function () use (&$adminInfo) {
//            return $adminInfo['id'];
//        });
//
//        $request->macro('adminInfo', function () use (&$adminInfo) {
//            return $adminInfo;
//        });

        return $next($request);
    }
}