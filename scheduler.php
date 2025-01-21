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

// Publish blog posts
$scheduler->php($jobs . "/publish_blog.php")->everyMinute();

// Send contact emails
$scheduler->php($jobs . "/email_jobs.php", args: ['-t' => 'home_contact'])->everyMinute();

// Blog comments
$scheduler->php($jobs . "/email_jobs.php", args: ['-t' => 'blog_comment'])->everyMinute();

// Send password reset emails
$scheduler->php($jobs . "/email_jobs.php", args: ['-t' => 'password_reset'])->everyMinute();

// Let the scheduler execute jobs which are due.
$scheduler->run();
