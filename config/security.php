<?php

return [
    "default_admin_pass" => "Admin2025!",
    "two_factor_enabled" => env("TWO_FACTOR_ENABLED"),
    "register_enabled" => env("REGISTER_ENABLED"),
    "max_failed_login" => 5,
    "lockout_time" => 60*10,
    "reset_token_time" => 60*10,
    "whitelist" => [
    ],
    "blacklist" => [
    ],
    // Prevent cross-site scripting (XSS)
    // eg) 'script-src' => "'self' https://trusted.cdn.com",
    "csp_directives" => [
    ],
    "max_requests" => [
        'captcha' => 200,
        'default' => 100,
        'api' => 200,
        'forgot-pass' => 20,
        'register' => 20,
        'login' => 50,
    ],
    "decay_seconds" => [
        'captcha' => 1,
        'default' => 60,
        'api' => 60,
        'forgot-pass' => 100,
        'register' => 300,
        'login' => 300,
    ],
];
