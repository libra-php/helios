<?php

namespace Helios\Admin;

use App\Models\EmailJob;
use App\Models\PasswordReset;
use App\Models\User;
use Helios\View\Flash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\Carbon;

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

    public static function generatePasswordToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function generateTwoFactorSecret(): string
    {
        $google2fa = new Google2FA();
        return $google2fa->generateSecretKey();
    }

    public static function generateTwoFactorQR(User $user): string
    {
        // Generate a 2FA QR 
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

    public static function testTwoFactorCode(User $user, string $code): bool
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
                $now = Carbon::now();
                $expires_at = Carbon::parse($user->locked_until);
                $minutes = ceil($now->diffInMinutes($expires_at));
                Flash::add("warning", "This account is temporarily locked. Please try again in $minutes minute(s).");
                return false;
            }

            return $result ? true : false;
        }
    }

    public static function requestPasswordReset(object $request): void
    {
        // Look for user by email
        $user = User::where("email", $request->email)->get(1);

        // Locked users cannot request password reset
        if ($user && !$user->locked_until) {
            // Check if there is a password reset in progress
            $password_reset = PasswordReset::where("user_id", $user->id)
                ->andWhere("expires_at", ">", date("Y-m-d H:i:s"))
                ->orderBy("id", "DESC")
                ->get(1);
            // If not, or the prev attempt did not send,
            // then send it!
            if (!$password_reset || !$password_reset->email_job_id) {
                self::passwordReset($user);
            } else {
                // There is already a valid password reset link, 
                // avoid sending any new mail
                $now = Carbon::now();
                $expires_at = Carbon::parse($password_reset->expires_at);
                $minutes = ceil($now->diffInMinutes($expires_at));
                Flash::add("success", "Please check your email inbox for instructions on how to reset your password. This link is valid for $minutes minute(s).");
                return;
            }
        }
        Flash::add("success", "If the email exists, a password reset link has been sent.");
    }

    public static function registerUser(object $request): User
    {
        unset($request->password_match);
        $request->two_fa_secret = self::generateTwoFactorSecret();
        $request->password = self::hashPassword($request->password);
        return User::create((array) $request);
    }

    public static function changePassword(User $user, string $password)
    {
        $user->password = self::hashPassword($password);
        $user->two_fa_secret = self::generateTwoFactorSecret();
        $user->two_fa_confirmed = 0;
        $user->save();
    }

    public static function passwordReset(User $user): void
    {
        $reset_token_time = config("security.reset_token_time");
        $expires_at = time() + $reset_token_time;
        $ip = getClientIp();
        $token = self::generatePasswordToken();
        // Create a password reset for the user
        $password_reset = PasswordReset::create([
            "user_id" => $user->id,
            "token" => $token,
            "ip" => ip2long($ip),
            "expires_at" => date("Y-m-d H:i:s", $expires_at),
        ]);
        if ($password_reset) {
            $email_job = self::emailPasswordReset($user, $token);
            if ($email_job) {
                // Record that it was sent successfully
                $password_reset->email_job_id = $email_job->id;
                $password_reset->save();
            }
        }
    }

    public static function emailPasswordReset(User $user, string $token)
    {
        $project_name = config("app.name");
        $project_url = config("app.url");
        $route = findRoute("password-reset.index", $token);
        $subject = $project_name . ": Password Reset Request";
        $body = template("admin/forgot-password/email/password-reset.html", [
            "to" => $user->name,
            "ip" => getClientIp(),
            "password_reset_url" => $project_url . $route,
            "from" => $project_name,
        ]);
        return EmailJob::create([
            "tag" => "password_reset",
            "subject" => $subject,
            "body" => $body,
            "to_address" => $user->email,
            "send_at" => date("Y-m-d H:i:s"),
        ]);
    }

    public static function confirm2FA(User $user): void
    {
        // Confirm the two fa code
        session()->set("two_factor_confirmed", true);
        $user->two_fa_confirmed = 1;
        $user->save();
    }

    public static function logUser(User $user, bool $remember_me = false): void
    {
        // Set user login_at
        $user->login_at = date("Y-m-d H:i:s");
        $user->login_ip = ip2long(getClientIp());
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
}
