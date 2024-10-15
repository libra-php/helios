<?php

namespace App\Modules;

use Helios\Module\Module;
use Helios\View\Post as PostView;
use Helios\View\Feed as FeedView;
use Helios\View\IView;

class Feed extends Module
{
    public function __construct()
    {
        $this->form('custom', true);
        $this->has_edit = true;
    }

    public function getCustomData(): array
    {
        return [
            "avatar_url" => user()->gravatar(40),
        ];
    }

    public function view(IView $view, ?int $id = null): string
    {
        if ($id) {
            $this->id = $id;
            $view = new PostView;
        } else {
            $view = new FeedView;
        }
        return parent::view($view);
    }
}
