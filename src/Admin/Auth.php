<?php

namespace Helios\Admin;

use PragmaRX\Google2FA\Google2FA;

class Auth
{
    public static function hashPassword(string $password): string|bool|null
    {
        return password_hash($password, PASSWORD_ARGON2I);
    }

    public static function generateSecretKey(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }
}
