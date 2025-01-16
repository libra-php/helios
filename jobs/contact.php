<?php

/**
 * Send contact emails
 */

require_once __DIR__ . "/../vendor/autoload.php";

use App\Models\EmailJob;

$max_retries = 3;

$jobs = EmailJob::where("tag", "home_contact")
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

