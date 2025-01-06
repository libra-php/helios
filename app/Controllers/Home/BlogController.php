<?php

namespace App\Controllers\Home;

use App\Services\BlogService;
use Helios\Web\Controller;
use StellarRouter\Get;


class BlogController extends Controller
{
    private BlogService $blog_service;
    public function __construct()
    {
        $this->blog_service = new BlogService; 
    }

    #[Get("/blog", "blog.index")]
    public function index(): string
    {
        return $this->render("home/blog.html", [
            "posts" => $this->blog_service->getBlogPosts(),
        ]);
    }

    #[Get("/blog/{slug}", "blog.index")]
    public function post(string $slug): string
    {
        $post = $this->blog_service->getBlogPostBySlug($slug);

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post['slug']}");

        return $this->render("home/blog-post.html", [
            "post" => $post
        ]);
    }
}
