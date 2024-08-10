<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{JsonResponse, Response, Request};

/**
 * Middleware
 * Limits the number of requests to prevent brute force attacks
 */
class RequestLimit implements IMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        $maxRequests = config("security.max_requests");
        $decaySeconds = config("security.decay_seconds");
        $clientIdentifier = $request->getClientIp();
        $cacheKey = 'request_limit_' . $clientIdentifier;

        if (!session()->has($cacheKey)) {
            session()->set($cacheKey, [
                'count' => 0,
                'timestamp' => time()
            ]);
        }

        $limit = session()->get($cacheKey);

        if (time() - $limit['timestamp'] > $decaySeconds) {
            $limit['count'] = 0;
            $limit['timestamp'] = time();
        }

        $limit['count']++;

        if ($limit['count'] > $maxRequests) {
            $middleware = $request->get("route")?->getMiddleware();
            return in_array("api", $middleware) ?
                new JsonResponse(["message" => "Too many requests. Try again later."], Response::HTTP_TOO_MANY_REQUESTS) :
                new Response("Too many requests. Try again later.", Response::HTTP_TOO_MANY_REQUESTS);
        }

        session()->set($cacheKey, $limit);

        $response = $next($request);

        return $response;
    }
}
