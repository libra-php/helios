<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use StellarRouter\Get;

class SignOutController
{
    #[Get("/sign-out", "sign-out.index")]
    public function index()
    {
        Auth::signOut();
        redirect(route("sign-in.index"));
    }
}
