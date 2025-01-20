<?php

namespace App\Controllers\Module\Blog;

use Helios\Admin\ModuleController;
use StellarRouter\Group;

/** @package App\Controllers\Module */
#[
    Group(
        prefix: "/admin/blog/images",
        middleware: ["module" => "blog-images"]
    )
]
class ImagesModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->roles = ["Super Admin", "Admin"];
        $this->table = "blog_post_images";
        $this->module_title = "Images";
        $this->link_parent = "Blog";
        $this->table_columns = [
            "ID" => "id",
            "Blog Post" => "(SELECT CONCAT(LEFT(title, 5), '...') FROM blog_posts WHERE blog_posts.id = blog_post_id) as blog_post",
            "Image" => "(SELECT name FROM files where files.id = image) as image",
            "Created" => "created_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
        ];

        $this->form_columns = [
            "Blog Post" => "blog_post_id",
            "Image" => "image",
            "Caption" => "caption",
        ];
        $this->form_controls = [
            "blog_post_id" => "select",
            "image" => "image",
            "caption" => "input",
        ];
        $this->dropdown_queries = [
            "blog_post_id" => "SELECT id as value, concat(created_at, ' - ', title) as label FROM blog_posts ORDER BY title",
        ];
        $this->validation_rules = [
            "blog_post_id" => ["required"],
            "image" => ["required"],
            "caption" => [],
        ];
    }
}
