<?php

namespace App\Models;

use Helios\Model\Model;

class UserRole extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("user_roles", $key);
    }
}

