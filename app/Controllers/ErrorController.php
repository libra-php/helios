<?php

namespace App\Controllers;

use Helios\Web\Controller;
use StellarRouter\Get;

class ErrorController extends Controller
{
    #[Get("/page-not-found", "error.page-not-found")]
    public function pageNotFound()
    {
        return $this->render("errors/page-not-found.html");
    }

    #[Get("/permission-denied", "error.permission-denied")]
    public function permissionDenied()
    {
        return $this->render("errors/permission-denied.html");
    }

    #[Get("/server-error", "error.server-error")]
    public function serverError()
    {
        return $this->render("errors/server-error.html");
    }
}
