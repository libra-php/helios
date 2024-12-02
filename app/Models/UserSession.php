<?php

namespace App\Models;

use Helios\Model\Model;

class UserSession extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('user_sessions', $id);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }
}
