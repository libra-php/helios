<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/recipes", middleware: ["module" => "recipes"])]
class RecipeModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->enabled = false;
        $this->table = "feed_posts";
        $this->module_title = "Recipes";
        $this->link_parent = "Culinary Arts";
    }
}
