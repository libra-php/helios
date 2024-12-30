<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Admin module middleware
 */
class Module implements IMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $user = user();
        $middleware = $request->get("route")?->getMiddleware();

        // Manage roles
        if (key_exists("module", $middleware) && key_exists("role", $middleware)) {
            if (!in_array($user->role()->name, $middleware["role"])) {
                redirect("/permission-denied");
            }
        }

        $response = $next($request);

        return $response;
    }
}
