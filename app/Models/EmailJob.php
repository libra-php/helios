<?php

namespace App\Models;

use Helios\Model\Model;

class EmailJob extends Model
{
    public function __construct(?string $id = null)
    {
        parent::__construct("email_jobs", $id);
    }
}
