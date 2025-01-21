<?php

namespace App\Controllers\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use App\Services\AuthService;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class PasswordResetController extends Controller
{
    public function __construct(private AuthService $service) {}

    #[Get("/password-reset/{token}", "password-reset.index")]
    public function index(string $token): string
    {
        $this->service->validatePasswordResetToken($token);

        return $this->render("admin/password-reset/index.html", [
            "token" => $token,
        ]);
    }

    #[Post("/password-reset/{token}", "password-reset.post")]
    public function post(string $token): string
    {
        $password_reset = $this->service->validatePasswordResetToken($token);

        $valid = $this->validateRequest([
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

        if ($valid) {
            $this->service->changePassword($password_reset, $valid->password);
            $this->service->logUser($password_reset->user());
            $this->service->redirectPasswordReset();
        }

        return $this->index($token);
    }
}
