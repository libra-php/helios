<?php

namespace App\Controllers\Feed;

use App\Models\Feed;
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
        $post = PostModel::findOrNotFound($id);
        if ($post) {
            $user = user();
            $user->avatar = $user->avatar();

            $post_user = User::find($post->user_id);
            $post_user->avatar = $post_user->avatar();
            $post->user = $post_user;
            $post->link = config("app.url") . "/admin/feed/" . $post->id;

            return $this->render("/admin/feed/show.html", [
                "post" => $post,
                "user" => $user,
            ]);
        }
    }

    #[Get("/ago/{id}", "feed.post-ago")]
    public function postAgo($id)
    {
        $post = PostModel::findOrNotFound($id);
        if ($post) {
            $ago = $this->twitterTimeFormat(Carbon::parse($post->created_at));
            return $ago;
        }
    }

    #[Get("/like-button/{id}", "feed.like-button")]
    public function likeButton($id)
    {
        $count = Like::search(["count(*)"])
            ->where(["post_id = ?"], $id)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        $liked_by_me = Like::search(["1"])
            ->where(["post_id = ? AND user_id = ?"], $id, user()->id)
            ->execute()
            ->fetch();
        return $this->render("/admin/feed/like-button.html", [
            "id" => $id,
            "count" => $count,
            "liked_by_me" => $liked_by_me,
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
        return $this->likeButton($id);
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
            if (trim($valid->body) != '') {
                $convertedPost = $this->convertBody($valid->body);

                $user = user();
                $post = PostModel::new([
                    "user_id" => $user->id,
                    "parent_id" => $id,
                    "body" => $convertedPost,
                ]);
                $post->user = $user;
                $post->user->avatar = $user->avatar();
                $post->link = config("app.url") . "/admin/feed/" . $post->id;
                trigger("commentButton, updateAgo");
                return $this->render("admin/feed/post.html", [
                    "post" => $post,
                    "user" => $user,
                ]);
            }
        }
        return $this->show($id);
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
        // Update feed load ts for user
        Feed::new([
            "user_id" => user()->id
        ]);
        header("HX-Push-Url: /admin/feed");
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
            if (trim($valid->body) != '') {
                $convertedPost = $this->convertBody($valid->body);
                $user = user();
                $post = PostModel::new([
                    "user_id" => $user->id,
                    "body" => $convertedPost,
                ]);
                $post->user = $user;
                $post->user->avatar = $user->avatar();
                $post->link = config("app.url") . "/admin/feed/" . $post->id;
                trigger("updateAgo");
                return $this->render("admin/feed/post.html", [
                    "post" => $post,
                    "user" => $user,
                ]);
            }
        }
        return false;
    }

    private function convertBody($body)
    {
        $pattern = '/(https?:\/\/[^\s]+)/';
        $replacement = '<a href="$1" target="_blank">$1</a>';
        $body = preg_replace($pattern, $replacement, $body);
        $body = nl2br($body);
        return $body;
    }

    #[Get("/waiting", "feed.posts-waiting")]
    public function postsWaiting()
    {
        // Find out last load time
        $last_load = Feed::search(["created_at"])
            ->where(["user_id = ?"], user()->id)
            ->orderBy(["id DESC"])
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
        // Find out how mnay posts are waiting to be viewed
        if ($last_load) {
            $count = PostModel::search(["count(*)"])
                ->where(["parent_id IS NULL 
                AND created_at > ?
                AND user_id != ?
                AND (user_id = 1 
                OR EXISTS (SELECT * 
                    FROM follow 
                    WHERE user_id = ? 
                    AND friend_id = posts.user_id))"], $last_load, user()->id, user()->id)
                ->orderBy(["created_at DESC"])
                ->execute()->fetch(PDO::FETCH_COLUMN);
            if ($count > 0) {
                return $this->render("/admin/feed/posts-waiting-button.html", [
                    "count" => $count,
                ]);
            }
        }
        return "";
    }

    private function twitterTimeFormat(Carbon $time)
    {
        $diffInSeconds = (int) $time->diffInSeconds();

        if ($diffInSeconds < 60) {
            return $diffInSeconds . 's';
        }

        $diffInMinutes = (int) floor($time->diffInMinutes());
        if ($diffInMinutes < 60) {
            return $diffInMinutes . 'm';
        }

        $diffInHours = (int) floor($time->diffInHours());
        if ($diffInHours < 24) {
            return $diffInHours . 'h';
        }

        $diffInDays = (int) floor($time->diffInDays());
        if ($diffInDays < 7) {
            return $diffInDays . 'd';
        }

        $diffInWeeks = (int) floor($time->diffInWeeks());
        if ($diffInWeeks < 4) {
            return $diffInWeeks . 'w';
        }

        $diffInMonths = (int) floor($time->diffInMonths());
        if ($diffInMonths < 12) {
            return $diffInMonths . 'mo';
        }

        return (int) floor($time->diffInYears()) . 'y';
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
            $user->avatar = $user->avatar();
            $post->user = $user;
            $post->link = config("app.url") . "/admin/feed/" . $post->id;
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
            $user->avatar = $user->avatar();
            $post->user = $user;
            $post->link = config("app.url") . "/admin/feed/" . $post->id;
        }
        return $comments;
    }
}
