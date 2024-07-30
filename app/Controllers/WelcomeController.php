<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class WelcomeController extends Controller
{
    #[Get("/", "welcome.index")]
    public function index()
    {
        return render("welcome.twig");
    }
}
