<?php

namespace App\Controllers\Home;

use App\Models\BlogPost;
use App\Models\EmailJob;
use App\Services\BlogService;
use App\Services\CaptchaService;
use Helios\View\Flash;
use Helios\Web\Controller;
use StellarRouter\{Group, Get, Post};

#[Group(prefix: "/blog")]
class BlogController extends Controller
{
    public function __construct(private BlogService $blog_service, private CaptchaService $captcha_service) {}

    #[Get("/", "blog.index")]
    public function index(): string
    {
        return $this->render("home/blog/index.html", [
            "posts" => $this->blog_service->getBlogPosts(),
        ]);
    }

    #[Get("/{slug}", "blog.show")]
    public function show(string $slug): string
    {
        $post = $this->blog_service->getPublishedBlogPostBySlug($slug);

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post["slug"]}");

        return $this->render("home/blog/post.html", [
            "post" => $post,
        ]);
    }

    #[Get("/preview/{slug}", "blog.preview", ["auth"])]
    public function preview(string $slug): string
    {
        $post = $this->blog_service->getBlogPostBySlug($slug);

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post["slug"]}");

        return $this->render("home/blog/post.html", [
            "post" => $post,
        ]);
    }

    #[Post("/comment/{post_id}", "blog.comment")]
    public function comment(int $post_id): string
    {
        $post = BlogPost::findOrFail($post_id);

        $valid = $this->validateRequest([
            "name" => ["required"],
            "comment" => ["required"],
            "captcha" => [],
        ]);

        if ($valid) {
            $captcha_success = $valid->captcha == $this->captcha_service->getCaptcha();
            if ($captcha_success) {
                $comment = $this->blog_service->createComment(
                    $post->id,
                    trim($valid->name),
                    trim($valid->comment)
                );

                if ($comment) {
                    EmailJob::create([
                        "tag" => "blog_comment",
                        "subject" => "New blog comment",
                        "body" => template("home/email/blog-comment.html", [
                            "name" => $valid->name,
                            "comment" => nl2br(trim($valid->comment)),
                            "url" => config("app.url") . "/blog/" . $post->slug
                        ]),
                        "to_address" => "william.hleucka@gmail.com",
                        "send_at" => date("Y-m-d H:i:s"),
                    ]);

                    $comment_count = $this->blog_service->getBlogPostCommentCount($post_id);
                    if ($comment_count > 1) {
                        trigger("load-comment-control");
                    } else {
                        trigger("load-comment-control, load-comments");
                    }

                    return $this->render("home/blog/comment.html", [
                        "comment" => $comment,
                    ]);
                }
            }
        }
        Flash::add("warning", "Invalid captcha code. Please try again.");
        trigger("load-comment-control");
    }

    #[Get("/images/{post_id}", "blog.comments")]
    public function images(int $post_id): string
    {
        return $this->render("home/blog/images.html", [
            "post_id" => $post_id,
            "images" => $this->blog_service->getBlogPostImages($post_id),
        ]);
    }

    #[Get("/comments/{post_id}", "blog.comments")]
    public function comments(int $post_id): string
    {
        return $this->render("home/blog/comments.html", [
            "post_id" => $post_id,
            "comments" => $this->blog_service->getBlogPostComments($post_id),
        ]);
    }

    #[Get("/comment/control/{post_id}", "blog.comments-control")]
    public function comments_control(int $post_id): string
    {
        return $this->render("home/blog/comment-control.html", [
            "id" => $post_id
        ]);
    }

    #[Get("/comment/ts/{comment_id}", "blog.update-ts")]
    public function update_ts(int $comment_id): string
    {
        return $this->blog_service->getCommentTimestamp($comment_id);
    }
}
