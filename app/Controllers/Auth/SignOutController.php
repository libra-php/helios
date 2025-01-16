<?php

namespace App\Controllers\Auth;

use App\Services\AuthService;
use Helios\Web\Controller;
use StellarRouter\Get;

class SignOutController extends Controller
{
    public function __construct(private AuthService $service)
    {
    }

    #[Get("/sign-out", "sign-out.index")]
    public function index(): void
    {
        $this->service->signOut();
        redirect(findRoute("sign-in.index"));
    }
}
