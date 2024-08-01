<?php

namespace Helios\Admin;

use App\Models\User;
use PragmaRX\Google2FA\Google2FA;

class Auth
{
    public static function authenticateUser(string $email, string $password): bool
    {
        $user = User::findByAttribute("email", $email);
        return $user && password_verify($password, $user->password);
    }

    public static function hashPassword(string $password): string|bool|null
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public static function google2FASecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }
}
