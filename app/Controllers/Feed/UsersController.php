<?php

namespace App\Controllers\Feed;

use App\Models\Follow;
use App\Models\User;
use Helios\Web\Controller;
use StellarRouter\{Get, Group, Post};

#[Group(prefix: "/users", middleware: ['auth'])]
class UsersController extends Controller
{
    #[Get("/", "users.index")]
    public function index(): string
    {
        $users = User::search(["*"])
            ->where(["id != 1 AND id != ?"], user()->id)
            ->orderBy(["name ASC"])
            ->execute()
            ->fetchAll();
        foreach ($users as &$user) {
            $user = User::find($user->id);
            $user->avatar = $user->avatar();
        }
        return $this->render("/admin/feed/users.html", [
            "user" => user(),
            "users" => $users,
        ]);
    }

    #[Get("/follow-button/{id}", "users.follow-button")]
    public function followButton($id)
    {
        // Are we following this user?
        $following = Follow::var("1", ["user_id = ? AND friend_id = ?"], user()->id, $id);
        return $this->render("/admin/feed/follow-button.html", [
            "id" => $id,
            "following" => $following,
        ]);
    }

    #[Post("/follow/{id}", "users.follow")]
    public function follow($id)
    {
        $user = user();
        $following = Follow::var("1", ["user_id = ? AND friend_id = ?"], $user->id, $id);
        if ($following) {
            // Unfollow user
            db()->query("DELETE FROM follow WHERE user_id = ? AND friend_id = ?", $user->id, $id);
        } else {
            // Follow user
            Follow::new([
                "user_id" => $user->id,
                "friend_id" => $id,
            ]);
        }
        trigger("loadFeed");
        return $this->followButton($id);
    }
}

