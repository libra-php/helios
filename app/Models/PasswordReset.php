<?php

namespace App\Models;

use Helios\Model\Model;

class PasswordReset extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('password_resets', $id);
    }
}
