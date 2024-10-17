<?php

namespace App\Controllers\Feed;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/feed", middleware: ['auth'])]
class FeedController extends Controller
{
    #[Get("/", "feed.index")]
    public function index(): string
    {
        $user = user();
        $user->avatar = $user->avatar();
        return $this->render("admin/feed/feed.html", [
            "user" => $user,
        ]);
    }
}

