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
        return $this->render("admin/two-fa/index.html", []);
    }

    #[Post("/two-factor-authentication", "sign-in.post")]
    public function post(): string
    {
        $this->addErrorMessage("min_length", "Please enter your 2FA code");
        $this->addErrorMessage("max_length", "Please enter your 2FA code");
        $valid = $this->validateRequest([
            "code" => [
                "required",
                "min_length|6",
                "max_length|6",
            ]
        ]);
        if ($valid) {
            die("WIP");
        }
        return $this->index();
    }
}
