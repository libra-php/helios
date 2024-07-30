<?php

$root = __DIR__ . "/../../";
$app_root = $root . "app/";
$src_root = $root . "src/";
$storage_root = $root . "storage/";

return [
    "root" => $root,
    "controllers" => $app_root . "Controllers/",
    "storage" => $storage_root,
    "templates" => $app_root . "Templates/",
    "template-cache" => $storage_root . "templates/.cache/"
];
