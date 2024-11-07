<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin", middleware: ["auth"])]
class AdminController extends Controller
{
    #[Get("/", "admin.index")]
    public function index(): void
    {
        die("wip");
    }
}
