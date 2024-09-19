<?php

$root = __DIR__ . "/../";
$app = $root . "app/";
$bin = $root . "bin/";
$src = $root . "src/";
$migrations = $root . "migrations/";
$templates = $root . "templates/";
$storage = $root . "storage/";
$jobs = $root . "jobs/";

return [
    "root" => $root,
    "bin" => $bin,
    "controllers" => $app . "Controllers/",
    "models" => $app . "Models/",
    "modules" => $app . "Modules/",
    "storage" => $storage,
    "jobs" => $jobs,
    "logs" => $storage . "logs/",
    "uploads" => $storage . "uploads/",
    "templates" => $templates,
    "template-cache" => $storage . "templates/.cache/",
    "migrations" => $migrations,
];
