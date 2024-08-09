<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

class Whitelist implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config("security.whitelist");
        $clientIp = $request->getClientIp();

        if (!in_array($clientIp, $whitelist)) {
            return new Response('Access denied: Your IP address is not whitelisted.', Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}

