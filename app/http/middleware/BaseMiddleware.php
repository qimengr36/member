<?php
namespace app\http\middleware;

use app\Request;
use kernel\interfaces\MiddlewareInterface;

/**
 * Class BaseMiddleware
 * @package app\api\middleware
 */
class BaseMiddleware implements MiddlewareInterface
{
    /**
     * @param Request $request
     * @param \Closure $next
     * @param bool $force
     * @return mixed
     */
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        if (!$request->hasMacro('adminId')) {
            $request->macro('adminId', function(){ return 0; });
        }
        return $next($request);
    }
}
