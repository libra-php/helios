<?php

namespace App\Controllers\Auth;

use StellarRouter\Get;

class SignOutController
{
    #[Get("/sign-out", "sign-out.index")]
    public function index()
    {
        dump("WIP!");
        redirect(findRoute("sign-in.index"));
    }
}
