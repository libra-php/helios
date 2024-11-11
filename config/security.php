<?php

return [
    "default_admin_pass" => "Admin2024!",
    "auth_route" => moduleRoute("module.index", "users"),
    "register_enabled" => true,
    "max_failed_login" => 10,
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
