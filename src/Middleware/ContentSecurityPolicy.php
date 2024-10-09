<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Adds Content Security Policy (CSP) headers to the response.
 */
class ContentSecurityPolicy implements IMiddleware
{
    private $policy;
    private $nonce;

    public function handle(Request $request, Closure $next): Response
    {
        $csp_directives = config("security.csp_directives");
        $this->nonce = bin2hex(random_bytes(16)); // Generate a secure nonce
        $this->policy = $this->buildPolicy($csp_directives);

        session()->set("nonce", $this->nonce);

        $response = $next($request);

        // Add the CSP header with the nonce and other directives
        $response->headers->set('Content-Security-Policy', $this->policy);

        return $response;
    }

    private function buildPolicy(array $directives): string
    {
        // Default directives
        $defaultDirectives = [
            // 'default-src' => "*",                          // Default policy: Only allow resources from the same origin
            // 'script-src' => "*",
            // 'style-src' => "*",
            // 'img-src' => "*",                        // Only allow images from the same origin
            // 'object-src' => "'none'",                           // Disallow <object>, <embed>, <applet> elements
        ];

        // Merge user-defined directives with defaults
        $directives = array_merge($defaultDirectives, $directives);

        $policyParts = [];
        foreach ($directives as $directive => $value) {
            $policyParts[] = $directive . ' ' . $value;
        }

        return implode('; ', $policyParts);
    }
}
