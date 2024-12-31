<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class SignInController extends Controller
{
    #[Get("/sign-in", "sign-in.index")]
    public function index(): string
    {
        return $this->render("admin/sign-in/index.html", [
            "register_enabled" => config("security.register_enabled")
        ]);
    }

    #[Post("/sign-in", "sign-in.post", ['login'])]
    public function post(): string
    {
        $valid = $this->validateRequest([
            'email_or_username' => ['required'],
            'password' => ['required'],
            'remember_me' => [],
        ]);

        if ($valid) {
            if (Auth::signIn($valid)) {
                $two_factor_enabled = config("security.two_factor_enabled");
                if ($two_factor_enabled) {
                    $route = findRoute("2fa.index");
                    redirect($route, [
                        "target" => "#sign-in",
                        "select" => "#two-factor-authentication",
                        "swap" => "outerHTML",
                    ]);
                } else {
                    $route = moduleRoute("module.index", "users");
                    redirect($route, [
                        "target" => "#sign-in",
                        "select" => "#admin",
                        "swap" => "outerHTML",
                    ]);
                }
            }
            $this->addRequestError("password", "Invalid email or password");
        }

        return $this->index();
    }
}
