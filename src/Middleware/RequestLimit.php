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
        $middleware = $request->get("route")?->getMiddleware();
        $key = "default";
        if ($middleware) {
            if (in_array("api", $middleware)) {
                $key = "api";
            } elseif (in_array("login", $middleware)) {
                $key = "login";
            } elseif (in_array("register", $middleware)) {
                $key = "register";
            } elseif (in_array("forgot-pass", $middleware)) {
                $key = "forgot-pass";
            }
        }
        $maxRequests = config("security.max_requests")[$key];
        $decaySeconds = config("security.decay_seconds")[$key];
        $clientIdentifier = $request->getClientIp();
        $cacheKey = "request_limit_" . $clientIdentifier;

        if (!session()->has($cacheKey)) {
            session()->set($cacheKey, [
                "count" => 0,
                "timestamp" => time(),
            ]);
        }

        $limit = session()->get($cacheKey);

        if (time() - $limit["timestamp"] > $decaySeconds) {
            $limit["count"] = 0;
            $limit["timestamp"] = time();
        }

        $limit["count"]++;

        if ($limit["count"] > $maxRequests) {
            return $key === "api"
                ? new JsonResponse(
                    ["message" => "Too many requests. Try again later."],
                    Response::HTTP_TOO_MANY_REQUESTS
                )
                : new Response(
                    "Too many requests. Try again later.",
                    Response::HTTP_TOO_MANY_REQUESTS
                );
        }

        session()->set($cacheKey, $limit);

        $response = $next($request);

        return $response;
    }
}
