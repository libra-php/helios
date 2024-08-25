<?php

namespace App\Models;

use Helios\Model\Model;

class Session extends Model
{
    public function __construct(?string $key = null)
    {
        parent::__construct("sessions", $key);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }

    public function module(): Module
    {
        return Module::findOrFail($this->module_id);
    }
}
