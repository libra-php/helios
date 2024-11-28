<?php

namespace App\Controllers\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use Helios\Admin\Auth;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class PasswordResetController extends Controller
{
    #[Get("/password-reset/{token}", "password-reset.index")]
    public function index(string $token): string
    {
        $password_reset = PasswordReset::where("token", $token)
            ->where("expires_at", ">", date("Y-m-d H:i:s"))
            ->orderBy("id", "DESC")
            ->get(1);
        if (!$password_reset) {
            redirect("/permission-denied");
        }

        return $this->render("admin/password-reset/index.html", [
            "token" => $token,
        ]);
    }

    #[Post("/password-reset/{token}", "password-reset.post")]
    public function post(string $token): string
    {
        $password_reset = PasswordReset::where("token", $token)
            ->where("expires_at", ">", date("Y-m-d H:i:s"))
            ->orderBy("id", "DESC")
            ->get(1);
        if ($password_reset) {
            $valid = $this->validateRequest([
                "password" => ["required", "min_length|8", "regex|^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$"],
                "password_match" => ["required", function ($value) {
                    $this->addErrorMessage("password_match", "Passwords must match");
                    return request()->get("password") === $value;
                }],
            ]);
            if ($valid) {
                $user = User::findOrFail($password_reset->user_id);
                Auth::changePassword($user, $valid->password);
                Auth::logUser($user, false);
                $two_factor_enabled = config("security.two_factor_enabled");
                if ($two_factor_enabled) {
                    $route = findRoute("2fa.index");
                    redirect($route, [
                        "target" => "#password-reset",
                        "select" => "#two-factor-authentication",
                        "swap" => "outerHTML",
                    ]);
                } else {
                    $route = moduleRoute("module.index", "users");
                    redirect($route, [
                        "target" => "#password-reset",
                        "select" => "#admin",
                        "swap" => "outerHTML",
                    ]);
                }
            }
        } else {
            redirect("/permission-denied");
        }

        return $this->index($token);
    }
}


