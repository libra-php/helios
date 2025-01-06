<?php

namespace App\Controllers\Home;

use App\Models\BlogPost;
use Helios\Web\Controller;
use StellarRouter\Get;
use Carbon\Carbon;


class BlogController extends Controller
{
    #[Get("/blog", "blog.index")]
    public function index(): string
    {
        $posts = BlogPost::where("status_id", 3)
            ->orderBy("created_at", "DESC")
            ->get(lazy: false);
        return $this->render("home/blog.html", [
            "posts" => array_map(fn($post) => [
                "cover" => $post->coverImage()?->name,
                "category" => $post->category()->name,
                "title" => $post->title,
                "subtitle" => $post->subtitle,
                "slug" => $post->slug,
                "content" => $post->content,
                "created_at" => $post->created_at,
                "updated_at" => $post->updated_at,
                "ago" => Carbon::parse($post->updated_at ?? $post->created_at)->diffForHumans(),
            ], $posts),
        ]);
    }

    #[Get("/blog/{slug}", "blog.index")]
    public function post(string $slug): string
    {
        $post = BlogPost::where("slug", $slug)->get();

        if (!$post) {
            redirect("/page-not-found");
        }

        header("HX-Push-Url: /blog/{$post->slug}");

        return $this->render("home/blog-post.html", [
            "post" => [
                "cover" => $post->coverImage()?->name,
                "category" => $post->category()->name,
                "title" => $post->title,
                "subtitle" => $post->subtitle,
                "slug" => $post->slug,
                "content" => $post->content,
                "created_at" => $post->created_at,
                "updated_at" => $post->updated_at,
                "ago" => Carbon::parse($post->updated_at ?? $post->created_at)->diffForHumans(),
            ]
        ]);
    }
}
