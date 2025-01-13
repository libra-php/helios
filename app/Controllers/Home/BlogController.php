<?php

namespace App\Controllers\Home;

use App\Models\BlogPost;
use App\Models\BlogPostComment;
use App\Services\BlogService;
use Helios\Web\Controller;
use StellarRouter\{Group, Get, Post};

#[Group(prefix: "/blog")]
class BlogController extends Controller
{
    private BlogService $blog_service;
    public function __construct()
    {
        $this->blog_service = new BlogService; 
    }

    #[Get("/", "blog.index")]
    public function index(): string
    {
        return $this->render("home/blog/index.html", [
            "posts" => $this->blog_service->getBlogPosts(),
        ]);
    }

    #[Get("/{slug}", "blog.index")]
    public function post(string $slug): string
    {
        $post = $this->blog_service->getBlogPostBySlug($slug);

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post['slug']}");

        return $this->render("home/blog/post.html", [
            "post" => $post,
        ]);
    }

    #[Post("/comment/{id}", "blog.comment")]
    public function comment(int $id): string
    {
        $post = BlogPost::findOrFail($id);

        $valid = $this->validateRequest([
            "name" => ["required"],
            "comment" => ["required"],
        ]);

        if ($valid) {
            BlogPostComment::create([
                "blog_post_id" => $post->id,
                "name" => $valid->name,
                "comment" => $valid->comment,
                "ip" => ip2long(getClientIp()),
                "approved" => false, // Posts will be approved in the backend
            ]);
        }

        return $this->post($post->slug);
    }

    #[Get("/comments/{id}", "blog.comments")]
    public function comments(int $id): string
    {
        return $this->render("home/blog/comments.html", [
            "comments" => $this->blog_service->getBlogPostComments($id)
        ]);
    }
}
