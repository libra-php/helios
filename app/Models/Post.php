<?php

namespace App\Models;

use Helios\Model\Model;

class Post extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("posts", $key);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }
}
