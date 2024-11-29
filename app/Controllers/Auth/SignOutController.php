<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\Get;

class SignOutController extends Controller
{
    #[Get("/sign-out", "sign-out.index")]
    public function index(): void
    {
        Auth::signOut();
        redirect(findRoute("sign-in.index"));
    }
}
