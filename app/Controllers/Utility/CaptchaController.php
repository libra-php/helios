<?php

namespace App\Controllers\Utility;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/captcha")]
class CaptchaController extends Controller
{
    #[Get("/", "captcha.index")]
    public function index(): string
    {
        setCaptcha();
        return $this->render("components/captcha.html");
    }

    #[Get("/load", "captcha.captcha")]
    public function captcha()
    {
        $captcha = getCaptcha();
        if (!$captcha) {
            return;
        }

        $im = imagecreatetruecolor(50, 24);
        $bg = imagecolorallocate($im, 22, 86, 165);
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
