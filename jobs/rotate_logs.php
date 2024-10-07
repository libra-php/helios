<?php

/**
 * Automatically remove old logs
 */

require_once __DIR__ . "/../vendor/autoload.php";

$logs_dir = config("paths.logs");
$max_days = 6;
$max_age = $max_days * 24 * 60 * 60;

foreach (glob($logs_dir . "/*.log") as $log) {
    $t = time();
    $create_time = getFileCreationDateLinux($log);
    $mod_time = filemtime($log);
    $create_age = $t - $create_time;
    $mod_age = $t - $mod_time;
    if ($create_time > $max_age || $mod_age > $max_age) {
        unlink($log);
    }
}

function getFileCreationDateLinux($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }

    # If you're using a file system like ext4 with the right 
    # kernel and mount options, the birth time might be available.
    $output = shell_exec('stat -c %W ' . escapeshellarg($filePath));
    $creationTime = (int)$output;

    if ($creationTime > 0) {
        return date("Y-m-d H:i:s", $creationTime);
    }

    return false;
}
