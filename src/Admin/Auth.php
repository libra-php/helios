<?php

namespace Helios\Admin;

use App\Models\User;

class Auth
{
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
        if ($user && self::testPassword($request->password, $user->password)) {
            // Set either the cookie or session
            if ($request->remember_me) {
                $future_time = time() + 86400 * 30;
                setcookie("user_uuid", $user->uuid, $future_time, "/");
            } else {
                session()->set("user_uuid", $user->uuid);
            }
            return true;
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
