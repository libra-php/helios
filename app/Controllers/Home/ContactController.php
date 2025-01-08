<?php

namespace App\Controllers\Home;

use App\Models\EmailJob;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ContactController extends Controller
{
    public function __construct()
    {
    }

    #[Get("/contact/captcha", "contact.captcha")]
    public function captcha()
    {
        $captcha = session()->get("captcha");
        if (!$captcha) return;

        // 50x24 standard captcha image
        $im = imagecreatetruecolor(50, 24);  

        $bg = imagecolorallocate($im, 22, 86, 165);
        $fg = imagecolorallocate($im, 255, 255, 255);
        imagefill($im, 0, 0, $bg); 

        // Print the captcha text in the image 
        // with random position & size
        imagestring($im, rand(1, 7), rand(1, 7),
            rand(1, 7),  $captcha, $fg);

        header("Cache-Control: no-store,
            no-cache, must-revalidate"); 
        header('Content-type: image/png');

        // Output the captcha as png
        imagepng($im); 
        imagedestroy($im);
    }

    #[Get("/contact", "contact.index")]
    public function index($success = false): string
    {
        // Random number 1000-9999
        $captcha = rand(1000, 9999);
        session()->set("captcha", $captcha);
        return $this->render("home/contact.html", [
            "success" => $success
        ]);
    }

    #[Post("/contact", "contact.post")]
    public function post(): string
    {
        $captcha = session()->get("captcha");
        $valid = $this->validateRequest([
            "email" => ["required", "email"],
            "message" => ["required", "min_length:=10"],
            "captcha" => ["required"],
        ]);
        $captcha_success = $valid->captcha == $captcha;
        if (!$captcha_success) {
            $this->addRequestError("captcha", "Invalid captcha code. Please try again.");
        }
        if ($valid && $captcha_success) {
            EmailJob::create([
                "tag" => "home_contact",
                "subject" => "Direct message",
                "body" => template("home/email/contact.html", [
                    "email" => $valid->email,
                    "message" => nl2br($valid->message),
                ]),
                "to_address" => "william.hleucka@gmail.com",
                "send_at" => date("Y-m-d H:i:s"),
            ]);
        }
        return $this->index($valid && $captcha_success);
    }
}
