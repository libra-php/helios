<?php

namespace App\Controllers\Home;

use App\Models\EmailJob;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ContactController extends Controller
{
    #[Get("/contact", "contact.index")]
    public function index(): string
    {
        setCaptcha();
        return $this->render("home/contact.html");
    }

    #[Post("/contact", "contact.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "email" => ["required", "email"],
            "message" => ["required", "min_length:=10"],
            "captcha" => ["required"],
        ]);

        if ($valid) {
            $captcha_success = $valid->captcha == getCaptcha();
            if (!$captcha_success) {
                Flash::add("warning", "Invalid captcha code. Please try again.");
            } else {
                Flash::add("success", "Thank you for your message! I’ll get back to you as soon as possible!");
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
        }
        return $this->index();
    }
}
