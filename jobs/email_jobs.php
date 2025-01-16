<?php

/**
 * Send contact emails
 */

require_once __DIR__ . "/../vendor/autoload.php";

use App\Models\EmailJob;

$opts = "t:";
$options = getopt($opts);
$tag = $options["t"];

$max_retries = 3;

$jobs = EmailJob::where("tag", $tag)
    ->andWhere("retries", "<", $max_retries)
    ->andWhere("send_at", "<", date("Y-m-d H:i:s"))
    ->andWhere("sent", 0)
    ->get(lazy: false);

foreach ($jobs as $email) {
    $result = email()->send(
        subject: $email->subject,
        body: $email->body,
        to_addresses: [$email->to_address],
    );
    if ($result) {
        $email->sent = 1;
    } else {
        $email->retries++;
    }
    $email->save();
}

