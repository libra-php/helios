<?php

namespace App\Controllers\Auth;

use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ForgotPasswordController extends Controller
{
    #[Get("/forgot-password", "forgot-password.index")]
    public function index(): string
    {
        return $this->render("admin/forgot-password/index.html");
    }

    #[Post("/forgot-password", "forgot-password.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            'email' => ['required'],
        ]);

        if ($valid) {
        }

        return $this->index();
    }
}
