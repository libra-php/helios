<?php

namespace App\Services;

use App\Models\BlogPost;
use App\Models\BlogPostComment;
use App\Models\BlogPostImages;
use Carbon\Carbon;

class BlogService
{
    public function getBlogPosts(): ?array
    {
        $posts = BlogPost::where("status_id", 3) // published
            ->orderBy("created_at", "DESC")
            ->get(lazy: false);

        if (!$posts) {
            return null;
        }

        return array_map(
            fn($post) => [
                "id" => $post->id,
                "author" => $post->user()->name,
                "category" => $post->category()->name,
                "title" => $post->title,
                "subtitle" => $post->subtitle,
                "slug" => $post->slug,
                "content" => $post->content,
                "read_min" => $this->estimateReadingTime($post->content),
                "created_at" => date('Y-m-d', strtotime($post->created_at)),
                "ago" => Carbon::parse(
                    $post->updated_at ?? $post->created_at
                )->diffForHumans(),
            ],
            $posts
        );
    }

    public function estimateReadingTime(
        string $content,
        int $words_per_minute = 200
    ) {
        $plain_text = strip_tags(html_entity_decode($content));
        $word_count = str_word_count($plain_text);
        $reading_time = ceil($word_count / $words_per_minute);
        return $reading_time;
    }

    public function getPublishedBlogPostBySlug(string $slug): ?array
    {
        $post = $this->getBlogPostBySlug($slug);

        if (!$post || $post['status'] != 'Published') {
            return null;
        }

        return $post;
    }

    public function getBlogPostBySlug(string $slug): ?array
    {
        $post = BlogPost::where("slug", $slug)->get();

        if (!$post) {
            return null;
        }

        $cover = $post->coverImage()?->name;

        return [
            "id" => $post->id,
            "status" => $post->status()->name,
            "author" => $post->user()->name,
            "cover" => $cover ? "/uploads/$cover" : null,
            "category" => $post->category()->name,
            "title" => $post->title,
            "subtitle" => $post->subtitle,
            "slug" => $post->slug,
            "content" => $post->content,
            "read_min" => $this->estimateReadingTime($post->content),
            "created_at" => date('Y-m-d', strtotime($post->created_at)),
            "ago" => Carbon::parse(
                $post->updated_at ?? $post->created_at
            )->diffForHumans(),
            "comments_enabled" => $post->comments_enabled == 1,
        ];
    }

    public function getBlogPostImages(int $blog_post_id): ?array
    {
        $images = BlogPostImages::where("blog_post_id", $blog_post_id)
            ->get(lazy: false);

        if (!$images) {
            return null;
        }

        return array_map(fn($image) => [
            "id" => $image->id,
            "thumbnail" => "/image/resize/80/80/{$image->file()->uuid}",
            "image" => $image->image(),
            "caption" => $image->caption,
        ], $images);
    }

    public function getBlogPostComments(int $blog_post_id): ?array
    {
        $comments = BlogPostComment::where("blog_post_id", $blog_post_id)
            ->andWhere("approved", 1)
            ->orderBy("created_at", "DESC")
            ->get(lazy: false);

        if (!$comments) {
            return null;
        }

        return array_map(
            fn($comment) => [
                "id" => $comment->id,
                "name" => $comment->name,
                "comment" => $comment->comment,
                "ago" => Carbon::parse($comment->created_at)->diffForHumans(),
                "created_at" => date('Y-m-d', strtotime($comment->created_at)),
                "update_ts" => $comment->created_at > date("Y-m-d H:i:s", strtotime("-1 minute"))
            ],
            $comments
        );
    }

    public function createComment(
        int $blog_post_id,
        string $name,
        string $comment
    ): ?array {
        $comment = BlogPostComment::create([
            "blog_post_id" => $blog_post_id,
            "name" => $name,
            "comment" => nl2br($comment),
            "ip" => ip2long(getClientIp()),
            "approved" => 1,
        ]);

        if ($comment) {
            return [
                "id" => $comment->id,
                "name" => $comment->name,
                "comment" => $comment->comment,
                "ago" => Carbon::parse($comment->created_at)->diffForHumans(),
                "created_at" => date('Y-m-d', strtotime($comment->created_at)),
                "update_ts" => $comment->created_at > date("Y-m-d H:i:s", strtotime("-1 minute"))
            ];
        }
        return null;
    }

    public function getCommentTimestamp(int $comment_id): string
    {
        $comment = BlogPostComment::find($comment_id);

        if ($comment) {
            return Carbon::parse($comment->created_at)->diffForHumans();
        }
    }

    public function getBlogPostCommentCount(int $post_id): int
    {
        $comments = BlogPostComment::where("blog_post_id", $post_id)
            ->andWhere("approved", 1)
            ->get(lazy: false);
        return count($comments);
    }
}
