<?php

namespace App\Controllers\Utility;

use App\Services\CaptchaService;
use Helios\Web\Controller;
use StellarRouter\{Get, Group};

#[Group(prefix: "/captcha")]
class CaptchaController extends Controller
{
    public function __construct(private CaptchaService $service) {}

    #[Get("/", "captcha.index")]
    public function index(): string
    {
        $this->service->setCaptcha();
        return $this->render("components/captcha.html");
    }

    #[Get("/load", "captcha.load")]
    public function load()
    {
        $this->service->renderCaptcha();
    }
}
