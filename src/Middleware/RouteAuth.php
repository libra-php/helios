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
        $user = user();
        $middleware = $request->get("route")?->getMiddleware();
        $two_factor_enabled = config("security.two_factor_enabled");
        $two_factor_confirmed = session()->get("two_factor_confirmed");
        $valid = $two_factor_enabled ? $user && $two_factor_confirmed : $user;

        if ($middleware && in_array("auth", $middleware) && !$valid) {
            redirect(findRoute("sign-in.index"));
        }

        $response = $next($request);

        return $response;
    }
}
