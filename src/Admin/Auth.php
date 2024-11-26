<?php

namespace Helios\Admin;

use App\Models\User;
use Helios\View\Flash;

class Auth
{
    public static function user(): ?User
    {
        $session_uuid = session()->get("user_uuid");
        $cookie_uuid = request()->cookies->get("user_uuid");

        if ($cookie_uuid || $session_uuid) {
            $user = User::where("uuid", $cookie_uuid ?? $session_uuid)->get(1);
            if ($user) {
                return $user;
            }
        }
        return null;
    }

    public static function testPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public static function signIn(object $request): bool
    {
        // WARN: This request must be validated

        // Look for user by email or username
        $user = User::where("email", $request->email_or_username)
            ->orWhere("username", $request->email_or_username)->get(1);

        // If we find a user, test the password
        if ($user && is_null($user->locked_until) && self::testPassword($request->password, $user->password)) {
            // Set user login_at
            $user->login_at = date("Y-m-d H:i:s");
            $user->save();
            // Set either the cookie or session
            if ($request->remember_me) {
                $future_time = time() + 86400 * 30;
                setcookie("user_uuid", $user->uuid, $future_time, "/");
            } else {
                session()->set("user_uuid", $user->uuid);
            }
            return true;
        } else if ($user) {
            $max_failed_login = config("security.max_failed_login");
            if ($user->failed_login >= $max_failed_login) {
                if (is_null($user->locked_until)) {
                    // Lock the user
                    $lockout_time = config("security.lockout_time");
                    $lockout_future = time() + $lockout_time;
                    $user->locked_until = date("Y-m-d H:i:s", $lockout_future);
                    $user->save();
                } else {
                    $current_date = date("Y-m-d H:i:s");
                    if ($current_date > $user->locked_until) {
                        // Unlock the user
                        $user->failed_login = 0;
                        $user->locked_until = null;
                        $user->save();
                    }
                }
                Flash::add("warning", "This account is temporarily locked. Please try again later.");
            } else {
                // Failed login attempt
                $user->failed_login++;
                $user->save();
            }
        }
        return false;
    }

    public static function signOut(): void
    {
        // Destroy the user session & cookies
        unset($_COOKIE["user_uuid"]);
        setcookie("user_uuid", "", -1, "/");
        session()->delete("user_uuid");
        session()->destroy();
    }

    public static function register(): void {}
}
