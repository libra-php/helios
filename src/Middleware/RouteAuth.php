<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Route requires authenticated user
 */
class RouteAuth implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $middleware = $request->get("route")?->getMiddleware();

        if ($middleware && in_array("auth", $middleware) && !user()) {
            redirect(findRoute("sign-in.index"));
        }

        $response = $next($request);

        return $response;
    }
}
