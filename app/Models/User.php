<?php

namespace App\Models;

use Helios\Model\Model;

class User extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("users", $key);
    }

    public function type(): UserType
    {
        return UserType::findOrFail($this->user_type_id);
    }
}
