<?php

namespace App\Models;

use Helios\Model\Model;

class User extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("users", $key);
    }

    public function role(): UserRole
    {
        return UserRole::findOrFail($this->user_role_id);
    }
}
