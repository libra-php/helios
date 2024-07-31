<?php

namespace App\Controllers\Admin;

use Helios\Web\Controller;
use StellarRouter\{Post, Group};

#[Group(prefix: "/admin")]
class AdminController extends Controller
{
    #[Post("/validate", "admin.validate")]
    function validate()
    {
        return 'hi';
    }
}
