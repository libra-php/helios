<?php

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class RegisterController extends Controller
{
    public function __construct(private AuthService $service)
    {
        $this->service->checkRegisterEnabled();
    }

    #[Get("/register", "register.index")]
    public function index(): string
    {
        return $this->render("admin/register/index.html");
    }

    #[Post("/register", "register.post", ["register"])]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "name" => ["required"],
            "email" => ["required", "email", "unique:=users"],
            "username" => [
                "required",
                "unique:=users",
                "regex:=^[a-zA-Z0-9_-]+$",
            ],
            "password" => [
                "required",
                "min_length:=8",
                "regex:=^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$",
            ],
            "password_match" => [
                "required",
                function ($value) {
                    $this->addErrorMessage(
                        "password_match",
                        "Passwords must match"
                    );
                    return request()->get("password") === $value;
                },
            ],
        ]);
        // Update validation messages
        $this->addErrorMessage(
            "password.regex",
            "Must contain: 1 uppercase, 1 number, and 1 symbol"
        );
        $this->addErrorMessage("username.regex", "Invalid username");

        if ($valid) {
            $new_user = $this->service->registerUser($valid);
            if ($new_user) {
                $this->service->logUser($new_user);
                $this->service->redirectRegister();
            } else {
                Flash::add("warning", "Failed to create new account");
            }
        }
        return $this->index();
    }
}
