<?php

namespace App\Controllers\Module\Blog;

use App\Models\BlogPost;
use Helios\Admin\ModuleController;
use StellarRouter\Group;

#[Group(prefix: "/admin/blog/posts", middleware: ["module" => "blog-posts"])]
class PostsModule extends ModuleController
{
    public function init(?int $id): void
    {
        $this->roles = ["Super Admin", "Admin"];
        $this->table = "blog_posts";
        $this->module_title = "Posts";
        $this->link_parent = "Blog";
        $this->table_columns = [
            "ID" => "id",
            "Author" => "(SELECT users.username 
                FROM users 
                WHERE users.id = blog_posts.user_id) as author",
            "Category" => "(SELECT name 
                FROM blog_categories 
                WHERE blog_categories.id = category_id) as category",
            "Title" => "title",
            "Created" => "created_at",
            "Updated" => "updated_at",
        ];
        $this->table_format = [
            "created_at" => "ago",
            "updated_at" => "ago",
        ];
        $this->filter_links = [
            "Draft" => "status_id=2",
            "Published" => "status_id=3",
            "Archived" => "status_id=1",
        ];
        $this->searchable = ["title", "subtitle"];

        $this->form_columns = [
            "Status" => "status_id",
            "Category" => "category_id",
            "Publish At" => "publish_at",
            "Comments Enabled" => "comments_enabled",
            "Title" => "title",
            "Subtitle" => "subtitle",
            "Slug" => "slug",
            "Cover Image" => "cover_image",
            "Content" => "content",
        ];
        $this->form_controls = [
            "publish_at" => "datetime",
            "status_id" => "select",
            "cover_image" => "image",
            "comments_enabled" => "switch",
            "title" => "input",
            "subtitle" => "input",
            "slug" => "input",
            "category_id" => "select",
            "content" => "editor",
        ];
        $this->dropdown_queries = [
            "status_id" => "SELECT id as value, name as label 
                FROM blog_post_status 
                ORDER BY name",
            "category_id" => "SELECT id as value, name as label 
                FROM blog_categories 
                ORDER BY name",
        ];
        $this->default_sort = "DESC";
        $this->default_values = [
            "comments_enabled" => 1,
            "category_id" => 1,
            "status_id" => 2,
        ];
        $this->validation_rules = [
            "status_id" => ["required"],
            "cover_image" => [],
            "title" => ["required"],
            "subtitle" => ["required"],
            "slug" => ["required", "regex:=^[a-z0-9]+(?:-[a-z0-9]+)*$"],
            "category_id" => ["required"],
            "content" => [],
            "publish_at" => [],
        ];

        $this->addErrorMessage(
            "slug.regex",
            "The slug must be a lowercase string containing only letters, numbers, and hyphens. It should not start or end with a hyphen, and consecutive hyphens are not allowed."
        );
        $this->addErrorMessage("slug", "Slug already exists");

        if ($id) {
            // Edit
            $this->validation_rules["slug"][] = function ($value) use ($id) {
                $post = BlogPost::find($id);
                if ($post && $post->slug === $value) {
                    return true;
                }

                $post = BlogPost::where("slug", $value)->get(1);
                return !$post;
            };
        } else {
            // Create
            $this->validation_rules["slug"][] = "unique:=blog_posts";
        }
    }

    protected function new(array $data): ?int
    {
        $data["user_id"] = user()->id;
        return parent::new($data);
    }
}
