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

    public function __construct(array $directives = [])
    {
        $this->nonce = bin2hex(random_bytes(16)); // Generate a secure nonce
        $this->policy = $this->buildPolicy($directives);
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Store nonce in attr bag
        $request->attributes->add(["nonce" => $this->nonce]);

        $response = $next($request);

        // Add the CSP header with the nonce and other directives
        $response->headers->set('Content-Security-Policy', $this->policy);

        return $response;
    }

    private function buildPolicy(array $directives): string
    {
        // Default directives
        $defaultDirectives = [
            'default-src' => "'self'",           // Default policy: Only allow resources from the same origin
            'script-src' => "'self' 'nonce-{$this->nonce}'",  // Allow scripts from same origin and inline scripts with nonce
            // 'style-src' => "'self' 'nonce-{$this->nonce}'",   // Allow styles from same origin and inline styles with nonce
            'style-src' => "'self' 'unsafe-inline'", // Fix for HTMX indicator, but less secure
            'img-src' => "'self' data:",               // Only allow images from the same origin
            'object-src' => "'none'",            // Disallow <object>, <embed>, <applet> elements
        ];

        // Merge user-defined directives with defaults
        $directives = array_merge($defaultDirectives, $directives);

        $policyParts = [];
        foreach ($directives as $directive => $value) {
            $policyParts[] = $directive . ' ' . $value;
        }

        return implode('; ', $policyParts);
    }

    public function getNonce(): string
    {
        return $this->nonce;
    }
}

