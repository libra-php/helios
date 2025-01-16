<?php

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ForgotPasswordController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    #[Get("/forgot-password", "forgot-password.index")]
    public function index(): string
    {
        return $this->render("admin/forgot-password/index.html");
    }

    #[Post("/forgot-password", "forgot-password.post", ["forgot-pass"])]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "email" => ["required", "email"],
        ]);

        if ($valid) {
            $this->service->requestPasswordReset($valid);
        }

        return $this->index();
    }
}
