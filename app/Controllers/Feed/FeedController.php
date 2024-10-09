<?php

namespace App\Controllers\Feed;

use Helios\Web\Controller;
use StellarRouter\{Get, Post, Group};

#[Group(prefix: "/feed", middleware: ['auth'])]
class FeedController extends Controller
{
    #[Get("/", "feed.index")]
    public function index(): string
    {
        $user = user();
        $user->avatar = $user->gravatar(40);
        return $this->render("admin/feed/feed.html", [
            "user" => $user,
        ]);
    }
}

