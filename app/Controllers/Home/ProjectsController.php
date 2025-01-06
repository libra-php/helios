<?php

namespace App\Controllers\Home;

use Helios\Web\Controller;
use StellarRouter\Get;

class ProjectsController extends Controller
{
    #[Get("/projects", "projects.index")]
    public function index(): string
    {
        return $this->render("home/projects.html");
    }
}
