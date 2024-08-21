<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Modify response if request is HTMX
 */
class HTMX implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->headers->has("HX-Request")) {
            $response->headers->set('Cache-Control', 'no-store, max-age=0');
        }

        return $response;
    }
}
