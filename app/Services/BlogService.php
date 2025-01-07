<?php

namespace App\Services;

use App\Models\BlogPost;
use Carbon\Carbon;

class BlogService
{
    public function getBlogPosts(): ?array
    {
        $posts = BlogPost::where("status_id", 3)
            ->orderBy("created_at", "DESC")
            ->get(lazy: false);

        if (!$posts) return null;

        return array_map(fn($post) => [
            "author" => $post->user()->name,
            "cover" => $post->cover_image ? "/uploads/" . $post->coverImage()?->name : '/images/me-full.jpeg',
            "category" => $post->category()->name,
            "title" => $post->title,
            "subtitle" => $post->subtitle,
            "slug" => $post->slug,
            "content" => $post->content,
            "created_at" => $post->created_at,
            "updated_at" => $post->updated_at,
            "ago" => Carbon::parse($post->updated_at ?? $post->created_at)->diffForHumans(),
        ], $posts);
    }

    public function getBlogPostBySlug(string $slug): ?array
    {
        $post = BlogPost::where("slug", $slug)->get();

        if (!$post) return null;

        $cover = $post->coverImage()?->name;

        return [
            "author" => $post->user()->name,
            "cover" => $cover ? "/uploads/$cover" : null,
            "category" => $post->category()->name,
            "title" => $post->title,
            "subtitle" => $post->subtitle,
            "slug" => $post->slug,
            "content" => $post->content,
            "created_at" => $post->created_at,
            "updated_at" => $post->updated_at,
            "ago" => Carbon::parse($post->updated_at ?? $post->created_at)->diffForHumans(),
        ];
    }
}
