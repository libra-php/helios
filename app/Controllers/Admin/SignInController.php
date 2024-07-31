<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
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
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if ($valid) {
            die('wip: good job');
        }

        return $this->index();
    }
}
