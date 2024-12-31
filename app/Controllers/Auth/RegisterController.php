<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class RegisterController extends Controller
{
    public function __construct()
    {
        if (!config("security.register_enabled")) {
            error_log("Registration is disabled. IP: " . getClientIp());
            $route = findRoute("sign-in.index");
            redirect($route);
        }
    }

    #[Get("/register", "register.index")]
    public function index(): string
    {
        return $this->render("admin/register/index.html");
    }

    #[Post("/register", "register.post", ['register'])]
    public function post(): string
    {
        $this->addErrorMessage("password.regex", "Must contain: 1 uppercase, 1 number, and 1 symbol");
        $this->addErrorMessage("username.regex", "Invalid username");
        $valid = $this->validateRequest([
            "name" => ["required"],
            "email" => ["required", "email", "unique:=users"],
            "username" => ["required", "unique:=users", "regex:=^[a-zA-Z0-9]+$"],
            "password" => [
                "required", 
                "min_length:=8", 
                "regex:=^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"
            ],
            "password_match" => ["required", function ($value) {
                $this->addErrorMessage("password_match", "Passwords must match");
                return request()->get("password") === $value;
            }],
        ]);
        if ($valid) {
            $user = Auth::registerUser($valid);
            if ($user) {
                Auth::logUser($user);
                $two_factor_enabled = config("security.two_factor_enabled");
                if ($two_factor_enabled) {
                    $route = findRoute("2fa.index");
                    redirect($route, [
                        "target" => "#register",
                        "select" => "#two-factor-authentication",
                        "swap" => "outerHTML",
                    ]);
                } else {
                    $route = moduleRoute("module.index", "users");
                    redirect($route, [
                        "target" => "#register",
                        "select" => "#admin",
                        "swap" => "outerHTML",
                    ]);
                }
            } else {
                Flash::add("warning", "Failed to create new account");
            }
        }
        return $this->index();
    }
}
