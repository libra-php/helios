<?php

namespace App\Controllers\Admin\Auth;

use Helios\Admin\Auth;
use StellarRouter\{Get, Group};

#[Group(prefix: "/admin")]
class SignOutController
{
    #[Get("/sign-out", "sign-out.index")]
    public function index()
    {
        Auth::signOut();
        redirect(route("sign-in.index"));
    }
}
