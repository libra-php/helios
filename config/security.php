<?php

return [
    "default_admin_pass" => "Admin2024!",
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
    "max_requests" => 500,
    "decay_seconds" => 60,
];
