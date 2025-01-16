<?php

namespace App\Controllers\Utility;

use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/captcha")]
class CaptchaController extends Controller
{
    #[Get("/", "captcha.index", ["captcha"])]
    public function captcha()
    {
        $captcha = getCaptcha();
        if (!$captcha) {
            return;
        }

        // 50x24 standard captcha image
        $im = imagecreatetruecolor(50, 24);

        $bg = imagecolorallocate($im, 22, 86, 165);
        $fg = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $bg);

        // Print the captcha text in the image
        // with random position & size
        imagestring($im, rand(1, 7), rand(1, 7), rand(1, 7), $captcha, $fg);

        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Content-type: image/png");

        // Output the captcha as png
        imagepng($im);
        imagedestroy($im);
    }
}

