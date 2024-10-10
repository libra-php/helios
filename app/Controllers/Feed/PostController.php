<?php

namespace App\Controllers\Feed;

use App\Models\Like;
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Group};
use App\Models\Post as PostModel;
use App\Models\User;
use Carbon\Carbon;
use PDO;

#[Group(prefix: "/feed/post", middleware: ['auth'])]
class PostController extends Controller
{
    #[Get("/show/{id}", "post.show")]
    public function show($id)
    {
        $post = PostModel::find($id);
        if ($post) {
            $user = user();
            $user->avatar = $user->gravatar(40);

            $post_user = User::find($post->user_id);
            $post_user->avatar = $post_user->gravatar(40);
            $post->user = $post_user;
            return $this->render("/admin/feed/show.html", [
                "post" => $post,
                "user" => $user,
            ]); 
        }
        http_response_code(404);
        header("HTTP/1.0 404 Not Found");
        die;
    }

    #[Get("/ago/{id}", "feed.post-ago")]
    public function postAgo($id)
    {
        $post = PostModel::find($id);
        if ($post) {
            $ago = Carbon::parse($post->created_at)->diffForHumans();
            return $ago;
        }
        http_response_code(404);
        header("HTTP/1.0 404 Not Found");
        die;
    }

    #[Get("/like-button/{id}", "feed.like-button")]
    public function likeButton($id)
    {
        $count = Like::search(["count(*)"])
            ->where(["post_id = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        return $this->render("/admin/feed/like-button.html", [
            "id" => $id,
            "count" => $count,
        ]);

    }

    #[Post("/like/{id}", "feed.post-like")]
    public function like($id)
    {
        $user = user();
        // Check if the user has liked this already
        $exists = Like::search(["1"])
            ->where(["user_id = ? AND post_id = ?"], $user->id, $id)
            ->execute()
            ->fetch();

        if ($exists) {
            // TODO: model delete
            db()->query("DELETE 
                FROM likes 
                WHERE post_id = ? 
                AND user_id = ?", $id, $user->id);
        } else {
            Like::new([
                "user_id" => user()->id,
                "post_id" => $id,
            ]);
        }

        trigger("likeButton");
    }

    #[Get("/comment-button/{id}", "feed.comment-button")]
    public function commentButton($id)
    {
        $count = PostModel::search(["count(*)"])
            ->where(["parent_id = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        return $this->render("/admin/feed/comment-button.html", [
            "id" => $id,
            "count" => $count,
        ]);
    }

    #[Post("/comment/{id}", "feed.post-comment")]
    public function comment($id)
    {
        $valid = $this->validateRequest([
            "body" => ["required", "min_length|1"],
        ]);
        if ($valid) {
            if (trim($valid->body)) {
                $user = user();
                $post = PostModel::new([
                    "user_id" => $user->id,
                    "parent_id" => $id,
                    "body" => $valid->body,
                ]);
                $post->user = $user;
                $post->user->avatar = $user->gravatar(40);
                return $this->render("admin/feed/post.html", [
                    "post" => $post,
                    "user" => $user,
                ]);
            }
        }
        trigger("commentButton");
    }

    #[Get("/comments/{id}", "feed.comments")]
    public function comments($id): string
    {
        return $this->render("admin/feed/posts.html", [
            "posts" => $this->getComments($id)
        ]);
    }

    #[Get("/posts", "feed.posts")]
    public function posts(): string
    {
        return $this->render("admin/feed/posts.html", [
            "posts" => $this->getPosts()
        ]);
    }

    #[Post("/", "feed.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "body" => ["required", "min_length|1"],
        ]);
        if ($valid) {
            if (trim($valid->body)) {
                $user = user();
                $post = PostModel::new([
                    "user_id" => $user->id,
                    "body" => $valid->body,
                ]);
                $post->user = $user;
                $post->user->avatar = $user->gravatar(40);
                return $this->render("admin/feed/post.html", [
                    "post" => $post,
                    "user" => $user,
                ]);
            }
        }
        return false;
    }

    private function getPosts()
    {
        $user = user();
        if ($user->role()->permission_level == 0) {
            // Super admins see everything
            $posts = PostModel::search(["*"])
                ->where(["parent_id IS NULL AND created_at > NOW() - INTERVAL 1 MONTH"])
                ->orderBy(["created_at DESC"])
                ->execute()->fetchAll();
        } else {
            $posts = PostModel::search(["*"])
                ->where(["parent_id IS NULL 
                AND created_at > NOW() - INTERVAL 1 MONTH
                AND (user_id = 1 
                OR user_id = ? 
                OR EXISTS (SELECT * 
                    FROM follow 
                    WHERE user_id = ? 
                    AND friend_id = posts.user_id))"], user()->id, user()->id)
                ->orderBy(["created_at DESC"])
                ->execute()->fetchAll();
        }

        foreach ($posts as $i => &$post) {
            $user = User::find($post->user_id);
            $user->avatar = $user->gravatar(40);
            $post->user = $user;
        }
        return $posts;
    }

    private function getComments($id)
    {
        $user = user();
        $comments = PostModel::search(["*"])
            ->where(["parent_id = ?"], $id)
            ->orderBy(["created_at DESC"])
            ->execute()->fetchAll();

        foreach ($comments as $i => &$post) {
            $user = User::find($post->user_id);
            $user->avatar = $user->gravatar(40);
            $post->user = $user;
        }
        return $comments;
    }
}
