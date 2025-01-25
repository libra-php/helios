<?php

namespace App\Services;

class CaptchaService
{
    public function getCaptcha(string $key = 'captcha')
    {
        return session()->get($key);
    }

    public function setCaptcha(string $key = 'captcha'): int
    {
        $captcha = rand(1000, 9999);
        session()->set($key, $captcha);
        return $captcha;
    }
}
