<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};

/**
 * Middleware
 * Will display maintenance mode message and exit if true
 */
class MaintenanceMode implements IMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $isMaintenanceMode = config("app.maintenance_mode");
        $maintenanceMessage = "The application is currently undergoing maintenance. Please check back later.";

        if ($isMaintenanceMode) {
            return new Response($maintenanceMessage, Response::HTTP_SERVICE_UNAVAILABLE);
        }

        return $next($request);
    }
}
