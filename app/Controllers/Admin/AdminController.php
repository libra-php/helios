<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin")]
class AdminController extends Controller
{
    #[Get("/", "admin.index", ["auth"])]
    public function index()
    {
        redirect(config("security.auth_route"));
    }
}
