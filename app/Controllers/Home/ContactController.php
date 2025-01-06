<?php

namespace App\Controllers\Home;

use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class ContactController extends Controller
{
    #[Get("/contact", "contact.index")]
    public function index(): string
    {
        return $this->render("home/contact.html");
    }

    #[Post("/contact", "contact.post")]
    public function post(): string
    {
        return $this->index();
    }
}
