<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class HomeController extends Controller
{
    #[Get("/", "home.index")]
    public function index(): string
    {
        return $this->render("home/index.html");
    }

    #[Get("/projects", "home.projects")]
    public function projects(): string
    {
        return $this->render("home/projects.html");
    }

    #[Get("/about", "home.about")]
    public function about(): string
    {
        return $this->render("home/about.html");
    }

    #[Get("/blog", "home.blog")]
    public function blog(): string
    {
        return $this->render("home/blog.html");
    }

    #[Get("/contact", "home.contact")]
    public function contact(): string
    {
        return $this->render("home/contact.html");
    }
}
