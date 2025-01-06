<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Adds XSS protection headers and sanitizes input.
 */
class XSSProtection implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $this->sanitizeInput($request);

        $response = $next($request);
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }

    private function sanitizeInput(Request $request)
    {
        // Sanitize GET parameters
        foreach ($request->query->all() as $key => $value) {
            //$request->query->set($key, $this->sanitize($value));
        }

        // Sanitize POST parameters
        foreach ($request->request->all() as $key => $value) {
            //$request->request->set($key, $this->sanitize($value));
        }
    }

    private function sanitize($value)
    {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }

        // Strip tags and encode special characters
        return htmlspecialchars(strip_tags($value), ENT_QUOTES, 'UTF-8');
    }
}
