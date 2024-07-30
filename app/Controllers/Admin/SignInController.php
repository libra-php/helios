<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin")]
class SignInController extends Controller
{
    #[Get("/sign-in", "sign-in.index")]
    public function index() {
        return $this->render("admin/sign-in/index.html");
    }
}
