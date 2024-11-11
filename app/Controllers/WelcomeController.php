<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class WelcomeController extends Controller
{
    #[Get("/welcome", "welcome.index")]
    public function index(): string
    {
        return $this->render("welcome.html");
    }

    #[Get("/test", "welcome.test", ["api"])]
    public function test(): int
    {
        return 42;
    }
}
