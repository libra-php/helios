<?php

use Lunar\Connection\{MySQL, SQLite};
use Symfony\Component\HttpFoundation\Request;

return [
    Request::class => Request::createFromGlobals(),
    MySQL::class => function() {
        $config = config("database");
        dd($config);
        if (!$config["enabled"]) return;
        return new MySQL(
            $config["dbname"],
            $config["username"],
            $config["password"],
            $config["host"],
            $config["port"],
            $config["charset"],
            $config["options"]
        );
    },
    SQLite::class => function() {
        $config = config("database");
        if (!$config["enabled"]) return;
        return new SQLite(
            $config["path"],
            $config["options"]
        );
    },
];
