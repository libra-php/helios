<?php

namespace App\Controllers\Module;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[Group(prefix: "/admin/blog/comments", middleware: ["module" => "blog-comments"])]
class BlogCommentsModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->roles = ["Super Admin", "Admin"];
        $this->table = "blog_post_comments";
        $this->module_title = "Comments";
        $this->link_parent = "Blog";
        $this->table_columns = [
            "Blog Post" => "(SELECT title FROM blog_posts WHERE id = blog_post_id) blog_post",
            "Name" => "name",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];

        $this->form_columns = [
            "Name" => "name",
            "Comment" => "comment",
            "Approved" => "approved",
        ];
        $this->form_controls = [
            "name" => "input",
            "comment" => "textarea",
            "approved" => "switch",
        ];
        $this->validation_rules = [
            "approved" => [],
        ];
    }
}
