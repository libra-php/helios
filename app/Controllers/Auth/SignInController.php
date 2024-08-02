<?php

namespace App\Controllers\Auth;

use Helios\Web\Controller;
use Helios\Admin\Auth;
use StellarRouter\{Get, Post};

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
                //redirect(route("profile.index"));
                // FIXME: redirect module route
                redirect("/admin/profile");
            }
            $this->addRequestError("password", "Invalid email or password");
        }

        return $this->index();
    }
}
