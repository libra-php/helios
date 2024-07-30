<?php

namespace Helios\Middleware;

use Symfony\Component\HttpFoundation\{Response, Request};

use Closure;

interface IMiddleware
{
    /**
     * @param Closure(): Response $next
     */
    public function handle(Request $request, Closure $next): Response;
}
