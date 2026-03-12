<?php


namespace app\adminapi\middleware;

use app\Request;
use app\services\system\admin\SystemRoleServices;
use kernel\exceptions\AuthException;
use kernel\interfaces\MiddlewareInterface;

/**
 * 权限规则验证
 * Class AdminCheckRoleMiddleware
 * @package app\http\middleware
 */
class AdminCheckRoleMiddleware implements MiddlewareInterface
{
    /**
     * 权限规则验证
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \throwable
     */
    public function handle(Request $request, \Closure $next)
    {
        if (!$request->adminId() || !$request->adminInfo())
            throw new AuthException(100100);

        if ($request->adminInfo()['level']) {
            /** @var SystemRoleServices $systemRoleService */
            $systemRoleService = app()->make(SystemRoleServices::class);
            $systemRoleService->verifyAuth($request);
        }

        return $next($request);
    }
}
