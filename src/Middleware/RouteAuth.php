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
            redirect(route("sign-in.index"));
        }

        $response = $next($request);

        return $response;
    }

    private function userAuth(Request $request): bool
    {
        $id = session()->get("user_id");
        $uuid = $request->cookies->get("user_uuid");

        // Cookie
        if ($uuid) {
            $user = User::findByAttribute("uuid", $uuid);
            if ($user) {
                return true;
            }
        } else if ($id) {
            $user = User::find($id);
            if ($user) {
                return true;
            }
        }

        return false;
    }
}
