<?php

namespace App\Controllers\Admin;

use App\Models\Like;
use Helios\Web\Controller;
use StellarRouter\{Get, Post, Group};
use App\Models\Post as PostModel;
use App\Models\User;
use Carbon\Carbon;
use PDO;

#[Group(prefix: "/feed", middleware: ['auth'])]
class FeedController extends Controller
{
    #[Post("/post", "feed.post")]
    public function post(): string
    {
        $valid = $this->validateRequest([
            "body" => ["required", "min_length|1"],
        ]);
        if ($valid) {
            if (trim($valid->body)) {
                $post = PostModel::new([
                    "user_id" => user()->id,
                    "body" => $valid->body,
                ]);
                return $this->render("components/post.html", [
                    "post" => $post,
                    "user" => user()
                ]);
            }
        }
        return false;
    }

    #[Get("/posts", "feed.posts")]
    public function posts(): string
    {
        return $this->render("components/posts.html",[
            "posts" => $this->getPosts(),
            "user_avatar" => user()->gravatar(40)
        ]);
    }

    #[Get("/post/ago/{id}", "feed.post-ago")]
    public function postAgo($id)
    {
        $post = PostModel::find($id);
        if ($post) {
            $ago = Carbon::parse($post->created_at)->diffForHumans();
            return $ago;
        }
        return '';
    }

    #[Get("/comment/count/{id}", "feed.comment-count")]
    public function commentCount($id)
    {
        $count = PostModel::search(["count(*)"])
            ->where(["parent_id = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        return $count ?? 0;
    }

    #[Post("/comment/{id}", "feed.post-comment")]
    public function comment($id)
    {
        $valid = $this->validateRequest([
            "body" => ["required", "min_length|1"],
        ]);
        if ($valid) {
            if (trim($valid->body)) {
                PostModel::new([
                    "user_id" => user()->id,
                    "parent_id" => $id,
                    "body" => $valid->body,
                ]);
            }
        }
        return $this->posts();
    }

    #[Get("/like/count/{id}", "feed.post-likes")]
    public function likes($id)
    {
        $count = Like::search(["count(*)"])
            ->where(["post_id = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        return $count ?? 0;
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

        return $this->posts();
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
            $post->user = $user;
            $hash = md5(strtolower(trim($user->email)));
            $size = 40;
            $post->gravatar = "http://www.gravatar.com/avatar/$hash?s=$size";
        }
        return $posts;
    }
}
