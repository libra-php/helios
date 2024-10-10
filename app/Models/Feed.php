<?php

namespace App\Models;

use Helios\Model\Model;

class Feed extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("feed", $key);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }
}
