<?php

namespace Helios\Middleware;

use Closure;
use App\Models\User;
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

        if ($middleware && in_array("auth", $middleware) && !$this->userAuth($request)) {
            redirect(findRoute("sign-in.index"));
        }

        $response = $next($request);

        return $response;
    }

    private function userAuth(Request $request): bool
    {
        $session_uuid = session()->get("user_uuid");
        $cookie_uuid = $request->cookies->get("user_uuid");

        if ($cookie_uuid || $session_uuid) {
            $user = User::where("uuid", $cookie_uuid ?? $session_uuid);
            if ($user) {
                return true;
            }
        }
        return false;
    }
}
