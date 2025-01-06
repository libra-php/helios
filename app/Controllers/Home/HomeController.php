<?php

namespace App\Controllers\Home;

use Helios\Web\Controller;
use StellarRouter\Get;

class HomeController extends Controller
{
    #[Get("/", "home.index")]
    public function index(): string
    {
        return $this->render("home/index.html");
    }
}
