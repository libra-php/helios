<?php

namespace App\Controllers\Auth;

use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ForgotPasswordController extends Controller
{
    #[Get("/forgot-password", "forgot-password.index")]
    public function index(): string
    {
        return $this->render("admin/forgot-password/index.html");
    }

    #[Post("/forgot-password", "forgot-password.post", ['forgot-pass'])]
    public function post(): string
    {
        $valid = $this->validateRequest([
            'email' => ['required', 'email'],
        ]);

        if ($valid) {
            Auth::requestPasswordReset($valid);
        }

        return $this->index();
    }
}

