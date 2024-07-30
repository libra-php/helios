<?php

$root = __DIR__ . "/../";
$app_root = $root . "app/";
$src_root = $root . "src/";
$templates_root = $root . "templates/";
$storage_root = $root . "storage/";

return [
    "root" => $root,
    "controllers" => $app_root . "Controllers/",
    "storage" => $storage_root,
    "templates" => $templates_root,
    "template-cache" => $storage_root . "templates/.cache/"
];
