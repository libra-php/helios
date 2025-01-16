<?php

namespace App\Models;

use Helios\Model\Model;

class PasswordReset extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("password_resets", $id);
    }

    public function emailJob(): EmailJob
    {
        return EmailJob::findOrFail($this->email_job_id);
    }

    public function user(): User
    {
        return User::findOrFail($this->user_id);
    }
}
