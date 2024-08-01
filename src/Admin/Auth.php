<?php

namespace Helios\Admin;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class Auth
{
    /**
     * Get currently authenticated user
     */
    public static function user(): ?User
    {
        $user_id = session()->get("user_id");
        $user = User::find($user_id);
        return $user;
    }

    /**
     * Authenticate a user by email and password
     */
    public static function authenticateUser(string $email, string $password): bool
    {
        $user = User::findByAttribute("email", $email);
        $result = $user && password_verify($password, $user->password);
        if ($result) {
            session()->set("user_id", $user->id);
        }
        return $result;
    }

    /**
     * Sign out a user by destroying the session
     */
    public static function signOut()
    {
        session()->delete("user_id");
        session()->destroy();
    }

    /**
     * Securely hash an application password
     */
    public static function hashPassword(string $password): string|bool|null
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    /**
     * Get a Google2FA secret key
     */
    public static function google2FASecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }
}
