<?php

namespace App\Controllers\Admin\Auth;

use Helios\Web\Controller;
use Helios\Admin\Auth;
use StellarRouter\{Get, Group, Post};

#[Group(prefix: "/admin")]
class SignInController extends Controller
{
    #[Get("/sign-in", "sign-in.index")]
    public function index() {
        return $this->render("admin/sign-in/index.html");
    }

    #[Post("/sign-in", "sign-in.post")]
    public function post() {
        $valid = $this->validateRequest([
            'email' => ['required'],
            'password' => ['required'],
            'remember_me' => [],
        ]);

        if ($valid) {
            if (Auth::authenticateUser($valid->email, $valid->password, isset($valid->remember_me))) {
                redirect(route("profile.index"));
            }
            $this->addRequestError("password", "Invalid email or password");
        }

        return $this->index();
    }
}
