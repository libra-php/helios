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

    public function renderCaptcha()
    {
        $captcha = $this->getCaptcha();
        if (!$captcha) {
            return;
        }

        $im = imagecreatetruecolor(50, 24);
        $bg = imagecolorallocate($im, 0, 31, 91);
        $fg = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $bg);

        imagestring($im, rand(1, 7), rand(1, 7), rand(1, 7), $captcha, $fg);

        // Prevent output before headers
        ob_start();

        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
        header("Content-Type: image/png");

        imagepng($im);
        imagedestroy($im);

        // Output image data
        ob_end_flush();
    }
}
