<?php

namespace App\Controllers\Auth;

use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class SignInController extends Controller
{
    #[Get("/sign-in", "sign-in.index")]
    public function index()
    {
        return $this->render("admin/sign-in/index.html", [
            "register_enabled" => config("security.register_enabled")
        ]);
    }

    #[Post("/sign-in", "sign-in.post")]
    public function post()
    {
        $valid = $this->validateRequest([
            'email' => ['required'],
            'password' => ['required'],
            'remember_me' => [],
        ]);

        dump("WIP!");
        if ($valid) {
            $this->addRequestError("password", "Invalid email or password");
        }

        return $this->index();
    }
}
