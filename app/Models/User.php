<?php

namespace App\Models;

use Helios\Model\Model;

class User extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct('users', $id);
    }

    public function gravatar()
    {
        $hash = md5(strtolower(trim($this->email)));
        return "https://www.gravatar.com/avatar/{$hash}";
    }
}
