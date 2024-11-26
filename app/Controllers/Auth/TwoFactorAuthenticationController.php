<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class TwoFactorAuthenticationController extends Controller
{
    #[Get("/two-factor-authentication", "2fa.index")]
    public function index(): string
    {
        return $this->render("admin/two-fa/index.html", [
        ]);
    }

    #[Post("/two-factor-authentication", "sign-in.post")]
    public function post(): string
    {
        die("WIP");
    }
}

