<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class TwoFactorAuthenticationController extends Controller
{

    public function __construct()
    {
        if (!user()) {
            $route = findRoute("sign-in.index");
            redirect($route, [
                "target" => "#two-factor-authentication",
                "select" => "#sign-in",
                "swap" => "outerHTML",
            ]);
        }
    }

    #[Get("/two-factor-authentication", "2fa.index")]
    public function index(): string
    {
        $user = user();
        return $this->render("admin/two-fa/index.html", [
            "show_qr" => !$user->two_fa_confirmed,
            "qr_src" => !$user->two_fa_confirmed ? Auth::generateTwoFactorQR($user) : '',
        ]);
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
            if (Auth::testTwoFactorCode($valid->code)) {
                $route = moduleRoute("module.index", "users");
                redirect($route, [
                    "target" => "#two-factor-authentication",
                    "select" => "#admin",
                    "swap" => "outerHTML",
                ]);
            } else {
                $this->addRequestError("code", "Invalid code");
            }
        }
        return $this->index();
    }
}
