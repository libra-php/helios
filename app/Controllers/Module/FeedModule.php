<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/feed", middleware: ["module" => "feed"])]
class FeedModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->enabled = false;
        $this->table = "feed_posts";
        $this->module_title = "Feed";
        $this->link_parent = "Activity";
    }
}
