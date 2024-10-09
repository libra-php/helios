<?php

return [
    "default_admin_pass" => "Admin2024!",
    "auth_route" => "/admin/feed",
    "whitelist" => [
    ],
    "blacklist" => [

    ],
    // Prevent cross-site scripting (XSS)
    // eg) 'script-src' => "'self' https://trusted.cdn.com",
    "csp_directives" => [
    ],
    "max_requests" => 120,
    "decay_seconds" => 25,
];
