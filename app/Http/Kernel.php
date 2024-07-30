<?php

namespace App\Http;

use Helios\Kernel\Http;

class Kernel extends Http
{
    protected array $middleware = [
        \Helios\Middleware\RequestUuid::class,
    ];
}
