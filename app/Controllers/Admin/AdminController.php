<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin")]
class AdminController extends Controller
{
    #[Get("/profile", "profile.index", ["auth"])]
    public function profile()
    {
        return $this->render("admin/profile/index.html");
    }
}
