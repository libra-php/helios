<?php

namespace App\Models;

use Helios\Model\Model;

class Like extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("likes", $key);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function post(): User
    {
        return Post::findOrFail($this->post_id);
    }
}
