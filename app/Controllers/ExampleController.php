<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class ExampleController extends Controller
{
    #[Get("/", "example.index")]
    public function index(): string
    {
        return $this->render("example/index.html");
    }
}
