<?php

/**
 * Publish blog post on configured date
 */

require_once __DIR__ . "/../vendor/autoload.php";

use App\Models\BlogPost;

$posts = BlogPost::where("status_id", 2)
    ->where("publish_at", "<", date("Y-m-d H:i:s"))
    ->get(lazy: false);

foreach ($posts as $post) {
    $post->status_id = 3; // Published 
    $post->save();
}
