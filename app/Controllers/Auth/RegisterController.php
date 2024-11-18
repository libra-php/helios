<?php

namespace App\Controllers\Auth;

use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class RegisterController extends Controller
{
    public function __construct()
    {
        if (!config("security.register_enabled")) {
            $route = moduleRoute("module.index", "users");
            redirect($route);
        }
    }

    #[Get("/register", "register.index")]
    public function index(): string
    {
        return $this->render("admin/register/index.html");
    }

    #[Post("/register", "register.post")]
    public function post(): string
    {
        $this->addErrorMessage("regex", "Must contain: 1 uppercase, 1 number, and 1 symbol");
        $valid = $this->validateRequest([
            "name" => ["required"],
            "email" => ["required", "email", "unique|users"],
            "username" => ["required", "unique|users"],
            "password" => ["required", "min_length|8", "regex|^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"],
            "password_match" => ["required", function ($value) {
                $this->addErrorMessage("password_match", "Passwords must match");
                return request()->get("password") === $value;
            }],
        ]);
        if ($valid) {
            die("WIP");
        }
        return $this->index();
    }
}
