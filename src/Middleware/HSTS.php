<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Adds HTTP Strict Transport Security (HSTS) header to responses.
 */
class HSTS implements IMiddleware
{
    public function __construct(private int $maxAge = 31536000, private bool $includeSubDomains = true, private bool $preload = true)
    {}

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $hstsHeader = "max-age={$this->maxAge}";
        if ($this->includeSubDomains) {
            $hstsHeader .= "; includeSubDomains";
        }
        if ($this->preload) {
            $hstsHeader .= "; preload";
        }

        $response->headers->set('Strict-Transport-Security', $hstsHeader);

        return $response;
    }
}
