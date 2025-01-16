<?php

namespace App\Http;

use Helios\Kernel\Http;

class Kernel extends Http
{
    protected array $middleware = [
        // \Helios\Middleware\Whitelist::class,
        \Helios\Middleware\Blacklist::class,
        \Helios\Middleware\EncryptCookies::class,
        \Helios\Middleware\HTMX::class,
        \Helios\Middleware\RouteAuth::class,
        \Helios\Middleware\CSRF::class,
        \Helios\Middleware\RequestUuid::class,
        \Helios\Middleware\RequestLimit::class,
        \Helios\Middleware\XSSProtection::class,
        \Helios\Middleware\ContentSecurityPolicy::class,
        \Helios\Middleware\HSTS::class,
        // \Helios\Middleware\Logging::class,
        \Helios\Middleware\MaintenanceMode::class,
    ];
}
