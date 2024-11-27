<?php

namespace Helios\Admin;

use App\Models\User;
use Helios\View\Flash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

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

    public static function generateTwoFactorCode(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public static function generateTwoFactorQR(User $user): string
    {
        $google2fa = new Google2FA();
        $g2faUrl = $google2fa->getQRCodeUrl(
            config("app.name"),
            $user->email,
            $user->two_fa_secret,
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(200),
                new ImagickImageBackEnd()
            )
        );

        return base64_encode($writer->writeString($g2faUrl));
    }

    public static function testTwoFactorCode(User $user, int $code): bool
    {
        $google2fa = new Google2FA();
        $result = $google2fa->verifyKey($user->two_fa_secret, $code);
        $max_failed_login = config("security.max_failed_login");

        if (!$result) {
            // Failed 2FA code
            self::failedAttempt($user);

            if ($user->failed_login >= $max_failed_login) {
                if (is_null($user->locked_until)) {
                    // Bad!
                    self::lockUser($user);
                }
                // Kill the session
                self::signOut();
                // Redirect to sign in
                $route = findRoute("sign-in.index");
                redirect($route, [
                    "target" => "#two-factor-authentication",
                    "select" => "#sign-in",
                    "swap" => "outerHTML",
                ]);
            }
            return false;
        } else {
            // Confirm the 2FA auth and unlock user
            self::confirm2FA($user);
            self::unlockUser($user);
        }

        return $result ? true : false;
    }

    public static function signIn(object $request): bool
    {
        // WARN: This request must be validated

        // Look for user by email or username
        $user = User::where("email", $request->email_or_username)
            ->orWhere("username", $request->email_or_username)->get(1);
        if (!$user) {
            // If we don't find a user, bail
            return false;
        } else {
            // Check the password
            $result = self::testPassword($request->password, $user->password);
            $max_failed_login = config("security.max_failed_login");
            $current_date = date("Y-m-d H:i:s");

            // Check if the user is locked
            if (!is_null($user->locked_until)) {
                // Is it time to unlock?
                if ($current_date > $user->locked_until) {
                    self::unlockUser($user);
                }
            }

            if (!$result) {
                // Bad username/email + password
                self::failedAttempt($user);

                // Check login attempts
                if ($user->failed_login >= $max_failed_login) {
                    if (is_null($user->locked_until)) {
                        // Awww, too bad
                        self::lockUser($user);
                    }
                }
            } elseif (is_null($user->locked_until) && $result) {
                // Authentication successful + unlock user
                $remember_me = $request->remember_me ? true : false;
                self::logUser($user, $remember_me);
                self::unlockUser($user);
            }

            // Set warning message if account is locked
            // regardless of result
            if ($user->locked_until) {
                Flash::add("warning", "This account is temporarily locked. Please try again later.");
                return false;
            }

            return $result ? true : false;
        }
    }

    public static function confirm2FA(User $user): void
    {
        // Confirm the two fa code
        session()->set("two_factor_confirmed", true);
        $user->two_fa_confirmed = 1;
        $user->save();
    }

    public static function logUser(User $user, bool $remember_me): void
    {
        // Set user login_at
        $user->login_at = date("Y-m-d H:i:s");
        $user->save();
        // Set either the cookie or session
        if ($remember_me) {
            $future_time = time() + 86400 * 30;
            setcookie("user_uuid", $user->uuid, $future_time, "/");
        } else {
            session()->set("user_uuid", $user->uuid);
        }
    }

    public static function failedAttempt(User $user): void
    {
        // Failed login attempt
        $user->failed_login++;
        $user->save();
    }

    public static function lockUser(User $user): void
    {
        // Lock the user
        $lockout_time = config("security.lockout_time");
        $lockout_future = time() + $lockout_time;
        $user->locked_until = date("Y-m-d H:i:s", $lockout_future);
        $user->save();
    }

    public static function unlockUser(User $user): void
    {
        // Unlock the user
        $user->failed_login = 0;
        $user->locked_until = null;
        $user->save();
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
