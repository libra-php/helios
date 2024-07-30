<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};
use Ramsey\Uuid\Uuid;

/**
 * Adds a UUID to request
 */
class RequestUuid implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $uuid4 = Uuid::uuid4()->toString();
        $request->attributes->add(["request_uuid" => $uuid4]);

        $response = $next($request);

        return $response;
    }
}
