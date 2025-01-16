<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

class Blacklist implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config("security.blacklist");
        $clientIp = $request->getClientIp();

        if (in_array($clientIp, $whitelist)) {
            return new Response("Access denied", Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
