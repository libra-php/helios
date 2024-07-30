<?php

namespace Helios\Web;

use Twig\Environment;

class Controller
{
    /**
    * Render a twig template
    */
    function render(string $path, array $data = [])
    {
        $twig = container()->get(Environment::class);
        return $twig->render($path, $data);
    }
}
