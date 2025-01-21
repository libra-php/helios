<?php

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class TwoFactorAuthenticationController extends Controller
{
    public function __construct(private AuthService $service)
    {
        if (!user()) {
            $this->service->redirectAuth();
        }
    }

    #[Get("/two-factor-authentication", "2fa.index")]
    public function index(): string
    {
        $user = user();
        return $this->render("admin/two-fa/index.html", [
            "show_qr" => !$user->two_fa_confirmed,
            "qr_src" => !$user->two_fa_confirmed
                ? $this->service->generateTwoFactorQR($user)
                : "",
        ]);
    }

    #[Post("/two-factor-authentication", "sign-in.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "code" => ["required", "min_length:=6", "max_length:=6"],
        ]);

        $this->addErrorMessage("min_length", "Please enter your 2FA code");
        $this->addErrorMessage("max_length", "Please enter your 2FA code");

        if ($valid) {
            if ($this->service->testTwoFactorCode(user(), $valid->code)) {
                $this->service->redirectTwoFactorAuthentication();
            } else {
                $this->addRequestError("code", "Invalid code");
            }
        }
        return $this->index();
    }
}
