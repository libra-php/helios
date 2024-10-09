<?php

namespace App\Modules;

use Helios\Module\Module;
use Helios\View\Feed as FeedView;
use Helios\View\IView;

class Feed extends Module
{
    public function getCustomData(): array
    {
        return [
            "avatar_url" => user()->gravatar(40),
        ];
    }

    public function view(IView $view, ?int $id = null): string
    {
        $view = new FeedView;
        return parent::view($view);
    }
}
