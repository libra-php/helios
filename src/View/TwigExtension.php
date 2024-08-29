<?php

namespace Helios\View;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @class TwigExtension provides functions to twig templates
 */
class TwigExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('csrf', [$this, 'csrf']),
            new TwigFunction('dump', [$this, 'dump']),
            new TwigFunction('route', [$this, 'route']),
            new TwigFunction('old', [$this, 'old']),
        ];
    }

    public function dump(...$args): void
    {
        dump(...$args);
    }
    public function route(string $name, ...$replacements): ?string
    {
        return findRoute($name, ...$replacements);
    }
    public function csrf(): string
    {
        $token = session()->get("csrf_token");
        return template("components/csrf.html", ["token" => $token]);
    }
    public function old(string $name): string
    {
        return request()->request->get($name, "");
    }
}
