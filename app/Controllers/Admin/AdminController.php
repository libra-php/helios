<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin")]
class AdminController extends Controller
{
    #[Get("/", "admin.index")]
    public function index()
    {
        // $route = user() ? route("profile.index") : route("sign-in.index");
        // redirect($route);
    }
}
