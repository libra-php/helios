<?php

namespace App\Controllers\Auth;

use Helios\Web\Controller;
use StellarRouter\{Get, Post};

class RegisterController extends Controller
{
    public function __construct()
    {
        if (!config("security.register_enabled")) {
            redirect(config("security.auth_route"));
        }
    }

    #[Get("/register", "register.index")]
    public function index()
    {
        return $this->render("admin/register/index.html");
    }

    #[Post("/register", "register.post")]
    public function post()
    {
        dump("WIP!");
        return $this->index();
    }
}
