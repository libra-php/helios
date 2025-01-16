<?php

namespace App\Controllers\Home;

use App\Models\BlogPost;
use App\Services\BlogService;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Group, Get, Post};

#[Group(prefix: "/blog")]
class BlogController extends Controller
{
    public function __construct(private BlogService $service)
    {
    }

    #[Get("/", "blog.index")]
    public function index(): string
    {
        return $this->render("home/blog/index.html", [
            "posts" => $this->service->getBlogPosts(),
        ]);
    }

    #[Get("/{slug}", "blog.index")]
    public function post(string $slug): string
    {
        setCaptcha();
        $post = $this->service->getBlogPostBySlug($slug);

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post["slug"]}");

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
            "captcha" => [],
        ]);

        if ($valid) {
            $captcha_success = $valid->captcha == getCaptcha();
            if (!$captcha_success) {
                Flash::add(
                    "warning",
                    "Invalid captcha code. Please try again."
                );
            } else {
                Flash::add(
                    "success",
                    "Thank you for sharing your thoughts! Your comment has been successfully posted."
                );
                $this->service->createComment(
                    $post->id,
                    trim($valid->name),
                    trim($valid->comment)
                );
            }
        }

        return $this->post($post->slug);
    }

    #[Get("/comments/{id}", "blog.comments")]
    public function comments(int $id): string
    {
        return $this->render("home/blog/comments.html", [
            "comments" => $this->service->getBlogPostComments($id),
        ]);
    }
}
