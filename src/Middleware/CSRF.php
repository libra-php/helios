<?php

namespace Helios\Middleware;

use Closure;
use Helios\Middleware\IMiddleware;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Checks request for CSRF token
 */
class CSRF implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $middleware = $request->get("route")?->getMiddleware() ?? [];
        $this->token();

        if (!in_array("api", $middleware)) {
            if (!$this->validate($request)) {
                return new Response("Invalid CSRF token", Response::HTTP_FORBIDDEN);
            }
        }

        $response = $next($request);

        return $response;
    }

    /**
     * Setup CSRF token
     */
    private function token(): void
    {
        $token = session()->get("csrf_token");
        $token_ts = session()->get("csrf_token_ts");

        if (
            is_null($token) ||
            is_null($token_ts) ||
            $token_ts + 3600 < time()
        ) {
            $token = $this->generateToken();
            session()->set("csrf_token", $token);
            session()->set("csrf_token_ts", time());
        }
    }

    /**
     * Generate a CSRF token string
     */
    function generateToken(): string
    {
        $key = config("app.key");
        $token = md5($key . random_bytes(32));
        return bin2hex($token);
    }

    /**
     * Validate a CSRF request token
     */
    private function validate(Request $request): bool
    {
        $request_method = $request->getMethod();
        if (in_array($request_method, ["GET", "HEAD", "OPTIONS"])) {
            return true;
        }

        $session_token = session()->get("csrf_token");
        $token = $request->get("csrf_token");

        if (
            !is_null($session_token) &&
            !is_null($token) &&
            hash_equals($session_token, $token)
        ) {
            return true;
        }

        return false;
    }
}
