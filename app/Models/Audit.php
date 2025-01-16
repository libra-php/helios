<?php

namespace App\Models;

use Helios\Model\Model;

class Audit extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("audit", $id);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }
}
