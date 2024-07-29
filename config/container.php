<?php

use Lunar\Connection\{MySQL, SQLite};

return [
    'mysql' => function() {
        $config = config("database");
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
    'sqlite' => function() {
        $config = config("database");
        if (!$config["enabled"]) return;
        return new SQLite(
            $config["path"],
            $config["options"]
        );
    },
];
