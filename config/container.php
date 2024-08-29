<?php

use Helios\View\TwigExtension;
use Lunar\Connection\{MySQL, SQLite};
use Symfony\Component\HttpFoundation\Request;

return [
    \Twig\Environment::class => function () {
        $cache_path = config("paths.template-cache");
        $templates_path = config("paths.templates");
        $loader = new \Twig\Loader\FilesystemLoader($templates_path);
        $debug = config("app.debug");
        $twig = new \Twig\Environment($loader, [
            'cache' => $debug ? false : $cache_path,
            'auto_reload' => $debug,
            'debug' => $debug,
        ]);
        $twig->addExtension(new TwigExtension);
        return $twig;
    },
    Request::class => Request::createFromGlobals(),
    MySQL::class => function () {
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
    SQLite::class => function () {
        $config = config("database");
        if (!$config["enabled"]) return;
        return new SQLite(
            $config["path"],
            $config["options"]
        );
    },
];
