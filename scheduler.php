<?php
/**
* Schedule automated tasks here
* Library: https://github.com/peppeocchi/php-cron-scheduler
*/
require_once __DIR__ . '/vendor/autoload.php';

use GO\Scheduler;

$scheduler = new Scheduler();

$jobs = config("paths.jobs");
$logs = config("paths.logs");

// Add scheduled tasks here :)

// Heartbeat
$heartbeat_filename = date("Y-m-d") . "_heartbeat.log";
$scheduler->php($jobs . "/heartbeat.php")
    ->everyMinute()
    ->output($logs . "/$heartbeat_filename", true);

// Rotate logs
$scheduler->php($jobs . "/rotate_logs.php")->monday();

// Send password reset emails
$scheduler->php($jobs . "/password_reset.php")->everyMinute();

// Publish blog posts
$scheduler->php($jobs . "/publish_blog.php")->everyMinute();

// Let the scheduler execute jobs which are due.
$scheduler->run();
