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
    private $maxAge;
    private $includeSubDomains;
    private $preload;

    public function __construct(int $maxAge = 31536000, bool $includeSubDomains = true, bool $preload = true)
    {
        $this->maxAge = $maxAge;
        $this->includeSubDomains = $includeSubDomains;
        $this->preload = $preload;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Pass the request to the next middleware
        $response = $next($request);

        // Construct the HSTS header value
        $hstsHeader = "max-age={$this->maxAge}";
        if ($this->includeSubDomains) {
            $hstsHeader .= "; includeSubDomains";
        }
        if ($this->preload) {
            $hstsHeader .= "; preload";
        }

        // Add the HSTS header to the response
        $response->headers->set('Strict-Transport-Security', $hstsHeader);

        return $response;
    }
}
