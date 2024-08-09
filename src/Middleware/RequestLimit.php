<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Limits the number of requests to prevent brute force attacks
 */
class RequestLimit implements IMiddleware
{

    public function __construct(private $maxRequests = 60, private $decaySeconds = 25) {}

    public function handle(Request $request, Closure $next): Response
    {
        $clientIdentifier = $request->getClientIp();
        $cacheKey = 'request_limit_' . $clientIdentifier;

        if (!session()->has($cacheKey)) {
            session()->set($cacheKey, [
                'count' => 0,
                'timestamp' => time()
            ]);
        }

        $limit = session()->get($cacheKey);

        if (time() - $limit['timestamp'] > $this->decaySeconds) {
            $limit['count'] = 0;
            $limit['timestamp'] = time();
        }

        $limit['count']++;

        if ($limit['count'] > $this->maxRequests) {
            return new Response(
                "Too many requests. Try again later.",
                Response::HTTP_TOO_MANY_REQUESTS
            );
        }

        session()->set($cacheKey, $limit);

        $response = $next($request);

        return $response;
    }
}
