<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/blog-categories", middleware: ["module" => "blog-categories"])]
class BlogCategoriesModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->table = "blog_categories";
        $this->module_title = "Categories";
        $this->link_parent = "Blog";
        $this->table_columns = [
            "Name" => "name",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
            "updated_at" => "ago",
        ];

        $this->form_columns = [
            "Name" => "name",
        ];
        $this->form_controls = [
            "name" => "input",
        ];
        $this->validation_rules = [
            "name" => ["required"],
        ];
    }

    public function hasDeletePermission(int $id): bool
    {
        return $id != 1 && parent::hasDeletePermission($id);
    }
}
