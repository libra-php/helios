<?php

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class SignInController extends Controller
{
    public function __construct(private AuthService $service) {}

    #[Get("/sign-in", "sign-in.index")]
    public function index(): string
    {
        return $this->render("admin/sign-in/index.html", [
            "register_enabled" => config("security.register_enabled"),
        ]);
    }

    #[Post("/sign-in", "sign-in.post", ["login"])]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "email_or_username" => ["required"],
            "password" => ["required"],
            "remember_me" => [],
        ]);

        if ($valid) {
            if ($this->service->signIn($valid)) {
                $this->service->redirectSignIn();
            }
            $this->addRequestError("password", "Invalid email or password");
        }

        return $this->index();
    }
}
