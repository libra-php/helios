<?php

namespace App\Models;

use Helios\Model\Model;

class Follow extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("follow", $key);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function friend(): User
    {
        return User::findOrFail($this->friend_id);
    }
}
