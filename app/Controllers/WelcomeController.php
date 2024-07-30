<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class WelcomeController extends Controller
{
    #[Get("/", "welcome.index")]
    public function index()
    {
        return $this->render("welcome.html");
    }

    #[Get("/test", "welcome.test", ["api"])]
    public function test()
    {
        return 42;
    }
}
