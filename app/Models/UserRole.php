<?php

namespace App\Models;

use Helios\Model\Model;

class UserRole extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('user_roles', $id);
    }
}
