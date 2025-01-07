<?php

namespace App\Controllers\Home;

use App\Models\EmailJob;
use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ContactController extends Controller
{
    #[Get("/contact", "contact.index")]
    public function index($success = false): string
    {
        return $this->render("home/contact.html", [
            "success" => $success
        ]);
    }

    #[Post("/contact", "contact.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "email" => ["required", "email"],
            "message" => ["required", "min_length:=10"],
        ]);
        if ($valid) {
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
        return $this->index($valid);
    }
}
