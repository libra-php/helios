<?php

namespace Helios\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\{Response, Request};
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Logging implements IMiddleware
{
    private $logger;

    public function __construct()
    {
        $log_path = config("paths.logs");
        $logger = new Logger("app");
        $logger->pushHandler(
            new StreamHandler($log_path . "app.log", Logger::INFO)
        );
        $this->logger = $logger;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Log request details
        $this->logger->info("Request Started", [
            "method" => $request->getMethod(),
            "url" => $request->getUri(),
            "headers" => $request->headers->all(),
        ]);

        // Pass the request to the next middleware
        $response = $next($request);

        // Log response details
        $this->logger->info("Response Sent", [
            "status_code" => $response->getStatusCode(),
            "headers" => $response->headers->all(),
        ]);

        return $response;
    }
}
